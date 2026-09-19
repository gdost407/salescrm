@extends('layouts.app')
@section('content')
@php
    $resource = 'ledger'; $permission = 'ledger';
@endphp
<div class="container-xxl flex-grow-1 container-p-y">
@include('web.partials.index', ['title' => 'Ledger', 'singular' => 'Ledger entry', 'columns' => ['transaction_date' => 'Date', 'client.name' => 'Client', 'entry_type' => 'Debit / Credit', 'transaction_type' => 'Transaction', 'amount' => 'Amount']])
</div>
@endsection
