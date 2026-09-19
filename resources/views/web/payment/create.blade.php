@extends('layouts.app')
@section('content')
@php
    $resource = 'payments'; $permission = 'payments';
@endphp
<div class="container-xxl flex-grow-1 container-p-y">
<h4>Create Payment</h4>
@include('web.partials.alerts')
<div class="card"><div class="card-body">@include('web.payment.form')</div></div>
</div>
@endsection
