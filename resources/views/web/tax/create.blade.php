@extends('layouts.app')
@section('content')
@php
    $resource = 'taxes'; $permission = 'taxes';
@endphp
<div class="container-xxl flex-grow-1 container-p-y">
<h4>Create Tax</h4>
@include('web.partials.alerts')
<div class="card"><div class="card-body">@include('web.tax.form')</div></div>
</div>
@endsection
