@extends('layouts.app')

@push('styles')
<style>
    .dashboard-hero { background: linear-gradient(120deg, #eef4ff 0%, #f7f9ff 58%, #f1f7ff 100%); border: 1px solid #e4ebf7; border-radius: 1rem; }
    .dashboard-stat-card, .dashboard-panel { border: 1px solid #e8edf4; border-radius: 1rem; box-shadow: 0 .25rem 1rem rgba(38, 56, 86, .045); }
    .dashboard-stat-card { transition: transform .18s ease, box-shadow .18s ease; }
    .dashboard-stat-card:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1.25rem rgba(38, 56, 86, .09); }
    .dashboard-stat-icon { width: 2.5rem; height: 2.5rem; display: inline-flex; align-items: center; justify-content: center; border-radius: .8rem; font-size: 1.25rem; }
    .dashboard-section-title { font-size: 1rem; font-weight: 600; }
    .dashboard-table thead th { color: #718096; font-size: .72rem; font-weight: 600; letter-spacing: .045em; text-transform: uppercase; white-space: nowrap; }
    .dashboard-table > :not(caption) > * > * { padding: .85rem 1rem; }
    .dashboard-note { display: -webkit-box; overflow: hidden; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
    .dashboard-status-row:last-child { margin-bottom: 0 !important; }
    @media (max-width: 575.98px) {
        .dashboard-hero { border-radius: .8rem; }
        .dashboard-stat-card .card-body { padding: .85rem !important; }
        .dashboard-stat-icon { width: 2.1rem; height: 2.1rem; font-size: 1.05rem; }
        .dashboard-table > :not(caption) > * > * { padding: .7rem; }
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="dashboard-hero d-flex flex-wrap justify-content-between align-items-center gap-3 p-3 p-md-4 mb-4">
        <div><span class="badge bg-label-primary rounded-pill mb-2"><i class="bx bx-calendar me-1"></i>{{ now()->format('l, d M Y') }}</span><h4 class="mb-1">Welcome back, {{ auth()->user()->name }}</h4><p class="text-muted mb-0">Here’s what’s happening in your sales pipeline.</p></div>
        @if(auth()->user()->hasPermission('create_leads'))
        <a href="{{ route('sales-create-lead') }}" class="btn btn-primary rounded-pill px-3"><i class="bx bx-plus me-1"></i>Create lead</a>
        @endif
    </div>
    <div class="row">
        @if($canPunchAttendance)
            <div class="col-12 mb-3">@include('app.staff.attendance-punch')</div>
        @endif
        @forelse($cards as $card)
        @php
            $metricStyle = match ($card['label']) {
                'Total leads' => ['icon' => 'bx-group', 'tone' => 'primary'],
                'New leads' => ['icon' => 'bx-plus-circle', 'tone' => 'info'],
                'Converted leads' => ['icon' => 'bx-check-circle', 'tone' => 'success'],
                'Pending follow-ups' => ['icon' => 'bx-calendar-check', 'tone' => 'warning'],
                'Clients' => ['icon' => 'bx-user-check', 'tone' => 'success'],
                'Staff' => ['icon' => 'bx-id-card', 'tone' => 'secondary'],
                default => ['icon' => 'bx-bar-chart-alt-2', 'tone' => 'primary'],
            };
        @endphp
        <div class="col-6 col-md-4 col-xl-2 mb-3">
            <div class="card dashboard-stat-card h-100"><div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-3"><span class="dashboard-stat-icon bg-label-{{ $metricStyle['tone'] }}"><i class="bx {{ $metricStyle['icon'] }}"></i></span><a class="text-body-secondary" href="{{ route($card['route']) }}" aria-label="View {{ strtolower($card['label']) }}"><i class="bx bx-right-top-arrow-circle fs-5"></i></a></div>
                <div class="small text-muted mb-1">{{ $card['label'] }}</div>
                <div class="fs-4 fw-semibold lh-1 mb-2">{{ number_format($card['count']) }}</div>
                <small class="text-muted d-block text-truncate" title="{{ $card['hint'] }}">{{ $card['hint'] }}</small>
            </div></div>
        </div>
        @empty
        <div class="col-12"><div class="alert alert-info">No dashboard metrics are available for your role. Contact your company administrator for access.</div></div>
        @endforelse
        @if($canViewLeads)
        <div class="col-6 col-md-4 col-xl-2 mb-3">
            <div class="card dashboard-stat-card h-100"><div class="card-body p-3">
                <div class="dashboard-stat-icon bg-label-success mb-3"><i class="bx bx-trending-up"></i></div>
                <div class="small text-muted mb-1">Conversion rate</div><div class="fs-4 fw-semibold lh-1 mb-2">{{ number_format($conversionRate, 1) }}%</div>
                <small class="text-muted">Converted / visible leads</small>
            </div></div>
        </div>
        @endif
    </div>
    @if($canViewLeads)
    <div class="card dashboard-panel mb-4">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center px-3 px-md-4 pt-3">
            <div><h5 class="dashboard-section-title mb-1">Today's schedule</h5><small class="text-muted">Appointments and follow-ups happening today</small></div>
            <a class="btn btn-sm btn-label-primary rounded-pill" href="{{ route('calendar') }}">View calendar <i class="bx bx-right-arrow-alt ms-1"></i></a>
        </div>
        <div class="table-responsive">
            <table class="table dashboard-table table-hover align-middle mb-0">
                <thead><tr><th>Lead name</th><th>Title</th><th>Assigned to</th><th>Status</th><th>Timing</th><th>Actions</th></tr></thead>
                <tbody id="dashboard-scheduled-events"><tr><td colspan="6" class="text-center py-4 text-muted">Loading today's events...</td></tr></tbody>
            </table>
        </div>
    </div>
    <div class="card dashboard-panel mb-4">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center px-3 px-md-4 pt-3">
            <div><h5 class="dashboard-section-title mb-1">Upcoming follow-ups</h5><small class="text-muted">Pending follow-ups with notes added to leads</small></div>
            <a class="btn btn-sm btn-label-primary rounded-pill" href="{{ route('calendar') }}">View calendar <i class="bx bx-right-arrow-alt ms-1"></i></a>
        </div>
        <div class="table-responsive">
            <table class="table dashboard-table table-hover align-middle mb-0">
                <thead><tr><th>Lead</th><th>Follow-up note</th><th>Scheduled</th><th>Added by</th></tr></thead>
                <tbody>
                    @forelse($dashboardFollowups as $followup)
                    <tr>
                        <td><a class="fw-semibold text-body" href="{{ route('sales-lead-view', $followup->lead) }}">{{ $followup->lead->name }}</a></td>
                        <td><strong>{{ $followup->subject ?: 'Follow-up' }}</strong>@if($followup->summary)<small class="dashboard-note d-block text-muted" title="{{ $followup->summary }}">{{ $followup->summary }}</small>@endif</td>
                        <td class="text-nowrap">{{ $followup->scheduled_at->format('d M Y, h:i A') }}</td>
                        <td>{{ $followup->user?->name ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-4 text-muted">No pending follow-ups.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
        <div><h5 class="dashboard-section-title mb-1">Lead overview</h5><small class="text-muted">{{ auth()->user()->hasPermission('view_all_leads') ? 'Showing company leads.' : 'Showing only leads assigned to you.' }} Deleted leads are excluded.</small></div>
        <a href="{{ route('sale-kanban') }}" class="small fw-semibold">Open sales pipeline <i class="bx bx-right-arrow-alt"></i></a>
    </div>
    <div class="row">
        <div class="col-lg-4 mb-4"><div class="card dashboard-panel h-100">
            <div class="card-header bg-transparent border-0 px-3 px-md-4 pt-3"><h5 class="dashboard-section-title mb-1">Status breakdown</h5><small class="text-muted">How leads are progressing</small></div>
            <div class="card-body px-3 px-md-4 pt-2">
                @forelse($statuses as $status)
                <div class="dashboard-status-row mb-4">
                    <div class="d-flex justify-content-between mb-2"><span>{{ $status['label'] }}</span><strong>{{ number_format($status['count']) }} <small class="text-muted">({{ $status['percentage'] }}%)</small></strong></div>
                    <div class="progress rounded-pill" style="height:7px"><div class="progress-bar rounded-pill" role="progressbar" style="width:{{ $status['percentage'] }}%" aria-label="{{ $status['label'] }}" aria-valuenow="{{ $status['percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div></div>
                </div>
                @empty
                <p class="text-muted mb-0">No leads yet. Your lead statuses will appear here.</p>
                @endforelse
            </div>
        </div></div>
        <div class="col-lg-8 mb-4"><div class="card dashboard-panel h-100">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center px-3 px-md-4 pt-3"><div><h5 class="dashboard-section-title mb-1">Recent leads</h5><small class="text-muted">Latest additions to your pipeline</small></div><a class="btn btn-sm btn-label-primary rounded-pill" href="{{ route('sales-all-list') }}">View all</a></div>
            <div class="table-responsive"><table class="table dashboard-table table-hover align-middle mb-0">
                <thead><tr><th>Name</th><th>Company</th><th>Status</th><th>Created</th></tr></thead>
                <tbody>
                @forelse($recentLeads as $lead)
                <tr><td><a class="fw-semibold text-body" href="{{ route('sales-lead-view', $lead) }}">{{ $lead->name }}</a></td><td>{{ $lead->company_name ?: '—' }}</td><td><span class="badge bg-label-primary rounded-pill">{{ $lead->status ?: 'Unspecified' }}</span></td><td class="text-nowrap text-muted">{{ $lead->created_at->format('d M Y') }}</td></tr>
                @empty
                <tr><td colspan="4" class="text-center py-4 text-muted">No leads available.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div></div>
    </div>
    @endif
</div>
@if($canViewLeads)
    @include('app.calendar.event-modals')
@endif
@endsection

@if($canViewLeads)
@push('scripts')
<script>
    (() => {
        const state = { events: [] };
        const tableBody = document.getElementById('dashboard-scheduled-events');
        const timeFormatter = new Intl.DateTimeFormat(undefined, { hour: 'numeric', minute: '2-digit' });
        const escapeHtml = (value) => String(value ?? '-').replace(/[&<>'"]/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' })[character]);
        const dateKey = (date) => [date.getFullYear(), String(date.getMonth() + 1).padStart(2, '0'), String(date.getDate()).padStart(2, '0')].join('-');
        const visibleEventsForDate = (date) => state.events.filter((event) => event.start.slice(0, 10) === dateKey(date));
        const loadEvents = async () => {
            const today = @json(now()->toDateString());
            const query = new URLSearchParams({ start: `${today} 00:00:00`, end: `${today} 23:59:59` });
            try {
                const response = await fetch(`{{ route('calendar.events') }}?${query}`, { headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('Unable to load scheduled events. Please refresh the page.');
                state.events = await response.json();
                tableBody.innerHTML = state.events.length === 0
                    ? '<tr><td colspan="6" class="text-center py-4 text-muted">No scheduled events today.</td></tr>'
                    : state.events.map((event) => {
                        const props = event.extendedProps;
                        const pending = props.status === 'pending';
                        return `<tr>
                            <td>${escapeHtml(props.leadName)}</td>
                            <td><button type="button" class="btn btn-link p-0 text-start" data-dashboard-event="${event.id}">${escapeHtml(props.subject || event.title)}</button></td>
                            <td>${escapeHtml(props.assignedTo || 'Unassigned')}</td>
                            <td><span class="badge ${pending ? 'bg-label-warning' : 'bg-label-success'}">${escapeHtml(props.status)}</span></td>
                            <td class="text-nowrap">${escapeHtml(timeFormatter.format(new Date(event.start)))}</td>
                            <td><div class="d-flex gap-1">
                                <button type="button" class="btn btn-xs btn-outline-primary" data-dashboard-event="${event.id}" title="View event">View</button>
                                ${pending && props.canEdit ? `<button type="button" class="btn btn-xs btn-outline-primary" data-calendar-edit="${event.id}" title="Edit activity">Edit</button>` : ''}
                                ${pending && props.canComplete ? `<button type="button" class="btn btn-xs btn-outline-success" data-calendar-complete="${event.id}" title="Complete activity">Complete</button>` : ''}
                            </div></td>
                        </tr>`;
                    }).join('');
            } catch (error) {
                tableBody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-danger">${escapeHtml(error.message)}</td></tr>`;
            }
        };

        @include('app.calendar.event-actions')

        tableBody.addEventListener('click', (clickEvent) => {
            const button = clickEvent.target.closest('[data-dashboard-event]');
            if (!button) return;
            const event = state.events.find((item) => String(item.id) === button.dataset.dashboardEvent);
            if (event) openDayModal(new Date(event.start), [event]);
        });
        loadEvents();
    })();
</script>
@endpush
@endif
