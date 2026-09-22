@extends('web.partials.print-layout', ['printTitle' => 'Payment receipt '.$record->payment_no])
@section('print-content')
<div class="card"><div class="card-body">
<h3>{{ $record->company->name }}</h3><p>{{ $record->company->address }}</p>
<h4>Payment receipt — {{ $record->payment_no }}</h4>
<dl class="row">
<dt class="col-4">Client</dt><dd class="col-8">{{ $record->client->name }}</dd>
<dt class="col-4">Date</dt><dd class="col-8">{{ $record->payment_date->format('d M Y') }}</dd>
<dt class="col-4">Amount (INR)</dt><dd class="col-8">{{ $record->amount }}</dd>
<dt class="col-4">Mode</dt><dd class="col-8">{{ ucfirst($record->payment_mode) }}</dd>
<dt class="col-4">Reference</dt><dd class="col-8">{{ $record->reference_no }}</dd>
</dl><p>{{ $record->notes }}</p>
<p class="text-end mt-5">Authorized signatory</p>
</div></div>
@endsection
