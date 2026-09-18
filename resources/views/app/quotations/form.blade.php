@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  @if (session('message'))<div class="alert alert-success" role="status">{{ session('message') }}</div>@endif
  <div class="card">
    <div class="card-header"><h4 class="mb-0">{{ $quotation->exists ? 'Edit '.$quotation->number() : 'Create quotation' }}</h4></div>
    <div class="card-body">
      @if ($errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
      @if ($clients->isEmpty())<div class="alert alert-info">No clients yet. Set a lead's status to Converted to create a client.</div>@endif
      @if ($catalogItems->isEmpty() && $quotation->items->isEmpty())<div class="alert alert-info">Add Service or Inventory items before creating a quotation.</div>@endif
      <form method="POST" action="{{ $quotation->exists ? route('quotations.update', $quotation) : route('quotations.store') }}" id="quotation-form">
        @csrf
        @if ($quotation->exists) @method('PUT') @endif
        <div class="row g-4 mb-5">
          <div class="col-md-6"><label for="client_id" class="form-label">Client</label><select name="client_id" id="client_id" class="form-select" required><option value="">Select client</option>@foreach ($clients as $client)<option value="{{ $client->id }}" @selected((string) old('client_id', $quotation->client_id) === (string) $client->id)>{{ $client->name }}{{ $client->company_name ? ' — '.$client->company_name : '' }}</option>@endforeach</select></div>
          <div class="col-md-3"><label for="quotation_date" class="form-label">Quotation date</label><input type="date" name="quotation_date" id="quotation_date" value="{{ old('quotation_date', $quotation->quotation_date?->format('Y-m-d')) }}" class="form-control" required></div>
          <div class="col-md-3"><label for="valid_until" class="form-label">Valid until</label><input type="date" name="valid_until" id="valid_until" value="{{ old('valid_until', $quotation->valid_until?->format('Y-m-d')) }}" class="form-control"></div>
        </div>
        <p class="text-body-secondary">Service rates exclude GST; Inventory rates include GST. All amounts are in INR.</p>
        @if ($quotation->exists)<p class="text-body-secondary">Existing rows retain their quoted rates and tax. Remove a row and add it again to use current catalogue pricing.</p>@endif
        <div class="table-responsive">
          <table class="table align-middle"><thead><tr><th style="min-width: 230px">Service / Inventory</th><th>HSN / SAC</th><th style="min-width: 110px">Quantity</th><th class="text-end">Rate</th><th>GST %</th><th class="text-end">Taxable</th><th class="text-end">GST</th><th class="text-end">Total</th><th></th></tr></thead><tbody id="quotation-rows"></tbody></table>
        </div>
        <button type="button" class="btn btn-outline-primary mt-3" id="add-quotation-item">Add item</button>
        <div class="row justify-content-end mt-4"><div class="col-md-5"><dl class="row" aria-live="polite"><dt class="col-7">Taxable subtotal</dt><dd class="col-5 text-end" id="quotation-subtotal">0.00</dd><dt class="col-7">GST / tax</dt><dd class="col-5 text-end" id="quotation-tax">0.00</dd><dt class="col-7 fs-5">Grand total (INR)</dt><dd class="col-5 text-end fs-5 fw-bold" id="quotation-total">0.00</dd></dl></div></div>
        <div class="row g-4">
          <div class="col-md-6"><label for="notes" class="form-label">Notes</label><textarea name="notes" id="notes" class="form-control" rows="4" maxlength="5000">{{ old('notes', $quotation->notes) }}</textarea></div>
          <div class="col-md-6"><label for="terms" class="form-label">Terms and conditions</label><textarea name="terms" id="terms" class="form-control" rows="4" maxlength="10000">{{ old('terms', $quotation->terms) }}</textarea></div>
          <div class="col-12 d-flex gap-2"><button type="submit" class="btn btn-primary" @disabled($clients->isEmpty())>{{ $quotation->exists ? 'Save quotation' : 'Submit quotation' }}</button>@if (auth()->user()->hasPermission('view_quotations'))<a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary">Cancel</a>@endif</div>
        </div>
      </form>
    </div>
  </div>
</div>
<template id="quotation-row-template">
  <tr>
    <td><select class="form-select item-select" aria-label="Service or Inventory item" required></select><input type="hidden" class="snapshot-id"><small class="rate-basis text-body-secondary d-block mt-1"></small></td>
    <td class="item-hsn"></td>
    <td><input type="number" class="form-control item-quantity" aria-label="Quantity" min="0.001" max="99999.999" step="0.001" required></td>
    <td class="item-rate text-end"></td><td class="item-gst"></td><td class="item-subtotal text-end"></td><td class="item-tax text-end"></td><td class="item-total text-end"></td>
    <td><button type="button" class="btn btn-sm btn-outline-danger remove-item" aria-label="Remove item">Remove</button></td>
  </tr>
</template>
@endsection

@push('scripts')
<script type="module">
  import { initQuotationEditor } from '{{ asset('assets/js/quotation-editor.js') }}';
  initQuotationEditor({{ Illuminate\Support\Js::from(['catalog' => $catalogItems, 'snapshots' => $quotation->items, 'rows' => $rows]) }});
</script>
@endpush
