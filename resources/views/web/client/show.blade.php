@extends('layouts.app')
@section('content')
@php
    $resource = 'clients'; $permission = 'clients';
@endphp
<div class="container-xxl flex-grow-1 container-p-y">
@include('web.partials.show', ['title' => 'Client details', 'columns' => ['name' => 'Name', 'company_name' => 'Company name', 'email' => 'Email', 'mobile' => 'Mobile', 'type' => 'Type', 'client_code' => 'Client code', 'gst_no' => 'GST number', 'pan_no' => 'PAN number', 'billing_address' => 'Billing address', 'shipping_address' => 'Shipping address', 'country' => 'Country', 'state' => 'State', 'city' => 'City', 'zip_code' => 'Postal code', 'notes' => 'Notes', 'is_active' => 'Status']])


</div>
@endsection
