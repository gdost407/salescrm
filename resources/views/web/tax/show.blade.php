@extends('layouts.app')
@section('content')
@php
    $resource = 'taxes'; $permission = 'taxes';
@endphp
<div class="container-xxl flex-grow-1 container-p-y">
@include('web.partials.show', ['title' => 'Tax details', 'columns' => ['name' => 'Name', 'rate' => 'Tax rate (%)', 'is_active' => 'Status']])


</div>
@endsection
