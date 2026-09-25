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
