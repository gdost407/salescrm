@extends('layouts.app')
@section('content')
@php
    $resource = 'taxes'; $permission = 'taxes';
@endphp
<div class="container-xxl flex-grow-1 container-p-y">
@include('web.partials.index', ['title' => 'Taxes', 'singular' => 'Tax', 'columns' => ['name' => 'Name', 'rate' => 'Tax %', 'is_active' => 'Active']])
</div>
@endsection
