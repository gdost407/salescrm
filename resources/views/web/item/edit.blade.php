@extends('layouts.app')
@section('content')
@php
    $resource = 'items'; $permission = 'catalog_items';
@endphp
<div class="container-xxl flex-grow-1 container-p-y">
<h4>Edit Item</h4>
@include('web.partials.alerts')
<div class="card"><div class="card-body">@include('web.item.form')</div></div>
</div>
@endsection
