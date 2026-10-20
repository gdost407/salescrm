<?php

namespace App\Http\Controllers\Web;

use App\Actions\SalesDocumentChoices;
use App\Actions\SaveSalesDocument;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveDocumentRequest;
use App\Models\Client;
use App\Models\Item;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

abstract class DocumentController extends Controller
{
    protected string $type;

    public function index(Request $request): View
    {
        $model = SalesDocumentChoices::MODELS[$this->type];
        $records = $model::forCompany($request->user()->company_id)->with('client')->latest('id')->paginate(20);

        return $this->page('index', compact('records'));
    }

    public function create(Request $request): View
    {
        $model = SalesDocumentChoices::MODELS[$this->type];

        return $this->form($request, new $model, 'create');
    }

    public function edit(Request $request): View
    {
        return $this->form($request, $this->record($request), 'edit');
    }

    public function show(Request $request): View
    {
        return $this->details($request, 'show');
    }

    public function print(Request $request): View
    {
        return $this->details($request, 'print');
    }

    public function store(SaveDocumentRequest $request, SaveSalesDocument $save): RedirectResponse
    {
        return $this->save($request, $save);
    }

    public function update(SaveDocumentRequest $request, SaveSalesDocument $save): RedirectResponse
    {
        return $this->save($request, $save, $this->record($request));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $record = $this->record($request);
        $paths = DB::transaction(function () use ($record): array {
            $record = $record->newQuery()->lockForUpdate()->findOrFail($record->id);
            $relations = ['payments', 'ledgerEntries'];
            if ($this->type === 'quotation') {
                $relations = array_merge($relations, ['jobs', 'invoices']);
            } elseif ($this->type === 'job') {
                $relations[] = 'invoices';
            }
            foreach ($relations as $relation) {
                if ($record->$relation()->exists()) {
                    throw ValidationException::withMessages(['record' => 'This document has linked records and cannot be deleted.']);
                }
            }
            $paths = $record->attachments()->pluck('file_path')->all();
            $record->attachments()->delete();
            $record->items()->delete();
            $record->delete();

            return $paths;
        });
        Storage::disk('local')->delete($paths);

        return redirect()->route($request->user()->hasPermission('view_'.$this->type.'s') ? $this->type.'s.index' : 'dashboard')->with('success', ucfirst($this->type).' deleted.');
    }

