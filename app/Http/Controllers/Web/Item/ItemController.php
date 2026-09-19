<?php

namespace App\Http\Controllers\Web\Item;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveCatalogItemRequest;
use App\Models\Item;
use App\Models\Tax;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ItemController extends Controller
{
    public function index(Request $request): View
    {
        $records = Item::forCompany($request->user()->company_id)->with('tax')->orderBy('name')->paginate(20);

        return view('web.item.index', compact('records'));
    }

    public function create(Request $request): View
    {
        return $this->form($request, new Item, 'create');
    }

    public function store(SaveCatalogItemRequest $request): RedirectResponse
    {
        return $this->save($request, new Item(['company_id' => $request->user()->company_id]));
    }

    public function edit(Request $request, Item $item): View
    {
        $this->owned($request, $item);

        return $this->form($request, $item, 'edit');
    }

    public function show(Request $request, Item $item): View
    {
        $this->owned($request, $item);

        return view('web.item.show', ['record' => $item->load('tax')]);
    }

    public function update(SaveCatalogItemRequest $request, Item $item): RedirectResponse
    {
        $this->owned($request, $item);

        return $this->save($request, $item);
    }

    public function destroy(Request $request, Item $item): RedirectResponse
    {
        $this->owned($request, $item);
        $image = $item->image;
        $item->delete();
        if ($image) {
            Storage::disk('local')->delete($image);
        }

        return redirect()->route($request->user()->hasPermission('view_catalog_items') ? 'items.index' : 'dashboard')->with('success', 'Item deleted.');
    }

    public function image(Request $request, Item $item): StreamedResponse
    {
        $this->owned($request, $item);
        abort_unless($request->user()->hasPermission('view_catalog_items') || $request->user()->hasPermission('edit_catalog_items'), 403);
        abort_unless($item->image && Storage::disk('local')->exists($item->image), 404);

        return Storage::disk('local')->response($item->image, null, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }

    private function form(Request $request, Item $item, string $page): View
    {
        $taxes = Tax::forCompany($request->user()->company_id)->orderBy('name')->get();

        return view('web.item.'.$page, ['record' => $item->load('tax'), 'taxes' => $taxes]);
    }

    private function owned(Request $request, Item $item): void
    {
        abort_unless((int) $item->company_id === (int) $request->user()->company_id, 404);
    }

    private function save(SaveCatalogItemRequest $request, Item $item): RedirectResponse
    {
        $data = $request->validated();
        $oldImage = $item->image;
        $newImage = $request->file('image')?->store('catalog-images/'.$request->user()->company_id, 'local');
        if ($newImage === false) {
            throw ValidationException::withMessages(['image' => 'The image could not be stored. Please try again.']);
        }
        try {
            DB::transaction(function () use ($request, $item, $data, $newImage): void {
                $tax = isset($data['tax_id'])
                    ? Tax::forCompany($request->user()->company_id)->findOrFail($data['tax_id'])
                    : Tax::firstOrCreate(['company_id' => $request->user()->company_id, 'name' => 'GST', 'rate' => $data['gst_rate']], ['is_active' => true]);
                $item->fill(collect($data)->only(['type', 'name', 'description', 'rate', 'sku', 'is_active'])->all());
                $item->hsn_sac = $data['hsn'];
                $item->tax_id = $tax->id;
                $item->tax_type = $data['type'] === 'inventory' ? 'inclusive' : 'exclusive';
                if ($newImage || $request->boolean('remove_image')) {
                    $item->image = $newImage ?: null;
                }
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

        return redirect()->route($request->user()->hasPermission('view_catalog_items') ? 'items.show' : 'items.create', $request->user()->hasPermission('view_catalog_items') ? $item : [])->with('success', 'Item saved.');
    }
}
