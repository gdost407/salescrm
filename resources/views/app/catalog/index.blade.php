@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  @if (session('message'))<div class="alert alert-success" role="status">{{ session('message') }}</div>@endif
  <div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
      <h4 class="mb-0">Services / Inventory</h4>
      @if (auth()->user()->hasPermission('create_catalog_items'))<a href="{{ route('catalog-items.create') }}" class="btn btn-primary">Add item</a>@endif
    </div>
    <div class="card-body">
      <form method="GET" class="d-flex flex-wrap gap-2 mb-4">
        <select name="type" class="form-select w-auto" aria-label="Item type">
          <option value="">All types</option>
          <option value="service" @selected(($filters['type'] ?? '') === 'service')>Services</option>
          <option value="inventory" @selected(($filters['type'] ?? '') === 'inventory')>Inventory</option>
        </select>
        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control w-auto" placeholder="Search items" aria-label="Search items">
        <button class="btn btn-outline-primary">Search</button>
      </form>
      <p class="text-body-secondary">Service rates exclude GST. Inventory rates include GST.</p>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead><tr><th>Item</th><th>Type</th><th>HSN / SAC</th><th>GST</th><th class="text-end">Rate (INR)</th><th>Actions</th></tr></thead>
          <tbody>
            @forelse ($items as $item)
            <tr>
              <td><a href="{{ route('catalog-items.show', $item) }}">{{ $item->name }}</a></td>
              <td>{{ ucfirst($item->type) }}</td><td>{{ $item->hsn }}</td><td>{{ $item->gst_rate }}%</td><td class="text-end">{{ $item->rate }}<small class="d-block text-body-secondary">{{ $item->type === 'service' ? 'Excl. GST' : 'Incl. GST' }}</small></td>
              <td><div class="d-flex gap-2">
                @if (auth()->user()->hasPermission('edit_catalog_items'))<a class="btn btn-sm btn-outline-primary" href="{{ route('catalog-items.edit', $item) }}">Edit</a>@endif
                @if (auth()->user()->hasPermission('delete_catalog_items'))
                <form method="POST" action="{{ route('catalog-items.destroy', $item) }}" onsubmit="return confirm('Delete this item? Existing quotations will be preserved.');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
                @endif
              </div></td>
            </tr>
            @empty<tr><td colspan="6" class="text-center py-5">No items found.</td></tr>@endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-4">{{ $items->links('pagination::bootstrap-5') }}</div>
    </div>
  </div>
</div>
@endsection
