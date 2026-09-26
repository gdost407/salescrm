<form method="POST" action="{{ $record->exists ? route($resource.'.update', $record->id) : route($resource.'.store') }}">
@csrf
@if($record->exists) @method('PUT') @endif
<div class="row">
<p class="col-12 text-muted">Recorded independently; saving this entry does not update invoice balances or other records.</p>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['required' => old('ledger_type', $record->ledger_type ?? 'client') === 'client', 'field' => 'client_id', 'label' => 'Client', 'inputType' => 'select', 'options' => ['' => 'Select client'] + $clients->pluck('name', 'id')->all()])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['field' => 'document', 'label' => 'Related document', 'inputType' => 'select', 'value' => $record->document_type ? $record->document_type.':'.$record->document_id : '', 'options' => ['' => 'No document'] + $documents->pluck('label', 'value')->all()])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['required' => true, 'field' => 'ledger_type', 'label' => 'Ledger type', 'inputType' => 'select', 'options' => ['client' => 'Client', 'company' => 'Company']])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['required' => true, 'field' => 'transaction_date', 'label' => 'Transaction date', 'inputType' => 'date', 'value' => $record->transaction_date ?? now()->format('Y-m-d')])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['required' => true, 'field' => 'entry_type', 'label' => 'Entry type', 'inputType' => 'select', 'options' => ['debit' => 'Debit', 'credit' => 'Credit']])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['required' => true, 'field' => 'transaction_type', 'label' => 'Transaction type', 'inputType' => 'text'])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['required' => true, 'field' => 'amount', 'label' => 'Amount', 'inputType' => 'number'])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['field' => 'reference_no', 'label' => 'Reference number', 'inputType' => 'text'])</div>
<div class="col-12 col-md-6">@include('web.partials.field', ['field' => 'description', 'label' => 'Description', 'inputType' => 'textarea'])</div>
</div>
<div class="d-flex flex-wrap gap-2"><button type="submit" class="btn btn-primary"><i class="bx bx-save me-1" aria-hidden="true"></i>Save Ledger entry</button>
@if(auth()->user()->hasPermission('view_'.$permission))<a href="{{ route($resource.'.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1" aria-hidden="true"></i>Back to list</a>@endif
</div></form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const type = document.getElementById('ledger_type');
    const client = document.getElementById('client_id');
    const updateRequired = () => {
        client.required = type.value === 'client';
        client.closest('.input-group').querySelector('.input-group-text').classList.toggle('text-danger', client.required);
    };
    type.addEventListener('change', updateRequired);
    updateRequired();
});
</script>
@endpush
