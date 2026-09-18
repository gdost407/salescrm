<?php

namespace App\Http\Controllers\Web\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveCatalogItemRequest;
use App\Models\Item;
use App\Models\Tax;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class CatalogItemController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['type' => ['nullable', 'in:service,inventory'], 'search' => ['nullable', 'string', 'max:255']]);
        $items = Item::query()->with('tax')->where('company_id', $request->user()->company_id)
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('name', 'like', '%'.$search.'%'))
            ->latest()->paginate(20)->withQueryString();

        return view('app.catalog.index', compact('items', 'filters'));
    }

    public function create(Request $request): View
    {
        return view('app.catalog.form', ['item' => new Item(['type' => $request->query('type') === 'inventory' ? 'inventory' : 'service'])]);
    }

    public function store(SaveCatalogItemRequest $request): RedirectResponse
    {
        $item = new Item(['company_id' => $request->user()->company_id]);
        $this->save($request, $item);

        return $this->savedResponse($request, $item);
    }

    public function show(Request $request, Item $catalogItem): View
    {
        $this->ensureCompany($request, $catalogItem);

        return view('app.catalog.show', ['item' => $catalogItem]);
    }

    public function edit(Request $request, Item $catalogItem): View
    {
        $this->ensureCompany($request, $catalogItem);

        return view('app.catalog.form', ['item' => $catalogItem]);
    }

    public function update(SaveCatalogItemRequest $request, Item $catalogItem): RedirectResponse
    {
        $this->save($request, $catalogItem);

        return $this->savedResponse($request, $catalogItem);
    }

    public function destroy(Request $request, Item $catalogItem): RedirectResponse
    {
        $this->ensureCompany($request, $catalogItem);
        $image = $catalogItem->image;
        $catalogItem->delete();
        if ($image) {
            Storage::disk('local')->delete($image);
        }

        return redirect()->back()->with('message', 'Item deleted. Existing quotations retain their saved values.');
    }

    public function image(Request $request, Item $catalogItem): StreamedResponse
    {
        $this->ensureCompany($request, $catalogItem);
        abort_unless($request->user()->hasPermission('view_catalog_items') || $request->user()->hasPermission('edit_catalog_items'), 403);
        abort_unless($catalogItem->image && Storage::disk('local')->exists($catalogItem->image), 404);

        return Storage::disk('local')->response($catalogItem->image, null, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }

    private function ensureCompany(Request $request, Item $item): void
    {
        abort_unless((int) $item->company_id === (int) $request->user()->company_id, 404);
    }

    private function save(SaveCatalogItemRequest $request, Item $item): void
    {
        $oldImage = $item->image;
        $newImage = null;
        $item->fill($request->safe()->except(['image', 'remove_image', 'hsn', 'gst_rate']));
        $item->hsn_sac = $request->validated('hsn');
        $item->tax_type = $item->type === 'inventory' ? 'inclusive' : 'exclusive';

        try {
            if ($request->hasFile('image')) {
                $newImage = $request->file('image')->store('catalog-images/'.$item->company_id, 'local');
                throw_if($newImage === false, \RuntimeException::class, 'Unable to store the item image.');
                $item->image = $newImage;
            } elseif ($request->boolean('remove_image')) {
                $item->image = null;
            }

            DB::transaction(function () use ($request, $item): void {
                $tax = Tax::query()->firstOrCreate([
                    'company_id' => $item->company_id,
                    'name' => 'GST',
                    'rate' => $request->validated('gst_rate'),
                    'is_active' => true,
                ]);
                $item->tax()->associate($tax);
                $item->save();
            });
        } catch (Throwable $exception) {
            if ($newImage) {
                Storage::disk('local')->delete($newImage);
            }
            throw $exception;
        }

        if ($oldImage && $oldImage !== $item->image) {
            Storage::disk('local')->delete($oldImage);
        }
    }

    private function savedResponse(Request $request, Item $item): RedirectResponse
    {
        $route = $request->user()->hasPermission('view_catalog_items')
            ? route('catalog-items.show', $item)
            : ($request->user()->hasPermission('edit_catalog_items') ? route('catalog-items.edit', $item) : route('catalog-items.create'));

        return redirect($route)->with('message', 'Item saved successfully.');
    }
}
