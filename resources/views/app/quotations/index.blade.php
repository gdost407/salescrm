@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  @if (session('message'))<div class="alert alert-success" role="status">{{ session('message') }}</div>@endif
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center gap-2"><h4 class="mb-0">Quotations</h4>@if (auth()->user()->hasPermission('create_quotations'))<a href="{{ route('quotations.create') }}" class="btn btn-primary">Create quotation</a>@endif</div>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Quotation</th><th>Client</th><th>Date</th><th class="text-end">Total (INR)</th><th>Actions</th></tr></thead><tbody>
      @forelse ($quotations as $quotation)
      <tr><td><a href="{{ route('quotations.show', $quotation) }}">{{ $quotation->number() }}</a></td><td>{{ $quotation->client_details['name'] }}</td><td>{{ $quotation->quotation_date->format('d M Y') }}</td><td class="text-end">{{ $quotation->total }}</td><td><a class="btn btn-sm btn-outline-secondary" href="{{ route('quotations.show', $quotation) }}">Details / Print</a> @if (auth()->user()->hasPermission('edit_quotations'))<a class="btn btn-sm btn-outline-primary" href="{{ route('quotations.edit', $quotation) }}">Edit</a>@endif</td></tr>
      @empty<tr><td colspan="5" class="text-center py-5">No quotations yet.</td></tr>@endforelse
    </tbody></table></div>
    <div class="card-body">{{ $quotations->links('pagination::bootstrap-5') }}</div>
  </div>
</div>
@endsection
