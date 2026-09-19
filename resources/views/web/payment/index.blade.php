@extends('layouts.app')
@section('content')
@php
    $resource = 'payments'; $permission = 'payments';
@endphp
<div class="container-xxl flex-grow-1 container-p-y">
@include('web.partials.index', ['title' => 'Payments', 'singular' => 'Payment', 'columns' => ['payment_no' => 'Payment number', 'client.name' => 'Client', 'payment_date' => 'Date', 'amount' => 'Amount', 'payment_mode' => 'Mode']])
</div>
@endsection
