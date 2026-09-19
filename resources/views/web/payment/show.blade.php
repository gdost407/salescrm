@extends('layouts.app')
@section('content')
@php
    $resource = 'payments'; $permission = 'payments';
@endphp
<div class="container-xxl flex-grow-1 container-p-y">
@include('web.partials.show', ['title' => 'Payment details', 'columns' => ['payment_no' => 'Payment number', 'client.name' => 'Client', 'payment_date' => 'Payment date', 'amount' => 'Amount', 'payment_mode' => 'Payment mode', 'reference_no' => 'Reference number', 'notes' => 'Notes']])

<a class="btn btn-primary mt-3" href="{{ route('payments.print', $record) }}" target="_blank" rel="noopener">Print receipt</a>
</div>
@endsection
