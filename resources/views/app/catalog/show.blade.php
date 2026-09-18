@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  @if (session('message'))<div class="alert alert-success" role="status">{{ session('message') }}</div>@endif
  <div class="card">
    <div class="card-header d-flex justify-content-between gap-2"><h4 class="mb-0">{{ $item->name }}</h4><a href="{{ route('catalog-items.index') }}" class="btn btn-outline-secondary">Back to items</a></div>
    <div class="card-body">
      @if ($item->image_path)<img src="{{ route('catalog-items.image', $item) }}" alt="{{ $item->name }}" class="img-fluid rounded mb-4" style="max-height: 260px">@endif
      <dl class="row"><dt class="col-sm-3">Type</dt><dd class="col-sm-9">{{ ucfirst($item->type) }}</dd><dt class="col-sm-3">HSN / SAC</dt><dd class="col-sm-9">{{ $item->hsn }}</dd><dt class="col-sm-3">GST / tax</dt><dd class="col-sm-9">{{ $item->gst_rate }}%</dd><dt class="col-sm-3">Rate</dt><dd class="col-sm-9">INR {{ $item->rate }} ({{ $item->type === 'service' ? 'GST exclusive' : 'GST inclusive' }})</dd></dl>
      <p class="text-break" style="white-space: pre-line">{{ $item->description }}</p>
      @if (auth()->user()->hasPermission('edit_catalog_items'))<a class="btn btn-primary" href="{{ route('catalog-items.edit', $item) }}">Edit item</a>@endif
    </div>
  </div>
</div>
@endsection
