<?php

namespace App\Jobs;

use App\Models\LeadActivity;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendLeadActivityNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $activityId) {}

    public function handle(): void
    {
        $activity = LeadActivity::with(['lead', 'user'])->find($this->activityId);

        if (! $activity || ! in_array($activity->activity_type, ['followup', 'visit', 'gmeet'], true)
            || $activity->status !== 'pending' || ! $activity->scheduled_at
            || $activity->scheduled_at->isFuture() || ($activity->metadata['activity_status'] ?? null) === 'rescheduled') {
            return;
        }

        $recipientIds = collect([$activity->user_id, $activity->lead?->assigned_to])
            ->filter()
            ->unique()
            ->values();
        $label = match ($activity->activity_type) {
            'followup' => 'Follow-up',
            'visit' => 'Site visit',
            'gmeet' => 'Meeting',
        };

        foreach ($recipientIds as $recipientId) {
            Notification::firstOrCreate(
                [
                    'user_id' => $recipientId,
                    'activity_id' => $activity->id,
                    'type' => 'lead_activity_reminder',
                ],
                [
                    'company_id' => $activity->company_id,
                    'lead_id' => $activity->lead_id,
                    'title' => $label.' reminder',
                    'message' => $activity->lead?->name.' has a '.$label.' scheduled now.',
                    'data' => [
                        'scheduled_at' => $activity->scheduled_at->toIso8601String(),
                        'url' => route('sales-lead-view', $activity->lead),
                    ],
                ],
            );
        }
    }
}
