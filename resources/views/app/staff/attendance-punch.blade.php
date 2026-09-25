<div class="card" id="attendance-punch">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
            <h5 class="mb-0">My attendance</h5>
            <a href="{{ route('staff.attendance.index') }}">View attendance</a>
        </div>
        <p class="text-muted">{{ $attendance?->date->format('d M Y') ?? now(config('attendance.timezone'))->format('d M Y') }} &middot; {{ config('attendance.timezone') }} &middot; {{ $attendance?->status() ?? 'Not punched in' }}</p>
        <div class="d-flex flex-wrap gap-4 mb-3">
            <span>Punch in: <strong>{{ $attendance?->punch_in?->setTimezone(config('attendance.timezone'))->format('h:i A') ?? '—' }}</strong></span>
            <span>Punch out: <strong>{{ $attendance?->punch_out?->setTimezone(config('attendance.timezone'))->format('h:i A') ?? '—' }}</strong></span>
            <span>Break start: <strong>{{ $attendance?->break_in?->setTimezone(config('attendance.timezone'))->format('h:i A') ?? '—' }}</strong></span>
            <span>Break end: <strong>{{ $attendance?->break_out?->setTimezone(config('attendance.timezone'))->format('h:i A') ?? '—' }}</strong></span>
            <span>Working hours: <strong>{{ $attendance?->workingHours() ?? '00:00' }}</strong> (HH:MM)</span>
        </div>
        @if($attendance && ! $attendance->punch_out && $attendance->date->toDateString() !== now(config('attendance.timezone'))->toDateString())
            <p class="text-warning">Your earlier shift is still open. Finish it before starting today's attendance.</p>
        @endif
        <div class="alert alert-danger d-none" id="attendance-error" role="alert"></div>
        <form id="attendance-punch-form" action="{{ route('staff.attendance.punch') }}" method="POST" class="d-flex flex-wrap gap-2">
            @csrf
            @if(! $attendance)
                <button type="submit" name="action" value="punch_in" class="btn btn-primary">Punch in</button>
            @elseif(! $attendance->punch_out)
                @if(! $attendance->break_in)
                    <button type="submit" name="action" value="break_in" class="btn btn-outline-warning">Start break</button>
                @elseif(! $attendance->break_out)
                    <button type="submit" name="action" value="break_out" class="btn btn-warning">End break</button>
                @endif
                @if(! $attendance->break_in || $attendance->break_out)
                    <button type="submit" name="action" value="punch_out" class="btn btn-outline-primary">Punch out</button>
                @endif
            @else
                <span class="badge bg-label-success">Attendance completed for today</span>
            @endif
        </form>
    </div>
</div>
@push('scripts')
<script>
    $('#attendance-punch-form').on('submit', function (event) {
        event.preventDefault();
        const form = $(this);
        const submitter = event.originalEvent.submitter;
        if (!submitter || form.data('submitting')) return;
        const data = form.serializeArray();
        data.push({ name: 'action', value: submitter.value });
        form.data('submitting', true).find('button').prop('disabled', true);
        $('#attendance-error').addClass('d-none').text('');
        $.ajax({
            url: form.attr('action'), method: 'POST', data, headers: { Accept: 'application/json' },
            success: function () { window.location.reload(); },
            error: function (xhr) {
                const response = xhr.responseJSON || {};
                const errors = Object.values(response.errors || {}).flat().join(' ');
                $('#attendance-error').removeClass('d-none').text(errors || response.message || 'Unable to record attendance. Please try again.');
            },
            complete: function () { form.data('submitting', false).find('button').prop('disabled', false); }
        });
    });
</script>
@endpush
