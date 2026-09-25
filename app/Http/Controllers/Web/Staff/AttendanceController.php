<?php

namespace App\Http\Controllers\Web\Staff;

use App\Actions\RecordAttendance;
use App\Http\Controllers\Controller;
use App\Http\Requests\PunchAttendanceRequest;
use App\Models\Attendance;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->is_active && $user->company_id && in_array($user->user_type, ['owner', 'staff'], true), 403);
        $canViewAll = $user->canViewAllAttendance();
        $filters = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'view' => ['nullable', Rule::in(['table', 'calendar'])],
            'staff_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('company_id', $user->company_id)->where('user_type', 'staff')],
            'status' => ['nullable', Rule::in(['working', 'break', 'completed'])],
        ]);
        abort_if(! $canViewAll && $request->filled('staff_id') && (int) $filters['staff_id'] !== $user->id, 403);
        $month = CarbonImmutable::createFromFormat('!Y-m', $filters['month'] ?? now(config('attendance.timezone'))->format('Y-m'));
        $view = $filters['view'] ?? 'table';
        $query = Attendance::visibleTo($user)->with('user:id,name')
            ->whereBetween('date', [$month->toDateString(), $month->endOfMonth()->toDateString()])
            ->when($request->filled('staff_id'), fn ($query) => $query->where('user_id', $filters['staff_id']));
        match ($filters['status'] ?? null) {
            'working' => $query->whereNull('punch_out')->where(fn ($query) => $query->whereNull('break_in')->orWhereNotNull('break_out')),
            'break' => $query->whereNull('punch_out')->whereNotNull('break_in')->whereNull('break_out'),
            'completed' => $query->whereNotNull('punch_out'),
            default => null,
        };
        $records = (clone $query)->when($request->filled('date'), fn ($query) => $query->where('date', $filters['date']))
            ->orderByDesc('date')->orderBy('punch_in')->paginate(25)->withQueryString();
        $calendarDays = collect();
        if ($view === 'calendar') {
            $calendarDays = collect(array_fill(0, $month->dayOfWeek, null));
            $counts = (clone $query)->select('date')->selectRaw('COUNT(*) as total')->groupBy('date')->pluck('total', 'date');
            for ($day = $month; $day->month === $month->month; $day = $day->addDay()) {
                $calendarDays->push(['date' => $day, 'count' => (int) ($counts[$day->toDateString()] ?? 0)]);
            }
            $calendarDays = $calendarDays->pad((int) (ceil($calendarDays->count() / 7) * 7), null);
        }
        $staff = $canViewAll ? User::where('company_id', $user->company_id)->where('user_type', 'staff')->orderBy('name')->get(['id', 'name']) : collect();

        return view('app.staff.attendance', compact('records', 'calendarDays', 'month', 'view', 'staff', 'canViewAll'));
    }

    public function punch(PunchAttendanceRequest $request, RecordAttendance $recordAttendance): JsonResponse
    {
        $attendance = $recordAttendance->handle($request->user(), $request->validated('action'));

        return response()->json(['message' => 'Attendance recorded.', 'status' => $attendance->status()]);
    }
}
