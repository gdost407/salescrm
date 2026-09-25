<?php

namespace App\Actions;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordAttendance
{
    public function handle(User $user, string $action): Attendance
    {
        return DB::transaction(function () use ($user, $action): Attendance {
            $staff = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            abort_unless($staff->is_active && $staff->company_id && $staff->user_type === 'staff', 403);
            $now = now()->startOfSecond();
            $date = $now->copy()->setTimezone(config('attendance.timezone'))->toDateString();
            $query = Attendance::where('company_id', $staff->company_id)->where('user_id', $staff->id);
            $attendance = (clone $query)->whereNull('punch_out')->lockForUpdate()->first();

            if ($action === 'punch_in') {
                if ($attendance || (clone $query)->where('date', $date)->exists()) {
                    throw ValidationException::withMessages(['action' => 'You already punched in. Only one attendance record per day is allowed.']);
                }

                return Attendance::create(['company_id' => $staff->company_id, 'user_id' => $staff->id, 'date' => $date, 'punch_in' => $now]);
            }

            if (! $attendance) {
                throw ValidationException::withMessages(['action' => 'Punch in before recording a break or punching out.']);
            }

            if ($action === 'break_in' && ! $attendance->break_in) {
                $attendance->break_in = $now;
            } elseif ($action === 'break_out' && $attendance->break_in && ! $attendance->break_out) {
                $attendance->break_out = $now;
            } elseif ($action === 'punch_out' && (! $attendance->break_in || $attendance->break_out)) {
                $attendance->working_seconds = $attendance->workingSeconds();
                $attendance->punch_out = $now;
            } else {
                throw ValidationException::withMessages(['action' => 'This action is not available. End an active break before punching out; only one break is allowed.']);
            }

            $attendance->save();

            return $attendance;
        });
    }
}
