@extends('layouts.app')
@section('content')
<style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #ffffff;
            font-family: Arial, Helvetica, sans-serif;
            color: #3d4b5c;
        }

        /* =========================
           MAIN CARD
        ========================= */

        .calendar-card {
            width: 100%;
            height: 835px;
            border: 0;
            border-radius: 0;
            overflow: hidden;
            box-shadow: none;
        }

        .calendar-wrapper {
            height: 100%;
            display: flex;
        }

        /* =========================
           SIDEBAR
        ========================= */

        .calendar-sidebar {
            width: 371px;
            min-width: 371px;
            height: 100%;
            border-right: 1px solid #e1e4e8;
            background: #fff;
        }

        .add-event-wrapper {
            height: 106px;
            padding: 28px 29px;
            border-bottom: 1px solid #e1e4e8;
        }

        .btn-add-event {
            width: 100%;
            height: 48px;
            border: 0;
            border-radius: 8px;

            background: linear-gradient(
                90deg,
                #6663ff 0%,
                #7674ff 100%
            );

            color: #fff;
            font-size: 18px;
            font-weight: 500;

            box-shadow: 0 5px 13px rgba(93, 91, 255, 0.28);
        }

        .btn-add-event:hover {
            color: #fff;
        }

        .btn-add-event i {
            font-size: 18px;
            margin-right: 10px;
        }

        /* =========================
           MINI CALENDAR
        ========================= */

        .mini-calendar-wrapper {
            height: 430px;
            padding: 27px 27px 20px;
            border-bottom: 1px solid #e1e4e8;
        }

        .mini-calendar-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
        }

        .mini-nav-btn {
            width: 37px;
            height: 38px;
            border: 0;
            border-radius: 7px;
            background: #f0f1f3;

            display: flex;
            align-items: center;
            justify-content: center;

            color: #6c7784;
            font-size: 20px;
        }

        .mini-month-name {
            font-size: 18px;
            font-weight: 400;
            color: #455364;
        }

        .mini-calendar {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
            table-layout: fixed;
        }

        .mini-calendar th {
            height: 27px;
            text-align: center;
            font-size: 14px;
            font-weight: 400;
            color: #445162;
        }

        .mini-calendar td {
            height: 31px;
            padding: 0;
            text-align: center;
            font-size: 16px;
            color: #3f4d5c;
        }

        .mini-calendar td.muted {
            color: #aeb4bc;
        }

        .mini-calendar td.selected span {
            width: 45px;
            height: 45px;
            margin: -7px auto 0;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 8px;
            background: #e9e8ff;
            color: #6563ff;
        }

        /* =========================
           FILTERS
        ========================= */

        .event-filters {
            padding: 34px 27px;
        }

        .event-filters h4 {
            margin: 0 0 23px;

            font-size: 22px;
            font-weight: 500;
            color: #3e4d5c;
        }

        .filter-item {
            display: flex;
            align-items: center;
            gap: 11px;

            margin-bottom: 19px;

            font-size: 18px;
            color: #465466;
        }

        .filter-check {
            width: 23px;
            height: 23px;

            border-radius: 5px;

            display: flex;
            align-items: center;
            justify-content: center;

            color: #fff;
            font-size: 14px;
            font-weight: bold;
        }

        .filter-all {
            background: #8b98a8;
        }

        .filter-personal {
            background: #ff4329;
        }

        .filter-business {
            background: #6563ff;
        }

        .filter-family {
            background: #ffa800;
        }

        /* =========================
           MAIN CALENDAR
        ========================= */

        .calendar-main {
            flex: 1;
            min-width: 0;
            height: 100%;
            background: #fff;
        }

        /* =========================
           CALENDAR HEADER
        ========================= */

        .calendar-header {
            height: 105px;
            border-bottom: 1px solid #e1e4e8;

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 0 31px 0 43px;
        }

        .calendar-header-left {
            display: flex;
            align-items: center;
            gap: 27px;
        }

        .calendar-arrow {
            border: 0;
            background: transparent;

            width: 27px;
            height: 35px;

            display: flex;
            align-items: center;
            justify-content: center;

            color: #65717e;
            font-size: 27px;
            padding: 0;
        }

        .calendar-month-title {
            margin: 0;

            font-size: 32px;
            line-height: 1;
            font-weight: 400;

            color: #64707c;
        }

        /* =========================
           VIEW BUTTONS
        ========================= */

        .calendar-view-buttons {
            display: flex;
            border-radius: 8px;
            overflow: hidden;
        }

        .calendar-view-buttons .btn {
            height: 47px;
            min-width: 81px;

            border: 0;
            border-right: 1px solid #d8d8fa;
            border-radius: 0;

            background: #e9e9ff;
            color: #7776ff;

            font-size: 18px;
            font-weight: 400;
        }

        .calendar-view-buttons .btn:last-child {
            border-right: 0;
        }

        .calendar-view-buttons .btn.active {
            background: linear-gradient(
                90deg,
                #6866ff,
                #716fff
            );
            color: #fff;
        }

        /* =========================
           CALENDAR GRID
        ========================= */

        .calendar-grid {
            height: calc(100% - 105px);
            display: grid;

            grid-template-columns: repeat(7, 1fr);
            grid-template-rows: 48px repeat(6, 1fr);

            border-left: 0;
        }

        /* Week header */

        .weekday {
            height: 48px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-right: 1px solid #e0e3e7;
            border-bottom: 1px solid #e0e3e7;

            font-size: 18px;
            font-weight: 400;

            color: #344251;
        }

        .weekday:last-child {
            border-right: 0;
        }

        /* Calendar day */

        .calendar-day {
            position: relative;

            min-width: 0;
            min-height: 0;

            border-right: 1px solid #e0e3e7;
            border-bottom: 1px solid #e0e3e7;

            background: #fff;
            overflow: hidden;
        }

        .calendar-day:nth-child(7n) {
            border-right: 0;
        }

        .day-number {
            position: absolute;

            top: 11px;
            left: 10px;

            font-size: 18px;
            font-weight: 400;

            color: #66727f;
        }

        .day-number.muted {
            color: #aeb4bb;
        }

        .day-summary {
            position: absolute;
            top: 8px;
            left: 10px;
            right: 8px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 1px;
            pointer-events: none;
        }

        .day-summary-number {
            color: #66727f;
            font-size: 18px;
            line-height: 20px;
        }

        .day-summary-item {
            color: #696cff;
            font-size: 11px;
            line-height: 14px;
            white-space: nowrap;
        }

        .calendar-record {
            padding: 9px 0;
            border-bottom: 1px solid #e1e4e8;
        }

        .calendar-record:last-child {
            border-bottom: 0;
        }

        .calendar-record-time {
            min-width: 72px;
            color: #696cff;
            font-size: 12px;
            font-weight: 600;
        }

        .calendar-record-summary {
            display: -webkit-box;
            overflow: hidden;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
            line-clamp: 2;
        }

        .today-cell {
            background: #f1f2f3;
        }

        [data-calendar-grid-days] {
            display: contents;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 1000px) {

            .calendar-sidebar {
                width: 300px;
                min-width: 300px;
            }

            .calendar-month-title {
                font-size: 26px;
            }

            .calendar-header {
                padding-left: 25px;
                padding-right: 20px;
            }

            .calendar-view-buttons .btn {
                min-width: 65px;
                font-size: 15px;
            }

            .weekday,
            .day-number {
                font-size: 14px;
            }

            .event {
                font-size: 13px;
            }
        }

        @media (max-width: 768px) {

            .calendar-wrapper {
                display: block;
            }

            .calendar-sidebar {
                width: 100%;
                height: auto;
            }

            .mini-calendar-wrapper {
                height: auto;
            }

            .calendar-main {
                height: 700px;
            }

            .calendar-header {
                height: auto;
                min-height: 90px;
                flex-wrap: wrap;
                gap: 15px;
                padding: 20px;
            }

            .calendar-grid {
                overflow-x: auto;
                min-width: 850px;
            }
        }
    </style>
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card">
    <div class="row">
      <div class="col-sm-3">
        <!-- Mini Calendar -->
        <div class="border mini-calendar-wrapper">
          <div class="mini-calendar-title">
            <button class="mini-nav-btn" data-calendar-previous aria-label="Previous month">
              <i class="bi bi-chevron-left"></i>
            </button>
            <span class="mini-month-name" data-calendar-mini-title></span>
            <button class="mini-nav-btn" data-calendar-next aria-label="Next month">
              <i class="bi bi-chevron-right"></i>
            </button>
          </div>
          <table id="mini-calendar" class="mini-calendar">
            <thead>
              <tr>
                <th>Sun</th>
                <th>Mon</th>
                <th>Tue</th>
                <th>Wed</th>
                <th>Thu</th>
                <th>Fri</th>
                <th>Sat</th>
              </tr>
            </thead>
            <tbody data-calendar-mini-grid></tbody>
          </table>
        </div>
        <!-- Event Filters -->
        <div class="event-filters">

            <h4>Event Filters</h4>

            <div class="filter-item" data-type="all">
                <span class="filter-check filter-all">
                    <i class="bi bi-check-lg"></i>
                </span>
                <span>View All</span>
            </div>

            <div class="filter-item" data-type="followup">
                <span class="filter-check filter-personal">
                    <i class="bi bi-check-lg"></i>
                </span>
                <span>Personal</span>
            </div>

            <div class="filter-item" data-type="visit">
                <span class="filter-check filter-business">
                    <i class="bi bi-check-lg"></i>
                </span>
                <span>Business</span>
            </div>

            <div class="filter-item" data-type="gmeet">
                <span class="filter-check filter-family">
                    <i class="bi bi-check-lg"></i>
                </span>
                <span>Family</span>
            </div>

        </div>
      </div>
      <div class="col-sm-9">
        <div class="calendar-header d-none">
          <div class="calendar-header-left">
            <button class="calendar-arrow" data-calendar-previous aria-label="Previous month">
              <i class="bi bi-chevron-left"></i>
            </button>
            <button class="calendar-arrow" data-calendar-next aria-label="Next month">
              <i class="bi bi-chevron-right"></i>
            </button>
            <h1 class="calendar-month-title" data-calendar-title></h1>
          </div>
        </div>
        <div class="calendar-grid border">
          <div class="weekday">Sun</div>
          <div class="weekday">Mon</div>
          <div class="weekday">Tue</div>
          <div class="weekday">Wed</div>
          <div class="weekday">Thu</div>
          <div class="weekday">Fri</div>
          <div class="weekday">Sat</div>
          <div data-calendar-grid-days></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="calendarDayModal" tabindex="-1" aria-labelledby="calendarDayModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="calendarDayModalLabel">Scheduled leads</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="calendar-day-details"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="calendarActivityModal" tabindex="-1" aria-labelledby="calendarActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="calendarActivityModalLabel">Update scheduled activity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="calendar-activity-form" method="POST">
                <div class="modal-body" id="calendar-activity-form-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary" id="calendar-activity-submit">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const calendarGrid = document.querySelector('[data-calendar-grid-days]');
        const miniGrid = document.querySelector('[data-calendar-mini-grid]');
        const calendarTitle = document.querySelector('[data-calendar-title]');
        const miniTitle = document.querySelector('[data-calendar-mini-title]');
        const monthFormatter = new Intl.DateTimeFormat(undefined, { month: 'long', year: 'numeric' });
        const timeFormatter = new Intl.DateTimeFormat(undefined, { hour: 'numeric', minute: '2-digit' });
        const today = new Date();
        const state = {
            month: new Date(today.getFullYear(), today.getMonth(), 1),
            events: [],
            filters: new Set(['followup', 'visit', 'gmeet']),
        };

        const dateKey = (date) => [
            date.getFullYear(),
            String(date.getMonth() + 1).padStart(2, '0'),
            String(date.getDate()).padStart(2, '0'),
        ].join('-');

        const escapeHtml = (value) => String(value ?? '-').replace(/[&<>'"]/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' })[character]);

        const visibleEventsForDate = (date) => state.events
            .filter((event) => event.start.slice(0, 10) === dateKey(date))
            .filter((event) => state.filters.has(event.extendedProps.activityType));

        const openDayModal = (date, dayEvents) => {
            const details = document.getElementById('calendar-day-details');
            document.getElementById('calendarDayModalLabel').textContent = `${new Intl.DateTimeFormat(undefined, { dateStyle: 'full' }).format(date)} (${dayEvents.length})`;
            details.innerHTML = dayEvents.length === 0
                ? '<p class="mb-0 text-body-secondary">No scheduled records for this date.</p>'
                : dayEvents.sort((first, second) => new Date(first.start) - new Date(second.start)).map((event) => {
                    const props = event.extendedProps;
                    const typeLabel = props.activityType === 'gmeet' ? 'Meet' : props.activityType === 'followup' ? 'Followup' : 'Visit';
                    const actionButtons = props.status === 'pending' ? `<div class="d-flex gap-1">
                        <button type="button" class="btn btn-xs btn-outline-primary" title="Edit activity" data-calendar-edit="${event.id}"><i class="bx bx-edit"></i></button>
                        <button type="button" class="btn btn-xs btn-outline-success" title="Complete activity" data-calendar-complete="${event.id}"><i class="bx bx-check"></i></button>
                    </div>` : `<span class="badge bg-label-success">${escapeHtml(props.status || 'Completed')}</span>`;
                    return `<div class="calendar-record">
                        <div class="d-flex align-items-start gap-2">
                            <span class="calendar-record-time">${escapeHtml(timeFormatter.format(new Date(event.start)))}</span>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div><strong>${escapeHtml(props.leadName)}</strong><span class="badge bg-label-primary ms-2">${typeLabel}</span></div>
                                    ${actionButtons}
                                </div>
                                <div class="small fw-semibold mt-1">${escapeHtml(props.subject || `${typeLabel} scheduled`)}</div>
                                <div class="calendar-record-summary small text-body-secondary">${escapeHtml(props.summary || 'No activity details added.')}</div>
                                <div class="d-flex flex-wrap gap-3 small text-body-secondary mt-1"><span>${escapeHtml(props.leadMobile || props.leadEmail || 'No contact')}</span><span>${escapeHtml(props.leadStatus || '-')} · ${escapeHtml(props.leadStage || '-')}</span></div>
                                <div class="d-flex gap-2 mt-2"><a href="${escapeHtml(`{{ url('/sales/lead-view') }}/${props.leadId}`)}" class="btn btn-xs btn-primary">Open lead</a></div>
                            </div>
                        </div>
                    </div>`;
                }).join('');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('calendarDayModal')).show();
        };

        const activityUrl = (event, action) => `{{ url('/sales/leads') }}/${event.extendedProps.leadId}/activities/${event.id}/${action}`;

        const activityFormFields = (event) => {
            const props = event.extendedProps;
            const scheduled = new Date(event.start);
            const date = `${scheduled.getFullYear()}-${String(scheduled.getMonth() + 1).padStart(2, '0')}-${String(scheduled.getDate()).padStart(2, '0')}`;
            const time = `${String(scheduled.getHours()).padStart(2, '0')}:${String(scheduled.getMinutes()).padStart(2, '0')}`;
            const common = `<input type="hidden" name="activity_type" value="${escapeHtml(props.activityType)}"><input type="hidden" name="_method" value="PUT">`;
            if (props.activityType === 'followup') return `${common}<label class="form-label">Followup note</label><textarea class="form-control mb-3" name="summary" rows="4" required>${escapeHtml(props.summary || '')}</textarea><div class="row g-2"><div class="col-6"><label class="form-label">Date</label><input type="date" class="form-control" name="followup_date" value="${date}" required></div><div class="col-6"><label class="form-label">Time</label><input type="time" class="form-control" name="followup_time" value="${time}" required></div></div>`;
            if (props.activityType === 'visit') return `${common}<label class="form-label">Address</label><textarea class="form-control mb-2" name="visit_address" required>${escapeHtml(props.leadAddress || '')}</textarea><input class="form-control mb-2" name="visit_country" placeholder="Country" value="${escapeHtml(props.leadCountry || '')}" required><input class="form-control mb-2" name="visit_state" placeholder="State" value="${escapeHtml(props.leadState || '')}" required><input class="form-control mb-2" name="visit_city" placeholder="City" value="${escapeHtml(props.leadCity || '')}" required><input class="form-control mb-2" name="visit_zip" placeholder="Zip code" value="${escapeHtml(props.leadPincode || '')}" required><textarea class="form-control mb-2" name="visit_motive" placeholder="Visit motive" required>${escapeHtml(props.summary || '')}</textarea><label class="form-label">Scheduled date and time</label><input type="datetime-local" class="form-control" name="visit_scheduled_at" value="${date}T${time}" required>`;
            return `${common}<label class="form-label">Meeting link</label><input type="url" class="form-control mb-3" name="meeting_link" placeholder="Meeting link" required><label class="form-label">Scheduled date and time</label><input type="datetime-local" class="form-control mb-3" name="meeting_scheduled_at" value="${date}T${time}" required><label class="form-label">Meeting motive</label><textarea class="form-control" name="meeting_motive" placeholder="Meeting motive" required>${escapeHtml(props.summary || '')}</textarea>`;
        };

        const openActivityForm = (event, complete = false) => {
            const form = document.getElementById('calendar-activity-form');
            const typeLabel = event.extendedProps.activityType === 'gmeet' ? 'Meet' : event.extendedProps.activityType.charAt(0).toUpperCase() + event.extendedProps.activityType.slice(1);
            document.getElementById('calendarActivityModalLabel').textContent = complete ? `Complete ${typeLabel}` : `Edit ${typeLabel}`;
            document.getElementById('calendar-activity-submit').textContent = complete ? 'Mark complete' : 'Save changes';
            form.action = complete ? activityUrl(event, 'complete') : `{{ url('/sales/leads') }}/${event.extendedProps.leadId}/activities/${event.id}`;
            document.getElementById('calendar-activity-form-body').innerHTML = complete
                ? '<label class="form-label" for="calendar-final-note">Final note</label><textarea class="form-control" id="calendar-final-note" name="final_note" rows="6" required></textarea>'
                : activityFormFields(event);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('calendarDayModal')).hide();
            bootstrap.Modal.getOrCreateInstance(document.getElementById('calendarActivityModal')).show();
        };

        document.addEventListener('click', (clickEvent) => {
            const editButton = clickEvent.target.closest('[data-calendar-edit]');
            const completeButton = clickEvent.target.closest('[data-calendar-complete]');
            const eventId = editButton?.dataset.calendarEdit || completeButton?.dataset.calendarComplete;
            if (!eventId) return;
            const event = state.events.find((item) => String(item.id) === String(eventId));
            if (event) openActivityForm(event, Boolean(completeButton));
        });

        document.getElementById('calendar-activity-form').addEventListener('submit', async (submitEvent) => {
            submitEvent.preventDefault();
            const form = submitEvent.currentTarget;
            const submitButton = document.getElementById('calendar-activity-submit');
            submitButton.disabled = true;
            try {
                const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
                if (!response.ok) throw new Error('Unable to save activity.');
                bootstrap.Modal.getOrCreateInstance(document.getElementById('calendarActivityModal')).hide();
                await loadEvents();
            } catch (error) {
                window.alert(error.message);
            } finally {
                submitButton.disabled = false;
            }
        });

        const createDay = (date, isCurrentMonth) => {
            const day = document.createElement('div');
            day.className = `calendar-day${dateKey(date) === dateKey(today) ? ' today-cell' : ''}`;
            const dayEvents = visibleEventsForDate(date);
            const summary = document.createElement('div');
            summary.className = 'day-summary';
            const number = document.createElement('span');
            number.className = `day-summary-number${isCurrentMonth ? '' : ' muted'}`;
            number.textContent = date.getDate();
            summary.appendChild(number);

            const eventLabels = { followup: 'Followup', visit: 'Visit', gmeet: 'Meet' };
            Object.entries(eventLabels).forEach(([type, label]) => {
                const count = dayEvents.filter((event) => event.extendedProps.activityType === type).length;
                if (count === 0) return;
                const item = document.createElement('span');
                item.className = 'day-summary-item';
                item.textContent = `${label} (${count})`;
                summary.appendChild(item);
            });
            day.appendChild(summary);

            if (dayEvents.length > 0) {
                day.addEventListener('click', () => openDayModal(date, dayEvents));
            }

            return day;
        };

        const render = () => {
            const year = state.month.getFullYear();
            const month = state.month.getMonth();
            const firstDay = new Date(year, month, 1);
            const start = new Date(year, month, 1 - firstDay.getDay());
            const title = monthFormatter.format(state.month);
            calendarTitle.textContent = title;
            miniTitle.textContent = title;
            calendarGrid.innerHTML = '';
            miniGrid.innerHTML = '';

            for (let index = 0; index < 42; index += 1) {
                const date = new Date(start.getFullYear(), start.getMonth(), start.getDate() + index);
                calendarGrid.appendChild(createDay(date, date.getMonth() === month));
                if (index % 7 === 0) miniGrid.appendChild(document.createElement('tr'));
                const cell = document.createElement('td');
                cell.className = date.getMonth() === month ? '' : 'muted';
                if (dateKey(date) === dateKey(today)) cell.classList.add('selected');
                const cellContent = document.createElement('span');
                cellContent.textContent = date.getDate();
                cell.appendChild(cellContent);
                const miniEvents = visibleEventsForDate(date);
                if (miniEvents.length > 0) {
                    cell.title = `${miniEvents.length} scheduled record(s)`;
                    cell.classList.add('has-scheduled-events');
                    cell.addEventListener('click', () => openDayModal(date, miniEvents));
                }
                miniGrid.lastElementChild.appendChild(cell);
            }
        };

        const loadEvents = async () => {
            const year = state.month.getFullYear();
            const month = state.month.getMonth();
            const firstDay = new Date(year, month, 1);
            const start = new Date(year, month, 1 - firstDay.getDay());
            const end = new Date(year, month + 1, 7 - new Date(year, month + 1, 1).getDay());
            const response = await fetch(`{{ route('calendar.events') }}?start=${dateKey(start)}&end=${dateKey(end)}`, {
                headers: { Accept: 'application/json' },
            });
            state.events = response.ok ? await response.json() : [];
            render();
        };

        const miniCalendarInstance = { dateClick: (date) => {
            state.month = new Date(date.getFullYear(), date.getMonth(), 1);
            loadEvents();
        } };

        document.querySelectorAll('[data-calendar-previous]').forEach((button) => button.addEventListener('click', () => {
            state.month = new Date(state.month.getFullYear(), state.month.getMonth() - 1, 1);
            loadEvents();
        }));
        document.querySelectorAll('[data-calendar-next]').forEach((button) => button.addEventListener('click', () => {
            state.month = new Date(state.month.getFullYear(), state.month.getMonth() + 1, 1);
            loadEvents();
        }));
        document.querySelectorAll('.event-filters .filter-item').forEach((filter) => filter.addEventListener('click', () => {
            const type = filter.dataset.type;
            state.filters = type === 'all'
                ? new Set(['followup', 'visit', 'gmeet'])
                : (state.filters.has(type) ? new Set([...state.filters].filter((item) => item !== type)) : new Set([...state.filters, type]));
            render();
        }));

        void miniCalendarInstance;
        loadEvents().catch(() => { state.events = []; render(); });
    })();
</script>
@endpush