<?php

namespace App\Http\Controllers\Web\Sales;

use App\Actions\SaveQuotation;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveQuotationRequest;
use App\Models\Item;
use App\Models\Client;
use App\Models\TxnQuotation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function index(Request $request): View
    {
        $quotations = TxnQuotation::query()->with('client')->where('company_id', $request->user()->company_id)->latest()->paginate(20);

        return view('app.quotations.index', compact('quotations'));
    }

    public function create(Request $request): View
    {
        return $this->form($request, new TxnQuotation(['quotation_date' => today()]));
    }

    public function store(SaveQuotationRequest $request, SaveQuotation $save): RedirectResponse
    {
        $quotation = $save->handle($request->user(), $request->validated());

        return $this->savedResponse($request, $quotation);
    }

    public function show(Request $request, TxnQuotation $quotation): View
    {
        $this->ensureCompany($request, $quotation);
        $quotation->load(['items', 'client', 'company']);
        $sellerDetails = $quotation->company->only(['name', 'email', 'phone', 'address', 'city', 'state', 'country', 'pincode']);
        $clientDetails = $quotation->client->only(['name', 'company_name', 'email', 'city', 'state', 'country', 'gst_no']);
        $clientDetails += ['phone' => $quotation->client->mobile, 'address' => $quotation->client->billing_address, 'pincode' => $quotation->client->zip_code];

        return view('app.quotations.show', compact('quotation', 'sellerDetails', 'clientDetails'));
    }

    public function edit(Request $request, TxnQuotation $quotation): View
    {
        $this->ensureCompany($request, $quotation);

        return $this->form($request, $quotation);
    }

    public function update(SaveQuotationRequest $request, TxnQuotation $quotation, SaveQuotation $save): RedirectResponse
    {
        $quotation = $save->handle($request->user(), $request->validated(), $quotation);

        return $this->savedResponse($request, $quotation);
    }

    private function form(Request $request, TxnQuotation $quotation): View
    {
        $quotation->load('items');
        $clients = Client::query()->where('company_id', $request->user()->company_id)->orderBy('name')->get(['id', 'name', 'company_name']);
        $catalogItems = Item::query()->with('tax')->where('company_id', $request->user()->company_id)->where('is_active', true)->orderBy('type')->orderBy('name')
            ->get(['id', 'type', 'name', 'hsn_sac', 'rate', 'tax_id', 'tax_type'])->map(fn ($item) => [
                'id' => $item->id, 'type' => $item->type, 'name' => $item->name, 'hsn' => $item->hsn_sac,
                'rate' => $item->rate, 'gst_rate' => $item->tax?->rate ?? '0.0000', 'tax_type' => $item->tax_type,
            ]);
        $snapshots = $quotation->items->map(fn ($item) => [
            'id' => $item->id, 'catalog_item_id' => $item->item_id, 'type' => $item->item_type,
            'name' => $item->item_name, 'hsn' => $item->hsn_sac, 'rate' => $item->rate,
            'gst_rate' => $item->tax_rate, 'tax_type' => $item->tax_type,
        ]);
        $rows = old('items', $quotation->items->map(fn ($item) => [
            'quotation_item_id' => $item->id, 'catalog_item_id' => $item->item_id, 'quantity' => $item->qty,
        ])->all()) ?: [['catalog_item_id' => '', 'quantity' => '1']];

        return view('app.quotations.form', compact('quotation', 'clients', 'catalogItems', 'snapshots', 'rows'));
    }

    private function ensureCompany(Request $request, TxnQuotation $quotation): void
    {
        abort_unless((int) $quotation->company_id === (int) $request->user()->company_id, 404);
    }

    private function savedResponse(Request $request, TxnQuotation $quotation): RedirectResponse
    {
        $route = $request->user()->hasPermission('view_quotations') ? route('quotations.show', $quotation)
            : ($request->user()->hasPermission('edit_quotations') ? route('quotations.edit', $quotation) : route('quotations.create'));

        return redirect($route)->with('message', 'Quotation saved successfully.');
    }
}
