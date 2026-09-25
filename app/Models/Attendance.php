<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\AttendanceFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    /** @use HasFactory<AttendanceFactory> */
    use HasFactory;

    protected $fillable = ['company_id', 'user_id', 'date', 'punch_in', 'punch_out', 'break_in', 'break_out', 'working_seconds'];

    protected $attributes = ['working_seconds' => 0];

    protected function casts(): array
    {
        return ['date' => 'date', 'punch_in' => 'immutable_datetime', 'punch_out' => 'immutable_datetime',
            'break_in' => 'immutable_datetime', 'break_out' => 'immutable_datetime', 'working_seconds' => 'integer'];
    }

    public function setDateAttribute(string|DateTimeInterface $value): void
    {
        $this->attributes['date'] = CarbonImmutable::parse($value)->toDateString();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where('company_id', $user->company_id)
            ->when(! $user->canViewAllAttendance(), fn (Builder $query) => $query->where('user_id', $user->id));
    }

    public function status(): string
    {
        return $this->punch_out ? 'Completed' : ($this->break_in && ! $this->break_out ? 'On break' : 'Working');
    }

    public function workingSeconds(): int
    {
        if ($this->punch_out) {
            return $this->working_seconds;
        }

        $end = now();
        $breakSeconds = $this->break_in ? $this->break_in->diffInSeconds($this->break_out ?? $end) : 0;

        return max(0, (int) ($this->punch_in->diffInSeconds($end) - $breakSeconds));
    }

    public function workingHours(): string
    {
        $minutes = intdiv($this->workingSeconds(), 60);

        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }

    public static function dashboardRecord(User $user): ?self
    {
        return self::query()->where('company_id', $user->company_id)->where('user_id', $user->id)
            ->where(function (Builder $query): void {
                $query->whereNull('punch_out')->orWhere('date', now(config('attendance.timezone'))->toDateString());
            })->orderByRaw('punch_out IS NOT NULL')->latest('date')->first();
    }
}
