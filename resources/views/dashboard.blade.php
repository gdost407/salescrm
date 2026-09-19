@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h4 class="mb-1">Welcome, {{ auth()->user()->name }}</h4><p class="text-muted mb-0">Your CRM overview &middot; {{ now()->format('d M Y') }}</p></div>
        @if(auth()->user()->hasPermission('create_leads'))
        <a href="{{ route('sales-create-lead') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i>Create lead</a>
        @endif
    </div>
    <div class="row">
        @forelse($cards as $card)
        <div class="col-12 col-sm-6 col-xl-4 mb-4">
            <div class="card h-100"><div class="card-body">
                <h6 class="text-primary">{{ $card['label'] }}</h6>
                <h2 class="mb-1">{{ number_format($card['count']) }}</h2>
                <p class="text-muted">{{ $card['hint'] }}</p>
                <a href="{{ route($card['route']) }}">View records <i class="bx bx-right-arrow-alt" aria-hidden="true"></i></a>
            </div></div>
        </div>
        @empty
        <div class="col-12"><div class="alert alert-info">No dashboard metrics are available for your role. Contact your company administrator for access.</div></div>
        @endforelse
        @if($canViewLeads)
        <div class="col-12 col-sm-6 col-xl-4 mb-4">
            <div class="card h-100"><div class="card-body">
                <h6 class="text-success">Conversion rate</h6><h2>{{ number_format($conversionRate, 1) }}%</h2>
                <p class="text-muted mb-0">Converted leads as a share of all visible leads.</p>
            </div></div>
        </div>
        @endif
    </div>
    @if($canViewLeads)
    <p class="text-muted">{{ auth()->user()->hasPermission('view_all_leads') ? 'Showing company leads.' : 'Showing only leads assigned to you.' }} Deleted leads are excluded.</p>
    <div class="row">
        <div class="col-lg-4 mb-4"><div class="card h-100">
            <h5 class="card-header">Lead status breakdown</h5>
            <div class="card-body">
                @forelse($statuses as $status)
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2"><span>{{ $status['label'] }}</span><strong>{{ number_format($status['count']) }} <small class="text-muted">({{ $status['percentage'] }}%)</small></strong></div>
                    <div class="progress" style="height:8px"><div class="progress-bar" role="progressbar" style="width:{{ $status['percentage'] }}%" aria-label="{{ $status['label'] }}" aria-valuenow="{{ $status['percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div></div>
                </div>
                @empty
                <p class="text-muted mb-0">No leads yet. Your lead statuses will appear here.</p>
                @endforelse
            </div>
        </div></div>
        <div class="col-lg-8 mb-4"><div class="card h-100">
            <div class="card-header d-flex justify-content-between"><h5 class="mb-0">Recent leads</h5><a href="{{ route('sales-all-list') }}">View all</a></div>
            <div class="table-responsive"><table class="table">
                <thead><tr><th>Name</th><th>Company</th><th>Status</th><th>Created</th></tr></thead>
                <tbody>
                @forelse($recentLeads as $lead)
                <tr><td><a href="{{ route('sales-lead-view', $lead) }}">{{ $lead->name }}</a></td><td>{{ $lead->company_name ?: '—' }}</td><td><span class="badge bg-label-primary">{{ $lead->status ?: 'Unspecified' }}</span></td><td class="text-nowrap">{{ $lead->created_at->format('d M Y') }}</td></tr>
                @empty
                <tr><td colspan="4" class="text-center py-4 text-muted">No leads available.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div></div>
    </div>
    @endif
</div>
@endsection
