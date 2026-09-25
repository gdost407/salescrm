@extends('layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4>Attendance</h4>
    <p class="text-muted">{{ $canViewAll ? 'Company staff attendance' : 'My attendance' }} &middot; {{ config('attendance.timezone') }}</p>
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    <div class="card mb-4"><div class="card-body">
        <form method="GET" action="{{ route('staff.attendance.index') }}" class="row g-3 align-items-end">
            <div class="col-sm-6 col-lg-3"><label class="form-label" for="attendance-month">Month</label><input type="month" id="attendance-month" name="month" value="{{ $month->format('Y-m') }}" class="form-control" required></div>
            @if($canViewAll)
            <div class="col-sm-6 col-lg-3"><label class="form-label" for="attendance-staff">Staff</label><select id="attendance-staff" name="staff_id" class="form-select"><option value="">All staff</option>@foreach($staff as $member)<option value="{{ $member->id }}" @selected((string) request('staff_id') === (string) $member->id)>{{ $member->name }}</option>@endforeach</select></div>
            @endif
            <div class="col-sm-6 col-lg-2"><label class="form-label" for="attendance-status">Status</label><select id="attendance-status" name="status" class="form-select"><option value="">All statuses</option>@foreach(['working' => 'Working', 'break' => 'On break', 'completed' => 'Completed'] as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-sm-6 col-lg-2"><label class="form-label" for="attendance-view">View</label><select id="attendance-view" name="view" class="form-select"><option value="table" @selected($view === 'table')>Table</option><option value="calendar" @selected($view === 'calendar')>Calendar</option></select></div>
            <div class="col-sm-6 col-lg-2"><button class="btn btn-primary" type="submit">Apply</button> <a href="{{ route('staff.attendance.index') }}" class="btn btn-outline-secondary">Reset</a></div>
        </form>
    </div></div>
    @if($view === 'calendar')
    <div class="card mb-4">
        <h5 class="card-header">{{ $month->format('F Y') }}</h5>
        <div class="table-responsive"><table class="table table-bordered text-center mb-0">
            <thead><tr>@foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $weekday)<th>{{ $weekday }}</th>@endforeach</tr></thead>
            <tbody>
                @foreach($calendarDays->chunk(7) as $week)
                <tr>
                    @foreach($week as $day)
                    @if($day)
                        <td class="align-top p-3 {{ $day['date']->toDateString() === now(config('attendance.timezone'))->toDateString() ? 'table-primary' : '' }}">
                            <a class="d-block" href="{{ route('staff.attendance.index', array_merge(request()->only('staff_id', 'status'), ['month' => $month->format('Y-m'), 'view' => 'calendar', 'date' => $day['date']->toDateString()])) }}">{{ $day['date']->day }}<small class="d-block text-nowrap">{{ $day['count'] }} {{ $day['count'] === 1 ? 'record' : 'records' }}</small></a>
                        </td>
                    @else
                        <td></td>
                    @endif
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table></div>
        <div class="card-body text-muted">Select a day to see its records below. Days without records are not automatically marked absent.</div>
    </div>
    @endif
    <div class="card">
        <h5 class="card-header">{{ request('date') ? 'Records for '.request('date') : 'Attendance records' }}</h5>
        <div class="table-responsive"><table class="table">
            <thead><tr><th>Date</th><th>Staff</th><th>Punch in</th><th>Punch out</th><th>Break start</th><th>Break end</th><th>Working hours</th><th>Status</th></tr></thead>
            <tbody>@forelse($records as $record)
                <tr>
                    <td class="text-nowrap">{{ $record->date->format('d M Y') }}</td><td>{{ $record->user?->name ?? 'Deleted staff' }}</td>
                    @foreach(['punch_in', 'punch_out', 'break_in', 'break_out'] as $field)
                        <td class="text-nowrap">{{ $record->$field?->setTimezone(config('attendance.timezone'))->format('d M, h:i A') ?? '—' }}</td>
                    @endforeach
                    <td>{{ $record->workingHours() }} <small class="text-muted">HH:MM</small></td><td><span class="badge bg-label-primary">{{ $record->status() }}</span></td>
                </tr>
            @empty<tr><td colspan="8" class="text-center py-4 text-muted">No attendance records found.</td></tr>@endforelse</tbody>
        </table></div>
        <div class="card-body">{{ $records->links() }}</div>
    </div>
</div>
@endsection
