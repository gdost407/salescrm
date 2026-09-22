@extends('layouts.app')
@section('content')
@php
    $resource = 'items'; $permission = 'catalog_items';
@endphp
<div class="container-xxl flex-grow-1 container-p-y">
@include('web.partials.index', ['title' => 'Services / Inventory', 'singular' => 'Item', 'columns' => ['name' => 'Name', 'type' => 'Type', 'hsn_sac' => 'HSN / SAC', 'rate' => 'Rate', 'tax.rate' => 'GST %']])
</div>
@endsection
