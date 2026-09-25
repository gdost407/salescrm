<?php

namespace App\Actions;

use App\Models\Attendance;
use App\Models\Company;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateWebhookLead
{
    public function __construct(private SaveLead $saveLead) {}

    /** @param array<string, mixed> $attributes */
    public function handle(array $attributes): Lead
    {
        return DB::transaction(function () use ($attributes): Lead {
            $company = Company::whereKey($attributes['company_id'])->lockForUpdate()->firstOrFail();
            $staffIds = User::where('company_id', $company->id)->where('user_type', 'staff')
                ->where('is_active', true)->orderBy('id')->lockForUpdate()->pluck('id');
            $today = now(config('attendance.timezone'))->startOfDay();
            $presentIds = Attendance::where('company_id', $company->id)->whereIn('user_id', $staffIds)
                ->where('date', $today->toDateString())->where('punch_in', '<=', now())
                ->whereNull('punch_out')->pluck('user_id')->unique();
            $assignedTo = null;

            if ($presentIds->isNotEmpty()) {
                $counts = Lead::withTrashed()->where('company_id', $company->id)->whereIn('assigned_to', $presentIds)
                    ->where('created_at', '>=', $today->copy()->setTimezone(config('app.timezone')))
                    ->where('created_at', '<', $today->copy()->addDay()->setTimezone(config('app.timezone')))
                    ->select('assigned_to')->selectRaw('COUNT(*) as total')->groupBy('assigned_to')->pluck('total', 'assigned_to');
                $minimum = $presentIds->min(fn (int $id): int => (int) ($counts[$id] ?? 0));
                $assignedTo = $presentIds->filter(fn (int $id): bool => (int) ($counts[$id] ?? 0) === $minimum)->random();
            }

            return $this->saveLead->handle([...$attributes, 'assigned_to' => $assignedTo]);
        });
    }
}
