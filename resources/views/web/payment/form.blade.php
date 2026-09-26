<form method="POST" action="{{ $record->exists ? route($resource.'.update', $record->id) : route($resource.'.store') }}">
@csrf
@if($record->exists) @method('PUT') @endif
<div class="row">
<p class="col-12 text-muted">Recorded independently; saving this entry does not update invoice balances or other records.</p>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['required' => true, 'field' => 'client_id', 'label' => 'Client', 'inputType' => 'select', 'options' => ['' => 'Select client'] + $clients->pluck('name', 'id')->all()])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['required' => true, 'field' => 'document', 'label' => 'Related document', 'inputType' => 'select', 'value' => $record->document_type ? $record->document_type.':'.$record->document_id : '', 'options' => ['' => 'Select document'] + $documents->pluck('label', 'value')->all()])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['required' => true, 'field' => 'payment_no', 'label' => 'Payment number', 'inputType' => 'text'])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['required' => true, 'field' => 'payment_date', 'label' => 'Payment date', 'inputType' => 'date', 'value' => $record->payment_date ?? now()->format('Y-m-d')])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['required' => true, 'field' => 'amount', 'label' => 'Amount', 'inputType' => 'number'])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['required' => true, 'field' => 'payment_mode', 'label' => 'Payment mode', 'inputType' => 'select', 'options' => ['cash' => 'Cash', 'bank' => 'Bank', 'upi' => 'UPI', 'card' => 'Card', 'cheque' => 'Cheque', 'other' => 'Other']])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['field' => 'reference_no', 'label' => 'Reference number', 'inputType' => 'text'])</div>
<div class="col-12 col-md-6">@include('web.partials.field', ['field' => 'notes', 'label' => 'Notes', 'inputType' => 'textarea'])</div>
</div>
<div class="d-flex flex-wrap gap-2"><button type="submit" class="btn btn-primary"><i class="bx bx-save me-1" aria-hidden="true"></i>Save Payment</button>
@if(auth()->user()->hasPermission('view_'.$permission))<a href="{{ route($resource.'.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1" aria-hidden="true"></i>Back to list</a>@endif
</div></form>
