@extends('layouts.app')
@section('content')
@php
    $resource = 'ledger'; $permission = 'ledger';
@endphp
<div class="container-xxl flex-grow-1 container-p-y">
<h4>Create Ledger entry</h4>
@include('web.partials.alerts')
<div class="card"><div class="card-body">@include('web.ledger.form')</div></div>
</div>
@endsection
