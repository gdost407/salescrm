@extends('layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
@include('web.partials.alerts')
<div class="card"><div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
<h5 class="mb-0">{{ $record->exists ? 'Edit '.$record->{$type.'_no'} : 'Create '.ucfirst($type) }}</h5>
<small class="text-body-secondary">Red icons indicate required fields.</small>
</div><div class="card-body">@include('web.partials.document-form')</div></div>
</div>
@endsection
