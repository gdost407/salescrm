@extends('web.partials.print-layout', ['printTitle' => ucfirst($type).' '.$record->{$type.'_no'}])
@section('print-content')
@include('web.partials.document-details')
@endsection
