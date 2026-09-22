@extends('layouts.app')
@section('content')
@php
    $resource = 'items'; $permission = 'catalog_items';
@endphp
<div class="container-xxl flex-grow-1 container-p-y">
@include('web.partials.show', ['title' => 'Item details', 'columns' => ['name' => 'Name', 'type' => 'Type', 'hsn_sac' => 'HSN / SAC', 'rate' => 'Rate', 'tax.rate' => 'GST %', 'description' => 'Description', 'sku' => 'SKU', 'is_active' => 'Status']])
<div class="card mt-3"><div class="card-body"><p>GST {{ $record->tax_type }}</p>
@if($record->image)<img class="img-fluid" style="max-height:240px" src="{{ route('items.image', $record) }}" alt="{{ $record->name }}">@endif</div></div>

</div>
@endsection
