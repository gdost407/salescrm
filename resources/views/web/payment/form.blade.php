<form method="POST" action="{{ $record->exists ? route($resource.'.update', $record->id) : route($resource.'.store') }}">
@csrf
@if($record->exists) @method('PUT') @endif
<div class="row"><div class="col-lg-8">
<p class="text-muted">Recorded independently; saving this entry does not update invoice balances or other records.</p>
@include('web.partials.field', ['field' => 'client_id', 'label' => 'Client', 'inputType' => 'select', 'options' => ['' => 'Select client'] + $clients->pluck('name', 'id')->all()])
@include('web.partials.field', ['field' => 'document', 'label' => 'Related document', 'inputType' => 'select', 'value' => $record->document_type ? $record->document_type.':'.$record->document_id : '', 'options' => ['' => 'Select document'] + $documents->pluck('label', 'value')->all()])
@include('web.partials.field', ['field' => 'payment_no', 'label' => 'Payment number', 'inputType' => 'text'])
@include('web.partials.field', ['field' => 'payment_date', 'label' => 'Payment date', 'inputType' => 'date', 'value' => $record->payment_date ?? now()->format('Y-m-d')])
@include('web.partials.field', ['field' => 'amount', 'label' => 'Amount', 'inputType' => 'number'])
@include('web.partials.field', ['field' => 'payment_mode', 'label' => 'Payment mode', 'inputType' => 'select', 'options' => ['cash' => 'Cash', 'bank' => 'Bank', 'upi' => 'UPI', 'card' => 'Card', 'cheque' => 'Cheque', 'other' => 'Other']])
@include('web.partials.field', ['field' => 'reference_no', 'label' => 'Reference number', 'inputType' => 'text'])
@include('web.partials.field', ['field' => 'notes', 'label' => 'Notes', 'inputType' => 'textarea'])
<button type="submit" class="btn btn-primary">Save Payment</button>
@if(auth()->user()->hasPermission('view_'.$permission))<a href="{{ route($resource.'.index') }}" class="btn btn-outline-secondary">Back to list</a>@endif
</div></div></form>
