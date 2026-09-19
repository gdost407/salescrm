@extends('layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
@include('web.partials.alerts')
<div class="d-flex justify-content-between mb-3 d-print-none">
@include('web.partials.actions', ['permission' => $resource])
<a class="btn btn-primary" target="_blank" rel="noopener" href="{{ route($resource.'.print', $record->id) }}">Print / Save PDF</a>
</div>
@include('web.partials.document-details')
@include('web.partials.attachments')
@include('web.partials.payment-summary')
</div>
@endsection
