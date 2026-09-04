@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-none d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div><h4 class="fw-bold mb-1">Sales pipeline</h4><p class="text-body-secondary mb-0">Move leads through your current status workflow.</p></div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create-lead-modal"><i class="bx bx-plus me-1"></i>New lead</button>
    </div>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <form class="d-flex gap-2" data-kanban-filter-form>
            <input class="form-control form-control-sm" name="search" value="{{ $filters['search'] }}" placeholder="Search leads" aria-label="Search leads">
            <button class="btn btn-sm btn-primary" type="submit" title="Filter leads"><i class="bx bx-search"></i></button>
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="offcanvas" data-bs-target="#kanban-filter" title="More filters"><i class="bx bx-filter-alt"></i></button>
        </form>
        <a href="{{ route('sales-create-lead') }}" class="btn btn-sm btn-primary"><i class="bx bx-plus me-1"></i>New lead</a>
    </div>
    <div id="kanban-alert" class="alert d-none" role="alert"></div>
    <div class="kanban-board d-flex gap-3 overflow-auto pb-3" data-kanban-board>
        @foreach ($statuses as $status)
            <section class="kanban-column card flex-shrink-0" data-status="{{ $status }}" style="width: 19rem;">
                <div class="card-header d-flex justify-content-between align-items-center"><h5 class="mb-0">{{ $status }}</h5><span class="badge bg-label-primary" data-count>{{ $leadCounts->get($status, 0) }}</span></div>
                <div class="card-body kanban-dropzone" data-status="{{ $status }}" data-page="1" data-loading="false" style="height: 65vh; overflow-y: auto;"></div>
            </section>
        @endforeach
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="kanban-filter" aria-labelledby="kanban-filter-label">
    <div class="offcanvas-header"><h5 id="kanban-filter-label" class="offcanvas-title">Filter leads</h5><button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button></div>
    <div class="offcanvas-body">
        <form data-kanban-filter-form>
            <input type="hidden" name="search" value="{{ $filters['search'] }}">
            <label class="form-label">Date range</label>
            <select class="form-select mb-3" name="date_range"><option value="">Any time</option>@foreach (['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', '3_month' => '3 Months', 'year' => 'This Year', 'custom' => 'Custom'] as $value => $label)<option value="{{ $value }}" @selected($filters['date_range'] === $value)>{{ $label }}</option>@endforeach</select>
            <div class="row g-2 mb-3"><div class="col-6"><label class="form-label">From</label><input class="form-control" type="date" name="date_from" value="{{ $filters['date_from'] }}"></div><div class="col-6"><label class="form-label">To</label><input class="form-control" type="date" name="date_to" value="{{ $filters['date_to'] }}"></div></div>
            <label class="form-label">Stage</label><select class="form-select mb-3" name="stage"><option value="">All stages</option>@foreach ($filterOptions['stages'] ?? [] as $option)<option value="{{ $option->name }}" @selected($filters['stage'] === $option->name)>{{ $option->name }}</option>@endforeach</select>
            <label class="form-label">Status</label><select class="form-select mb-3" name="status"><option value="">All statuses</option>@foreach ($filterOptions['statuses'] ?? [] as $option)<option value="{{ $option->name }}" @selected($filters['status'] === $option->name)>{{ $option->name }}</option>@endforeach</select>
            <label class="form-label">Source</label><select class="form-select mb-3" name="source"><option value="">All sources</option>@foreach ($filterOptions['sources'] ?? [] as $option)<option value="{{ $option->name }}" @selected($filters['source'] === $option->name)>{{ $option->name }}</option>@endforeach</select>
            <label class="form-label">Contact person</label><select class="form-select mb-3" name="assigned_to"><option value="">All contact persons</option>@foreach ($filterOptions['users'] ?? [] as $user)<option value="{{ $user->id }}" @selected((string) $filters['assigned_to'] === (string) $user->id)>{{ $user->name }}</option>@endforeach</select>
            <button class="btn btn-primary" type="submit">Apply filters</button>
        </form>
    </div>
</div>

