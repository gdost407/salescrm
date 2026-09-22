@extends('layouts.app')
@section('content')
@php
    $resource = 'ledger'; $permission = 'ledger';
@endphp
<div class="container-xxl flex-grow-1 container-p-y">
@include('web.partials.show', ['title' => 'Ledger entry details', 'columns' => ['transaction_date' => 'Transaction date', 'client.name' => 'Client', 'entry_type' => 'Entry type', 'transaction_type' => 'Transaction type', 'amount' => 'Amount', 'ledger_type' => 'Ledger type', 'reference_no' => 'Reference number', 'description' => 'Description']])


</div>
@endsection
