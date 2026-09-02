@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css">
<style>
  .fc .fc-button-primary {
    background-color: #696cff;
    border-color: #696cff;
  }
  .fc .fc-button-primary:hover,
  .fc .fc-button-primary:not(:disabled):active,
  .fc .fc-button-primary:not(:disabled).fc-button-active {
    background-color: #5a5ccf;
    border-color: #5a5ccf;
  }
  .fc-event {
    border-radius: 4px !important;
    font-size: .78rem !important;
    cursor: pointer;
  }
  .fc .fc-day-today {
    background-color: rgba(105, 108, 255, .06) !important;
  }
  .upcoming-event-item:hover {
    background: rgba(105,108,255,.04);
    border-radius: 8px;
    transition: background .15s ease;
  }
  .letter-spacing-1 { letter-spacing: .05em; }
  .cursor-pointer { cursor: pointer; }
  .fc-toolbar-title { font-size: 1rem !important; font-weight: 600 !important; }
  .calendar-stat { border-left: 3px solid var(--calendar-stat-color); }
  .calendar-stat-value { font-size: 1.35rem; line-height: 1; }
  #calendar-error { display: none; }
  @media (max-width: 767.98px) {
    .app-calendar-sidebar { width: 100% !important; min-height: auto !important; }
    .app-calendar-content .card-body { padding: 1rem !important; }
    .fc .fc-toolbar { flex-wrap: wrap; gap: .65rem; }
    .fc .fc-toolbar-chunk { display: flex; align-items: center; }
  }
</style>
@endpush


@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

  {{-- Page header --}}
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h4 class="mb-1 fw-semibold">Sales Calendar</h4>
      <p class="text-muted mb-0 small">Upcoming follow-ups, site visits &amp; meetings</p>
    </div>
  </div>

  <div class="row g-3 mb-4" aria-label="Calendar activity summary">
    <div class="col-12 col-sm-4">
      <div class="card border-0 shadow-sm h-100 calendar-stat" style="--calendar-stat-color:#696cff;">
        <div class="card-body py-3 d-flex align-items-center justify-content-between">
          <div><div class="text-muted small">Follow-ups</div><div class="calendar-stat-value fw-semibold" id="stat-followup">0</div></div>
          <i class="bx bx-phone-call fs-2" style="color:#696cff;" aria-hidden="true"></i>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-4">
      <div class="card border-0 shadow-sm h-100 calendar-stat" style="--calendar-stat-color:#fd7e14;">
        <div class="card-body py-3 d-flex align-items-center justify-content-between">
          <div><div class="text-muted small">Site visits</div><div class="calendar-stat-value fw-semibold" id="stat-visit">0</div></div>
          <i class="bx bx-map-pin fs-2" style="color:#fd7e14;" aria-hidden="true"></i>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-4">
      <div class="card border-0 shadow-sm h-100 calendar-stat" style="--calendar-stat-color:#28a745;">
        <div class="card-body py-3 d-flex align-items-center justify-content-between">
          <div><div class="text-muted small">Meetings</div><div class="calendar-stat-value fw-semibold" id="stat-gmeet">0</div></div>
          <i class="bx bx-video fs-2" style="color:#28a745;" aria-hidden="true"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="card app-calendar-wrapper shadow-sm border-0">
    <div class="row g-0">

      {{-- ===== SIDEBAR ===== --}}
      <div class="col-auto app-calendar-sidebar border-end d-flex flex-column" id="app-calendar-sidebar" style="width:280px; min-height:600px;">

        {{-- Filter section --}}
        <div class="p-4 border-bottom">
          <h6 class="text-uppercase fw-bold text-muted small mb-3 letter-spacing-1">Event Filters</h6>

          <div class="d-flex flex-column gap-2">
            <label class="d-flex align-items-center gap-2 cursor-pointer user-select-none" for="filter-all">
              <input class="form-check-input calendar-filter-all mt-0" type="checkbox" id="filter-all" checked>
              <span class="fw-medium">View All</span>
            </label>

            <label class="d-flex align-items-center gap-2 cursor-pointer user-select-none" for="filter-followup">
              <input class="form-check-input calendar-filter-type mt-0" type="checkbox" id="filter-followup"
                data-type="followup" checked style="accent-color:#696cff;">
              <span class="badge rounded-pill px-2 py-1" style="background:rgba(105,108,255,.12); color:#696cff;">
                <i class="bx bx-phone-call me-1"></i>Follow-up
              </span>
            </label>

            <label class="d-flex align-items-center gap-2 cursor-pointer user-select-none" for="filter-visit">
              <input class="form-check-input calendar-filter-type mt-0" type="checkbox" id="filter-visit"
                data-type="visit" checked style="accent-color:#fd7e14;">
              <span class="badge rounded-pill px-2 py-1" style="background:rgba(253,126,20,.12); color:#fd7e14;">
                <i class="bx bx-map-pin me-1"></i>Visit
              </span>
            </label>

            <label class="d-flex align-items-center gap-2 cursor-pointer user-select-none" for="filter-gmeet">
              <input class="form-check-input calendar-filter-type mt-0" type="checkbox" id="filter-gmeet"
                data-type="gmeet" checked style="accent-color:#28a745;">
              <span class="badge rounded-pill px-2 py-1" style="background:rgba(40,167,69,.12); color:#28a745;">
                <i class="bx bx-video me-1"></i>Meeting
              </span>
            </label>
          </div>
        </div>

        {{-- Upcoming events section --}}
        <div class="p-4 flex-grow-1 overflow-auto">
          <h6 class="text-uppercase fw-bold text-muted small mb-3 letter-spacing-1">Upcoming</h6>
          <div id="upcoming-events-list">
            <div class="text-center text-muted py-4">
              <i class="bx bx-calendar-check fs-2 opacity-50"></i>
              <p class="small mt-2 mb-0">Loading…</p>
            </div>
          </div>
          <div id="calendar-error" class="alert alert-danger small mb-0" role="alert">
            We could not load scheduled activities. Please refresh the page.
          </div>
        </div>

      </div>
      {{-- ===== /SIDEBAR ===== --}}

      {{-- ===== MAIN CALENDAR ===== --}}
      <div class="col app-calendar-content">
        <div class="card shadow-none border-0 h-100">
          <div class="card-body p-4">
            <div id="calendar"></div>
          </div>
        </div>
      </div>
      {{-- ===== /MAIN CALENDAR ===== --}}

    </div>
  </div>

