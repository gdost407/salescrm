        const openDayModal = (date, dayEvents) => {
            const details = document.getElementById('calendar-day-details');
            document.getElementById('calendarDayModalLabel').textContent = `${new Intl.DateTimeFormat(undefined, { dateStyle: 'full' }).format(date)} (${dayEvents.length})`;
            details.innerHTML = dayEvents.length === 0
                ? '<p class="mb-0 text-body-secondary">No scheduled records for this date.</p>'
                : dayEvents.sort((first, second) => new Date(first.start) - new Date(second.start)).map((event) => {
                    const props = event.extendedProps;
                    const typeLabel = props.activityType === 'gmeet' ? 'Meet' : props.activityType === 'followup' ? 'Followup' : 'Visit';
                    const actionButtons = props.status === 'pending' ? `<div class="d-flex gap-1">
                        ${props.canEdit ? `<button type="button" class="btn btn-xs btn-outline-primary" title="Edit activity" data-calendar-edit="${event.id}"><i class="bx bx-edit"></i></button>` : ''}
                        ${props.canComplete ? `<button type="button" class="btn btn-xs btn-outline-success" title="Complete activity" data-calendar-complete="${event.id}"><i class="bx bx-check"></i></button>` : ''}
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
            return `${common}<label class="form-label">Meeting link</label><input type="url" class="form-control mb-3" name="meeting_link" placeholder="Meeting link" value="${escapeHtml(props.meetingLink || '')}" required><label class="form-label">Scheduled date and time</label><input type="datetime-local" class="form-control mb-3" name="meeting_scheduled_at" value="${date}T${time}" required><label class="form-label">Meeting motive</label><textarea class="form-control" name="meeting_motive" placeholder="Meeting motive" required>${escapeHtml(props.summary || '')}</textarea>`;
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
            const mobileEvent = clickEvent.target.closest('[data-mobile-event]');
            if (mobileEvent) {
                const event = state.events.find((item) => String(item.id) === String(mobileEvent.dataset.mobileEvent));
                if (event) {
                    const date = new Date(`${event.start.slice(0, 10)}T00:00:00`);
                    openDayModal(date, visibleEventsForDate(date));
                }
                return;
            }
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
                if (!response.ok) {
                    const result = await response.json().catch(() => ({}));
                    const errors = Object.values(result.errors || {}).flat().join('\n');
                    throw new Error(errors || result.message || 'Unable to save activity.');
                }
                bootstrap.Modal.getOrCreateInstance(document.getElementById('calendarActivityModal')).hide();
                await loadEvents();
            } catch (error) {
                window.alert(error.message);
            } finally {
                submitButton.disabled = false;
            }
        });