    public function attachment(Request $request): Response
    {
        $record = $this->record($request);
        $attachment = $record->attachments()->where('company_id', $request->user()->company_id)->findOrFail($request->route('attachment'));
        abort_unless(Storage::disk('local')->exists($attachment->file_path), 404);

        if ($request->boolean('preview')) {
            abort_unless(in_array($attachment->mime_type, ['image/jpeg', 'image/png', 'image/webp'], true), 404);

            return response()->file(Storage::disk('local')->path($attachment->file_path), [
                'Content-Type' => $attachment->mime_type,
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ]);
        }

        return Storage::disk('local')->download($attachment->file_path, $attachment->file_name, ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
    }

    private function record(Request $request): Model
    {
        $model = SalesDocumentChoices::MODELS[$this->type];

        return $model::forCompany($request->user()->company_id)->findOrFail($request->route('document'));
    }

    private function details(Request $request, string $page): View
    {
        $record = $this->record($request)->load('company', 'client', 'items', 'attachments', 'creator');
        $payments = $request->user()->hasPermission('view_payments')
            ? $record->payments()->where('company_id', $request->user()->company_id)->orderBy('payment_date')->get()
            : collect();

        return $this->page($page, compact('record', 'payments'));
    }

    private function form(Request $request, Model $record, string $page): View
    {
        $companyId = $request->user()->company_id;
        if ($this->type === 'quotation' && $page === 'create') {
            $clients = Client::query()
                ->where('company_id', $companyId)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(10)
                ->get(['id', 'name', 'company_name', 'email', 'mobile', 'billing_address', 'shipping_address', 'country', 'state', 'city', 'zip_code']);

            $selectedClientId = $request->old('client_id');
            if ($selectedClientId && ! $clients->contains('id', (int) $selectedClientId)) {
                $selectedClient = Client::query()
                    ->where('company_id', $companyId)
                    ->whereKey($selectedClientId)
                    ->first(['id', 'name', 'company_name', 'email', 'mobile', 'billing_address', 'shipping_address', 'country', 'state', 'city', 'zip_code']);
                if ($selectedClient) {
                    $clients->prepend($selectedClient);
                }
            }
        } else {
            $clients = Client::where('company_id', $companyId)->orderBy('name')->get(['id', 'name']);
        }
        $catalog = Item::forCompany($companyId)->with('tax')->where('is_active', true)->orderBy('name')->get()
            ->map(fn (Item $item): array => ['id' => $item->id, 'type' => $item->type, 'name' => $item->name, 'hsn' => $item->hsn_sac, 'rate' => $item->rate, 'gst_rate' => $item->tax?->rate ?? '0', 'tax_type' => $item->tax_type]);
        $snapshots = $record->exists ? $record->items->map(fn ($item): array => [
            'id' => $item->id, 'catalog_item_id' => $item->item_id, 'type' => $item->item_type, 'name' => $item->item_name,
            'hsn' => $item->hsn_sac, 'rate' => $item->rate, 'gst_rate' => $item->tax_rate, 'tax_type' => $item->tax_type,
        ]) : collect();
        $rows = $record->exists ? $record->items->map(fn ($item): array => ['quotation_item_id' => $item->id, 'catalog_item_id' => $item->item_id, 'quantity' => $item->qty])->all() : [];
        $linkedDocuments = [];
        foreach (['quotation_id' => 'quotation', 'job_id' => 'job'] as $field => $type) {
            if (in_array($field, SalesDocumentChoices::FIELDS[$this->type], true)) {
                $model = SalesDocumentChoices::MODELS[$type];
                $query = $model::forCompany($companyId)->orderByDesc('id');
                if ($field === 'quotation_id' && $this->type === 'job' && $page === 'create') {
                    $today = today();
                    $legacyExpiryDate = today()->subDays(2);
                    $query->where(function ($quotationQuery) use ($today, $legacyExpiryDate): void {
                        $quotationQuery->where('status', 'accepted')
                            ->orWhere(function ($pendingQuery) use ($today, $legacyExpiryDate): void {
                                $pendingQuery->whereIn('status', ['draft', 'sent'])
                                    ->where(function ($validityQuery) use ($today, $legacyExpiryDate): void {
                                        $validityQuery->whereDate('valid_until', '>=', $today)
                                            ->orWhere(function ($legacyQuery) use ($legacyExpiryDate): void {
                                                $legacyQuery->whereNull('valid_until')
                                                    ->whereDate('quotation_date', '>=', $legacyExpiryDate);
                                            });
                                    });
                            });
                    });
                }
                $linkedDocuments[$field] = $query->get(['id', $type.'_no']);
            }
        }
        $statuses = SalesDocumentChoices::STATUSES[$this->type];

        return $this->page($page, compact('record', 'clients', 'catalog', 'snapshots', 'rows', 'linkedDocuments', 'statuses'));
    }

    private function save(SaveDocumentRequest $request, SaveSalesDocument $save, ?Model $record = null): RedirectResponse
    {
        $paths = [];
        try {
            $record = DB::transaction(function () use ($request, $save, $record, &$paths): Model {
                $record = $save->handle($request->user(), $request->validated(), $this->type, $record);
                foreach ($request->file('attachments', []) as $file) {
                    $path = $file->store('documents/'.$request->user()->company_id, 'local');
                    if ($path === false) {
                        throw ValidationException::withMessages(['attachments' => 'The attachment could not be stored. Please try again.']);
                    }
                    $paths[] = $path;
                    $record->attachments()->create([
                        'company_id' => $request->user()->company_id, 'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path, 'mime_type' => $file->getMimeType(), 'file_size' => $file->getSize(), 'uploaded_by' => $request->user()->id,
                    ]);
                }

                return $record;
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($paths);
            throw $exception;
        }
        $canView = $request->user()->hasPermission('view_'.$this->type.'s');

        return redirect()->route($this->type.'s.'.($canView ? 'show' : 'create'), $canView ? ['document' => $record->id] : [])->with('success', ucfirst($this->type).' saved.');
    }

    private function page(string $page, array $data): View
    {
        return view('web.'.$this->type.'.'.$page, $data + ['type' => $this->type, 'resource' => $this->type.'s']);
    }
}
