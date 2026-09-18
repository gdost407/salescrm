@extends('layouts.app')

@push('styles')
<style>
  .quotation-document { max-width: 1100px; margin: 0 auto; color: #202b3c; }
  .quotation-document .quotation-heading { letter-spacing: .12em; color: #253e64; }
  .quotation-document .table th { background: #f1f4f8; color: #253e64; }
  .quotation-document .document-text { white-space: pre-line; overflow-wrap: anywhere; }
  @media print {
    @page { size: A4; margin: 12mm; }
    html, body, .layout-page, .content-wrapper { background: white !important; margin: 0 !important; padding: 0 !important; }
    .layout-menu, .layout-navbar, .content-footer, .layout-overlay, .content-backdrop, #app-notifications, .quotation-actions, .alert { display: none !important; }
    .layout-wrapper, .layout-container, .layout-page, .content-wrapper { display: block !important; min-height: 0 !important; width: 100% !important; }
    .quotation-page { margin: 0 !important; padding: 0 !important; max-width: none !important; }
    .quotation-document { border: 0 !important; box-shadow: none !important; max-width: none; padding: 0 !important; font-size: 10pt; }
    .quotation-document .table { font-size: 9pt; }
    .quotation-document .table th, .quotation-document .table td { padding: 6px 4px; }
    .quotation-document thead { display: table-header-group; }
    .quotation-document tr, .quotation-totals, .quotation-signature { break-inside: avoid; }
    .quotation-document a { color: inherit; text-decoration: none; }
    .quotation-document .table-responsive { overflow: visible; }
  }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y quotation-page">
  @if (session('message'))<div class="alert alert-success" role="status">{{ session('message') }}</div>@endif
  <div class="quotation-actions d-flex flex-wrap justify-content-end gap-2 mb-4">
    <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary">All quotations</a>
    @if (auth()->user()->hasPermission('edit_quotations'))<a href="{{ route('quotations.edit', $quotation) }}" class="btn btn-outline-primary">Edit quotation</a>@endif
    <button type="button" onclick="window.print()" class="btn btn-primary">Print / Save PDF</button>
  </div>
  <article class="card quotation-document p-4 p-md-5">
    <div class="row g-4 border-bottom pb-4 mb-4">
      <div class="col-7">
        <h3 class="mb-2">{{ $quotation->seller_details['name'] }}</h3>
        @include('app.quotations.partials.address', ['details' => $quotation->seller_details])
      </div>
      <div class="col-5 text-end"><h2 class="quotation-heading mb-3">QUOTATION</h2><div class="fw-bold">{{ $quotation->number() }}</div><div>Date: {{ $quotation->quotation_date->format('d M Y') }}</div>@if ($quotation->valid_until)<div>Valid until: {{ $quotation->valid_until->format('d M Y') }}</div>@endif</div>
    </div>
    <div class="mb-4"><div class="text-uppercase small text-body-secondary mb-2">Quotation for</div><h5 class="mb-1">{{ $quotation->client_details['name'] }}</h5>@if ($quotation->client_details['company_name'] ?? null)<div class="fw-semibold">{{ $quotation->client_details['company_name'] }}</div>@endif @include('app.quotations.partials.address', ['details' => $quotation->client_details])</div>
    <div class="table-responsive">
      <table class="table table-bordered align-top"><thead><tr><th>#</th><th>Description</th><th>HSN / SAC</th><th class="text-end">Qty</th><th class="text-end">Rate</th><th class="text-end">Taxable</th><th class="text-end">GST %</th><th class="text-end">GST</th><th class="text-end">Amount</th></tr></thead><tbody>
      @foreach ($quotation->items as $item)
        <tr><td>{{ $loop->iteration }}</td><td><strong>{{ $item->name }}</strong><div class="small">{{ ucfirst($item->type) }} · {{ $item->type === 'service' ? 'Rate excl. GST' : 'Rate incl. GST' }}</div>@if ($item->description)<div class="document-text small mt-1">{{ $item->description }}</div>@endif</td><td>{{ $item->hsn }}</td><td class="text-end">{{ rtrim(rtrim($item->quantity, '0'), '.') }}</td><td class="text-end">{{ $item->rate }}</td><td class="text-end">{{ $item->subtotal }}</td><td class="text-end">{{ $item->gst_rate }}%</td><td class="text-end">{{ $item->tax_total }}</td><td class="text-end">{{ $item->total }}</td></tr>
      @endforeach
      </tbody></table>
    </div>
    <div class="row justify-content-end mt-4 quotation-totals"><div class="col-6"><dl class="row"><dt class="col-7">Taxable subtotal</dt><dd class="col-5 text-end">{{ $quotation->subtotal }}</dd><dt class="col-7">GST / tax</dt><dd class="col-5 text-end">{{ $quotation->tax_total }}</dd><dt class="col-7 border-top pt-3">Grand total (INR)</dt><dd class="col-5 text-end border-top pt-3 fw-bold">{{ $quotation->total }}</dd></dl></div></div>
    <p class="small text-body-secondary">All amounts are in INR. GST is added to Service rates and extracted from Inventory rates. Amounts are rounded to two decimal places per line.</p>
    @if ($quotation->notes)<div class="mt-4"><h6>Notes</h6><div class="document-text">{{ $quotation->notes }}</div></div>@endif
    @if ($quotation->terms)<div class="mt-4"><h6>Terms and conditions</h6><div class="document-text">{{ $quotation->terms }}</div></div>@endif
    <div class="quotation-signature text-end mt-5 pt-4"><div>For {{ $quotation->seller_details['name'] }}</div><div class="mt-5 fw-semibold">Authorized signatory</div></div>
    <div class="border-top mt-4 pt-3 small text-body-secondary">This is a quotation, not a tax invoice.</div>
  </article>
</div>
@endsection
