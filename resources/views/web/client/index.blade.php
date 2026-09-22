@extends('layouts.app')
@section('content')
@php
    $resource = 'clients'; $permission = 'clients';
@endphp
<div class="container-xxl flex-grow-1 container-p-y">
@include('web.partials.index', ['title' => 'Clients', 'singular' => 'Client', 'columns' => ['name' => 'Name', 'company_name' => 'Company', 'email' => 'Email', 'mobile' => 'Mobile']])
</div>
@endsection
