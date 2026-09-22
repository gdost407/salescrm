@extends('layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
@include('web.partials.index', ['title' => ucfirst($resource), 'singular' => ucfirst($type), 'permission' => $resource, 'columns' => [$type.'_no' => 'Number', 'client.name' => 'Client', $type.'_date' => 'Date', 'status' => 'Status', 'total_amount' => 'Total (INR)']])
</div>
@endsection