</div>

{{-- ===== EVENT DETAIL MODAL ===== --}}
<div class="modal fade" id="eventDetailModal" tabindex="-1" aria-labelledby="eventDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header border-0 pb-0" id="eventDetailHeader">
        <div>
          <span class="badge rounded-pill mb-2 fs-6" id="modal-type-badge"></span>
          <h5 class="modal-title fw-bold mb-0" id="eventDetailModalLabel" style="font-size:1.1rem;"></h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-3">
        <div class="row g-3">
          <div class="col-6">
            <div class="small text-muted fw-semibold mb-1">LEAD</div>
            <div class="fw-medium" id="modal-lead-name">—</div>
          </div>
          <div class="col-6">
            <div class="small text-muted fw-semibold mb-1">STATUS</div>
            <span class="badge" id="modal-status-badge">—</span>
          </div>
          <div class="col-12">
            <div class="small text-muted fw-semibold mb-1">SCHEDULED AT</div>
            <div class="fw-medium" id="modal-scheduled-at">—</div>
          </div>
          <div class="col-12" id="modal-subject-row">
            <div class="small text-muted fw-semibold mb-1">SUBJECT</div>
            <div id="modal-subject">—</div>
          </div>
          <div class="col-12" id="modal-summary-row" style="display:none;">
            <div class="small text-muted fw-semibold mb-1">NOTES</div>
            <div class="text-muted small" id="modal-summary">—</div>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <a href="#" class="btn btn-primary btn-sm" id="modal-lead-link" target="_blank">
          <i class="bx bx-link-external me-1"></i>View Lead
        </a>
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
{{-- ===== /EVENT DETAIL MODAL ===== --}}

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js"></script>
<script>
(function () {
  'use strict';

  // ------------------------------------------------------------------
  // Helpers
  // ------------------------------------------------------------------
  const typeColors = {
    followup: { bg: '#696cff', text: '#fff', label: 'Follow-up', icon: 'bx-phone-call' },
    visit:    { bg: '#fd7e14', text: '#fff', label: 'Visit',     icon: 'bx-map-pin' },
    gmeet:    { bg: '#28a745', text: '#fff', label: 'Meeting',   icon: 'bx-video' },
  };

  const statusBadge = {
    pending:   'bg-label-warning',
    completed: 'bg-label-success',
    cancelled: 'bg-label-secondary',
    missed:    'bg-label-danger',
  };

  function formatDt(isoStr) {
    if (!isoStr) return '—';
    const d = new Date(isoStr);
    return d.toLocaleDateString('en-IN', {
      day: '2-digit', month: 'short', year: 'numeric',
      hour: '2-digit', minute: '2-digit', hour12: true,
    });
  }

  // ------------------------------------------------------------------
  // Track which types are visible
  // ------------------------------------------------------------------
  const visibleTypes = new Set(['followup', 'visit', 'gmeet']);
  let calendarInstance = null;

  // ------------------------------------------------------------------
  // Upcoming events list
  // ------------------------------------------------------------------
  function updateStats(events) {
    ['followup', 'visit', 'gmeet'].forEach(type => {
      const count = events.filter(event => event.extendedProps.activityType === type).length;
      document.getElementById(`stat-${type}`).textContent = count;
    });
  }

  function showCalendarError(show) {
    document.getElementById('calendar-error').style.display = show ? '' : 'none';
  }

  function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>'"]/g, character => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      "'": '&#39;',
      '"': '&quot;',
    })[character]);
  }

  async function loadUpcoming() {
    const now = new Date().toISOString();
    const future = new Date(Date.now() + 60 * 24 * 60 * 60 * 1000).toISOString(); // next 60 days
    try {
      const res = await fetch(`{{ route('calendar.events') }}?start=${now}&end=${future}`);
      if (!res.ok) throw new Error(`Calendar request failed with ${res.status}`);
      const events = await res.json();
      updateStats(events);
      renderUpcoming(events.filter(e => visibleTypes.has(e.extendedProps.activityType)).slice(0, 6));
      showCalendarError(false);
    } catch (e) {
      console.error('Upcoming load failed', e);
      showCalendarError(true);
    }
  }

  function renderUpcoming(events) {
    const list = document.getElementById('upcoming-events-list');
    if (!events.length) {
      list.innerHTML = `<div class="text-center text-muted py-4">
        <i class="bx bx-calendar-x fs-2 opacity-50"></i>
        <p class="small mt-2 mb-0">No upcoming events</p>
      </div>`;
      return;
    }

    list.innerHTML = events.map(ev => {
      const p = ev.extendedProps;
      const c = typeColors[p.activityType] || { bg: '#8592a3', text: '#fff', label: 'Activity', icon: 'bx-calendar' };
      const date = new Date(p.scheduledAt);
      const dayNum = date.toLocaleDateString('en-IN', { day: '2-digit' });
      const mon = date.toLocaleDateString('en-IN', { month: 'short' });
      const time = date.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', hour12: true });
      return `
        <div class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom upcoming-event-item"
             data-event-id="${ev.id}" style="cursor:pointer;" title="Click to view">
          <div class="rounded-3 d-flex flex-column align-items-center justify-content-center flex-shrink-0"
               style="width:40px;height:44px;background:${c.bg}20; border-left:3px solid ${c.bg};">
            <span class="fw-bold lh-1" style="font-size:.8rem; color:${c.bg};">${dayNum}</span>
            <span class="text-uppercase lh-1" style="font-size:.6rem; color:${c.bg};">${mon}</span>
          </div>
          <div class="overflow-hidden">
            <div class="fw-medium text-truncate small">${escapeHtml(p.leadName)}</div>
            <div class="text-muted" style="font-size:.7rem;">
              <i class="bx ${c.icon} me-1"></i>${escapeHtml(c.label)} · ${escapeHtml(time)}
            </div>
          </div>
        </div>`;
    }).join('');

    // Clicking upcoming item → jump calendar & open modal
    list.querySelectorAll('.upcoming-event-item').forEach(item => {
      item.addEventListener('click', () => {
        const id = parseInt(item.dataset.eventId);
        if (calendarInstance) {
          const ev = calendarInstance.getEventById(id);
          if (ev) {
            calendarInstance.gotoDate(ev.start);
            openModal(ev);
          }
        }
      });
    });
  }

  // ------------------------------------------------------------------
  // Event detail modal
  // ------------------------------------------------------------------
  let bsModal = null;

  function openModal(event) {
    const p = event.extendedProps;
    const c = typeColors[p.activityType] || { bg: '#8592a3', text: '#fff', label: ucfirst(p.activityType), icon: 'bx-calendar' };

    // Header
    document.getElementById('eventDetailHeader').style.borderBottom = `3px solid ${c.bg}`;
    const typeBadge = document.getElementById('modal-type-badge');
    typeBadge.style.background = c.bg + '20';
    typeBadge.style.color = c.bg;
    typeBadge.innerHTML = `<i class="bx ${c.icon} me-1"></i>${c.label}`;

    document.getElementById('eventDetailModalLabel').textContent = p.subject || event.title;

    // Fields
    document.getElementById('modal-lead-name').textContent = p.leadName;
    document.getElementById('modal-scheduled-at').textContent = formatDt(p.scheduledAt);

    const statusEl = document.getElementById('modal-status-badge');
    statusEl.className = 'badge ' + (statusBadge[p.status] || 'bg-label-secondary');
    statusEl.textContent = ucfirst(p.status || '—');

    const subjectEl = document.getElementById('modal-subject');
    subjectEl.textContent = p.subject || '—';

    const summaryRow = document.getElementById('modal-summary-row');
    if (p.summary) {
      summaryRow.style.display = '';
      document.getElementById('modal-summary').textContent = p.summary;
    } else {
      summaryRow.style.display = 'none';
    }

    // Lead link
    const leadLink = document.getElementById('modal-lead-link');
    leadLink.href = p.leadId
      ? `{{ url('sales/lead-view') }}/${p.leadId}`
      : '#';

    if (!bsModal) {
      bsModal = new bootstrap.Modal(document.getElementById('eventDetailModal'));
    }
    bsModal.show();
  }

  function ucfirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
  }

  // ------------------------------------------------------------------
  // FullCalendar initialisation
  // ------------------------------------------------------------------
  document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');

    calendarInstance = new FullCalendar.Calendar(calendarEl, {
      plugins: ['dayGrid', 'timeGrid', 'list', 'interaction'],
      initialView: 'dayGridMonth',
      headerToolbar: {
        left:   'prev,next today',
        center: 'title',
        right:  'dayGridMonth,timeGridWeek,timeGridDay,listMonth',
      },
      height: 'auto',
      events: {
        url: '{{ route("calendar.events") }}',
        method: 'GET',
        failure: function () {
          console.error('Failed to load calendar events.');
          showCalendarError(true);
        },
      },

      // Filter on client after fetch
      eventDisplay: 'block',

      eventDidMount: function (info) {
        const type = info.event.extendedProps.activityType;
        if (!visibleTypes.has(type)) {
          info.el.style.display = 'none';
        }
      },

      eventClick: function (info) {
        openModal(info.event);
      },

      eventMouseEnter: function (info) {
        info.el.style.transform = 'scale(1.02)';
        info.el.style.transition = 'transform .15s ease';
        info.el.style.zIndex = '9';
      },

      eventMouseLeave: function (info) {
        info.el.style.transform = '';
      },

      noEventsContent: 'No scheduled activities for this period.',

      loading: function (isLoading) {
        if (isLoading) showCalendarError(false);
      },
    });

    calendarInstance.render();

    // ------------------------------------------------------------------
    // Filter checkboxes
    // ------------------------------------------------------------------
    const filterAll  = document.getElementById('filter-all');
    const typeChecks = document.querySelectorAll('.calendar-filter-type');

    function applyFilters() {
      // Rebuild visibleTypes
      visibleTypes.clear();
      typeChecks.forEach(cb => {
        if (cb.checked) visibleTypes.add(cb.dataset.type);
      });

      // Show/hide rendered events
      calendarInstance.getEvents().forEach(ev => {
        const type = ev.extendedProps.activityType;
        const els  = calendarInstance.el.querySelectorAll(`[data-event-id="${ev.id}"]`);
        // FullCalendar doesn't expose per-element hide easily, use classlist trick
        ev.setProp('display', visibleTypes.has(type) ? 'block' : 'none');
      });

      // Sync "View All" state
      const allChecked = [...typeChecks].every(cb => cb.checked);
      const noneChecked = [...typeChecks].every(cb => !cb.checked);
      filterAll.indeterminate = !allChecked && !noneChecked;
      filterAll.checked = allChecked;

      loadUpcoming();
    }

    filterAll.addEventListener('change', function () {
      typeChecks.forEach(cb => { cb.checked = this.checked; });
      applyFilters();
    });

    typeChecks.forEach(cb => {
      cb.addEventListener('change', applyFilters);
    });

    // Initial upcoming load
    loadUpcoming();
  });
})();
</script>
@endpush
@endsection