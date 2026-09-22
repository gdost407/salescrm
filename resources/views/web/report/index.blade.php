@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <h4 class="mb-0">{{ $title }}</h4>
        <a class="btn btn-success" href="{{ route('reports.export', ['type' => $type] + \Illuminate\Support\Arr::except($filters, ['page', 'per_page'])) }}"><i class="bx bx-download me-1"></i>Export Excel (all matches)</a>
    </div>
    @include('web.partials.alerts')
    <div class="card mb-4"><div class="card-body">
        <form action="{{ route('reports.index', ['type' => $type]) }}" method="GET">
            <div class="row">
                <div class="col-md-4 mb-3"><label for="q" class="form-label">Search</label><input class="form-control" name="q" id="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ $type === 'lead' ? 'Name, company, email or mobile' : 'Number / reference, client or notes' }}"></div>
                <div class="col-md-3 mb-3"><label for="date_from" class="form-label">From date</label><input type="date" class="form-control" id="date_from" name="date_from" value="{{ $filters['date_from'] ?? '' }}"></div>
                <div class="col-md-3 mb-3"><label for="date_to" class="form-label">To date</label><input type="date" class="form-control" id="date_to" name="date_to" value="{{ $filters['date_to'] ?? '' }}"></div>
                <div class="col-md-2 mb-3"><label for="per_page" class="form-label">Rows per page</label><select class="form-select" name="per_page" id="per_page">@foreach([25, 50, 100, 250] as $size)<option value="{{ $size }}" @selected((int) ($filters['per_page'] ?? 50) === $size)>{{ $size }}</option>@endforeach</select></div>
                @foreach($options as $field => $choices)
                <div class="col-md-3 mb-3">
                    <label for="{{ $field }}" class="form-label">{{ ['client_id' => 'Client', 'assigned_to' => 'Assigned to'][$field] ?? ucfirst(str_replace('_', ' ', $field)) }}</label>
                    <select class="form-select" id="{{ $field }}" name="{{ $field }}">
                        <option value="">All</option>
                        @foreach($choices as $value => $label)
                        <option value="{{ $value }}" @selected((string) ($filters[$field] ?? '') === (string) $value)>{{ ucfirst(str_replace('_', ' ', $label)) }}</option>
                        @endforeach
                    </select>
                </div>
                @endforeach
            </div>
            <button type="submit" class="btn btn-primary">Apply filters</button>
            <a href="{{ route('reports.index', ['type' => $type]) }}" class="btn btn-outline-secondary">Reset</a>
        </form>
    </div></div>
    <p class="text-muted">Dates filter {{ $type === 'lead' ? 'lead creation' : ($type === 'ledger' ? 'ledger transactions' : $type.' dates') }}. Scroll horizontally to see all columns. Excel includes every matching record, across all pages.</p>
    @if($type === 'invoice')<p class="text-muted">Paid and balance amounts are stored invoice values. Independent payment records do not update these amounts.</p>@endif
    <div class="card">
        <div class="card-header">{{ number_format($records->total()) }} matching records @if($records->total()) &middot; Showing {{ $records->firstItem() }}–{{ $records->lastItem() }} @endif</div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm">
                <thead><tr>@foreach($columns as $label)<th class="text-nowrap">{{ $label }}</th>@endforeach</tr></thead>
                <tbody>
                    @forelse($rows as $row)
                    <tr>@foreach($row as $field => $value)<td class="{{ in_array($field, \App\Actions\SalesReport::NUMERIC, true) ? 'text-end' : '' }}" style="min-width:140px;max-width:360px;white-space:pre-wrap;overflow-wrap:anywhere">{{ $value !== '' ? $value : '—' }}</td>@endforeach</tr>
                    @empty
                    <tr><td colspan="{{ count($columns) }}" class="text-center py-5">No records match these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $records->links('pagination::bootstrap-5') }}</div>
    </div>
</div>
@endsection
