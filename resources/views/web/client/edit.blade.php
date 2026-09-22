@extends('layouts.app')
@section('content')
@php
    $resource = 'clients'; $permission = 'clients';
@endphp
<div class="container-xxl flex-grow-1 container-p-y">
<h4>Edit Client</h4>
@include('web.partials.alerts')
<div class="card"><div class="card-body">@include('web.client.form')</div></div>
</div>
@endsection