<div class="modal fade" id="create-lead-modal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
    <form method="POST" action="{{ route('sales-leads.store') }}" data-create-lead-form>@csrf
        <div class="modal-header"><h5 class="modal-title">New lead</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div class="row g-3">
            <div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="name" required></div><div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email"></div>
            <div class="col-md-6"><label class="form-label">Mobile</label><input class="form-control" name="mobile"></div><div class="col-md-6"><label class="form-label">Job title</label><input class="form-control" name="job_title"></div>
            <div class="col-md-4"><label class="form-label">Value</label><input class="form-control" type="number" step="0.01" min="0" name="deal_amount"></div>
            <div class="col-md-4"><label class="form-label">Stage</label><select class="form-select" name="stage" required>@foreach ($settings->get('stage', collect()) as $setting)<option value="{{ $setting->name }}">{{ $setting->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label">Status</label><select class="form-select" name="status" required>@foreach ($statuses as $status)<option value="{{ $status }}">{{ $status }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label">Source</label><select class="form-select" name="source" required>@foreach ($settings->get('source', collect()) as $setting)<option value="{{ $setting->name }}">{{ $setting->name }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label">Assigned to</label><select class="form-select" name="assigned_to"><option value="">Unassigned</option>@foreach ($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></div>
            <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"></textarea></div>
        </div></div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Create lead</button></div>
    </form>
</div></div></div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="lead-detail-modal" aria-labelledby="lead-detail-title"><div class="offcanvas-header"><h5 class="offcanvas-title" id="lead-detail-title" data-detail-name></h5><button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button></div><div class="offcanvas-body" data-detail-body></div></div>
<div class="modal fade" id="activity-modal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content"><form method="POST" data-activity-form>@csrf<div class="modal-header"><h5 class="modal-title">Add activity</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="activity_type" value="notes"><div class="d-flex flex-wrap gap-2 mb-3">@foreach (['notes' => 'Note', 'call' => 'Call', 'followup' => 'Follow-up', 'visit' => 'Visit', 'gmeet' => 'Meeting', 'email' => 'Email'] as $type => $label)<button type="button" class="btn btn-sm btn-outline-primary" data-activity-type="{{ $type }}">{{ $label }}</button>@endforeach</div><div data-activity-fields></div></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save activity</button></div></form></div></div></div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const token = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const detailModal = new bootstrap.Offcanvas(document.getElementById('lead-detail-modal'));
    const activityModal = new bootstrap.Modal(document.getElementById('activity-modal'));
    const createModal = new bootstrap.Modal(document.getElementById('create-lead-modal'));
    const statusUrl = @json(route('sales-leads.status', '__lead__'));
    const detailsUrl = @json(route('sales-leads.kanban-details', '__lead__'));
    const activityUrl = @json(route('sales-lead-activities.store', '__lead__'));
    const dataUrl = @json(route('sales-leads.kanban-data'));
    const assigneeUrl = @json(route('sales-leads.assignee', '__lead__'));
    const board = document.querySelector('[data-kanban-board]');
    const activityForm = document.querySelector('[data-activity-form]');
    const showAlert = (message, type = 'success') => { const alert = document.querySelector('#kanban-alert'); alert.className = `alert alert-${type}`; alert.textContent = message; window.setTimeout(() => alert.classList.add('d-none'), 3500); };
    const refreshCounts = () => document.querySelectorAll('[data-count]').forEach((count) => { count.textContent = count.closest('.kanban-column').dataset.total ?? 0; });
    const filterData = () => Object.fromEntries(new FormData(document.querySelector('#kanban-filter form')).entries());
    const loadColumn = async (zone, reset = false) => {
        if (zone.dataset.loading === 'true' || (!reset && zone.dataset.nextPage === '')) return;
        zone.dataset.loading = 'true';
        const page = reset ? 1 : (zone.dataset.nextPage || 1);
        const params = new URLSearchParams({ ...filterData(), status: zone.dataset.status, page });
        const response = await fetch(`${dataUrl}?${params}`, { headers: { Accept: 'application/json' } });
        if (response.ok) {
            const data = await response.json();
            if (reset) zone.innerHTML = '';
            zone.insertAdjacentHTML('beforeend', data.html);
            zone.dataset.nextPage = data.nextPage ?? '';
            zone.closest('.kanban-column').dataset.total = data.total;
            refreshCounts();
        }
        zone.dataset.loading = 'false';
    };
    const loadBoard = () => document.querySelectorAll('.kanban-dropzone').forEach((zone) => { zone.dataset.nextPage = 1; loadColumn(zone, true); });
    const activityFields = {
        notes: '<label class="form-label">Note</label><textarea class="form-control" name="summary" rows="5" required></textarea>',
        call: '<label class="form-label">Call note</label><textarea class="form-control" name="summary" rows="5" required></textarea>',
        followup: '<div class="mb-3"><label class="form-label">Follow-up note</label><textarea class="form-control" name="summary" rows="3"></textarea></div><div class="row g-3"><div class="col-md-6"><label class="form-label">Date</label><input class="form-control" type="date" name="followup_date" required></div><div class="col-md-6"><label class="form-label">Time</label><input class="form-control" type="time" name="followup_time" required></div></div>',
        visit: '<div class="row g-3"><div class="col-12"><label class="form-label">Address</label><textarea class="form-control" name="visit_address" required></textarea></div><div class="col-md-6"><label class="form-label">Country</label><input class="form-control" name="visit_country" required></div><div class="col-md-6"><label class="form-label">State</label><input class="form-control" name="visit_state" required></div><div class="col-md-6"><label class="form-label">City</label><input class="form-control" name="visit_city" required></div><div class="col-md-6"><label class="form-label">Zip code</label><input class="form-control" name="visit_zip" required></div><div class="col-12"><label class="form-label">Visit motive</label><textarea class="form-control" name="visit_motive" required></textarea></div><div class="col-12"><label class="form-label">Scheduled for</label><input class="form-control" type="datetime-local" name="visit_scheduled_at" required></div><div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="mark_as_lead_address" value="1" id="mark-as-lead-address"><label class="form-check-label" for="mark-as-lead-address">Save this as the lead address</label></div></div></div>',
        gmeet: '<div class="mb-3"><label class="form-label">Meeting link</label><input class="form-control" type="url" name="meeting_link" required></div><div class="mb-3"><label class="form-label">Scheduled for</label><input class="form-control" type="datetime-local" name="meeting_scheduled_at" required></div><div><label class="form-label">Meeting motive</label><textarea class="form-control" name="meeting_motive" required></textarea></div>',
        email: '<div class="mb-3"><label class="form-label">Subject</label><input class="form-control" name="email_subject" required></div><div class="mb-3"><label class="form-label">Message</label><textarea class="form-control" name="email_body" rows="6" required></textarea></div><div><label class="form-label">Attachment</label><input class="form-control" type="file" name="attachment"></div>',
    };
    const setActivityType = (type) => { activityForm.querySelector('[name="activity_type"]').value = type; activityForm.querySelector('[data-activity-fields]').innerHTML = activityFields[type]; document.querySelectorAll('[data-activity-type]').forEach((button) => button.classList.toggle('active', button.dataset.activityType === type)); };
    const routeFor = (template, leadId) => template.replace('__lead__', leadId);
    document.addEventListener('dragstart', (event) => { const card = event.target.closest('.kanban-lead'); if (!card) return; card.classList.add('opacity-50'); card.dataset.origin = card.parentElement.dataset.status; });
    document.addEventListener('dragend', (event) => event.target.closest('.kanban-lead')?.classList.remove('opacity-50'));
    document.querySelectorAll('.kanban-dropzone').forEach((zone) => { zone.addEventListener('scroll', () => { if (zone.scrollTop + zone.clientHeight >= zone.scrollHeight - 80) loadColumn(zone); zone.closest('.kanban-column').dataset.total = zone.closest('.kanban-column').dataset.total ?? 0; }); zone.addEventListener('dragover', (event) => event.preventDefault()); zone.addEventListener('drop', async (event) => { event.preventDefault(); const card = document.querySelector('.kanban-lead.opacity-50'); if (!card || card.dataset.origin === zone.dataset.status) return; const origin = card.parentElement; const response = await fetch(routeFor(statusUrl, card.dataset.leadId), { method: 'PATCH', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': token }, body: JSON.stringify({ status: zone.dataset.status }) }); if (response.ok) { zone.append(card); showAlert('Lead status updated.'); return; } origin.append(card); showAlert('Unable to update lead status.', 'danger'); }); });
    document.querySelectorAll('[data-kanban-filter-form]').forEach((form) => form.addEventListener('submit', (event) => { event.preventDefault(); document.querySelectorAll('[data-kanban-filter-form]').forEach((otherForm) => { Object.entries(Object.fromEntries(new FormData(form).entries())).forEach(([key, value]) => { const input = otherForm.elements.namedItem(key); if (input) input.value = value; }); }); loadBoard(); }));
    document.addEventListener('click', async (event) => { const detailButton = event.target.closest('[data-lead-view]'); if (detailButton) { const body = document.querySelector('[data-detail-body]'); document.querySelector('[data-detail-name]').textContent = 'Lead details'; body.innerHTML = '<div class="text-body-secondary">Loading details…</div>'; detailModal.show(); const response = await fetch(routeFor(detailsUrl, detailButton.dataset.leadView), { headers: { Accept: 'application/json' } }); if (!response.ok) { body.textContent = 'Unable to load lead details.'; return; } const data = await response.json(); document.querySelector('[data-detail-name]').textContent = data.name; body.innerHTML = data.html + `<div class="mt-3"><a class="btn btn-sm btn-outline-primary" href="${routeFor(@json(route('sales-lead-view', '__lead__')), detailButton.dataset.leadView)}">Open full details</a></div>`; return; } const assignButton = event.target.closest('[data-assign-lead]'); if (assignButton) { const response = await fetch(routeFor(assigneeUrl, assignButton.dataset.assignLead), { method: 'PATCH', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': token }, body: JSON.stringify({ assigned_to: assignButton.dataset.assignedTo || null }) }); if (response.ok) { const button = document.querySelector(`[data-assignee-button="${assignButton.dataset.assignLead}"]`); button.querySelector('.text-truncate').textContent = (await response.json()).assignee || 'Unassigned'; showAlert('Contact person updated.'); } return; } const activityButton = event.target.closest('[data-lead-activity]'); if (activityButton) { activityForm.action = routeFor(activityUrl, activityButton.dataset.leadActivity); activityForm.reset(); setActivityType('notes'); detailModal.hide(); activityModal.show(); return; } const typeButton = event.target.closest('[data-activity-type]'); if (typeButton) setActivityType(typeButton.dataset.activityType); });
    document.querySelector('[data-create-lead-form]').addEventListener('submit', async (event) => { event.preventDefault(); const response = await fetch(event.target.action, { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token }, body: new FormData(event.target) }); if (!response.ok) { const data = await response.json(); window.unlockFormSubmit(event.target); showAlert(Object.values(data.errors ?? {}).flat()[0] ?? 'Unable to create lead.', 'danger'); return; } const data = await response.json(); const column = document.querySelector(`.kanban-dropzone[data-status="${CSS.escape(data.status)}"]`); if (!column) { window.location.reload(); return; } column.insertAdjacentHTML('afterbegin', data.html); refreshCounts(); event.target.reset(); window.unlockFormSubmit(event.target); createModal.hide(); showAlert(data.message); });
    activityForm.addEventListener('submit', async (event) => { event.preventDefault(); const response = await fetch(event.target.action, { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token }, body: new FormData(event.target) }); if (!response.ok) { const data = await response.json(); window.unlockFormSubmit(event.target); showAlert(Object.values(data.errors ?? {}).flat()[0] ?? 'Please complete the activity fields.', 'danger'); return; } activityModal.hide(); window.unlockFormSubmit(event.target); showAlert((await response.json()).message); });
    setActivityType('notes');
    loadBoard();
});
</script>
@endsection
