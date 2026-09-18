@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  @if (session('message'))<div class="alert alert-success" role="status">{{ session('message') }}</div>@endif
  <div class="card">
    <div class="card-header"><h4 class="mb-0">{{ $item->exists ? 'Edit item' : 'Add Service / Inventory' }}</h4></div>
    <div class="card-body">
      @if ($errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
      <form method="POST" action="{{ $item->exists ? route('catalog-items.update', $item) : route('catalog-items.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($item->exists) @method('PUT') @endif
        <div class="row g-4">
          <div class="col-md-4"><label for="type" class="form-label">Type</label><select name="type" id="type" class="form-select" required><option value="service" @selected(old('type', $item->type) === 'service')>Service — GST exclusive</option><option value="inventory" @selected(old('type', $item->type) === 'inventory')>Inventory — GST inclusive</option></select></div>
          <div class="col-md-8"><label for="name" class="form-label">Name</label><input name="name" id="name" value="{{ old('name', $item->name) }}" class="form-control" maxlength="255" required></div>
          <div class="col-md-4"><label for="hsn" class="form-label">HSN / SAC</label><input name="hsn" id="hsn" value="{{ old('hsn', $item->hsn) }}" class="form-control" maxlength="20" required></div>
          <div class="col-md-4"><label for="gst_rate" class="form-label">GST / tax (%)</label><input type="number" name="gst_rate" id="gst_rate" value="{{ old('gst_rate', $item->gst_rate) }}" class="form-control" min="0" max="100" step="0.01" required></div>
          <div class="col-md-4"><label for="rate" class="form-label">Rate (INR)</label><input type="number" name="rate" id="rate" value="{{ old('rate', $item->rate) }}" class="form-control" min="0" max="99999999.99" step="0.01" required><div class="form-text" id="rate-help">Service rates exclude GST; Inventory rates include GST.</div></div>
          <div class="col-12"><label for="description" class="form-label">Description</label><textarea name="description" id="description" class="form-control" rows="3" maxlength="5000">{{ old('description', $item->description) }}</textarea></div>
          <div class="col-md-6"><label for="image" class="form-label">Image</label><input type="file" name="image" id="image" class="form-control" accept="image/jpeg,image/png,image/webp"><div class="form-text">JPG, PNG or WebP, up to 2 MB.</div></div>
          @if ($item->image_path)
          <div class="col-md-6"><img src="{{ route('catalog-items.image', $item) }}" alt="{{ $item->name }}" class="img-thumbnail mb-2" width="120"><div class="form-check"><input type="checkbox" name="remove_image" id="remove_image" value="1" class="form-check-input" @checked(old('remove_image'))><label for="remove_image" class="form-check-label">Remove image</label></div></div>
          @endif
          <div class="col-12 d-flex gap-2"><button class="btn btn-primary" type="submit">Save item</button>@if (auth()->user()->hasPermission('view_catalog_items'))<a class="btn btn-outline-secondary" href="{{ route('catalog-items.index') }}">Cancel</a>@endif</div>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
