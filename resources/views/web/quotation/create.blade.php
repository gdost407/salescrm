@extends('layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
<h4>{{ $record->exists ? 'Edit '.$record->{$type.'_no'} : 'Create '.ucfirst($type) }}</h4>
@include('web.partials.alerts')
<div class="card"><div class="card-body">@include('web.partials.document-form')</div></div>
</div>
@endsection
