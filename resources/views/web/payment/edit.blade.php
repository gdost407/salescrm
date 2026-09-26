@extends('layouts.app')
@section('content')
@php
    $resource = 'payments'; $permission = 'payments';
@endphp
<div class="container-xxl flex-grow-1 container-p-y">
@include('web.partials.alerts')
<div class="card"><div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
<h5 class="mb-0">Edit Payment</h5>
<small class="text-body-secondary">Red icons indicate required fields.</small>
</div><div class="card-body">@include('web.payment.form')</div></div>
</div>
@endsection
