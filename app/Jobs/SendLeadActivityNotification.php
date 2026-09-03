<?php

namespace App\Jobs;

use App\Models\LeadActivity;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendLeadActivityNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $activityId, public string $reminderWindow = 'at_time') {}

    public function handle(): void
    {
        $activity = LeadActivity::with(['lead', 'user'])->find($this->activityId);

        if (! $activity || ! in_array($activity->activity_type, ['followup', 'visit', 'gmeet'], true)
            || $activity->status !== 'pending' || ! $activity->scheduled_at
            || ($activity->metadata['activity_status'] ?? null) === 'rescheduled'
            || ! $activity->lead?->assigned_to
            || ($this->reminderWindow === 'before' && $activity->scheduled_at->lessThanOrEqualTo(now()))
            || ($this->reminderWindow === 'at_time' && $activity->scheduled_at->isFuture())) {
            return;
        }

        $label = match ($activity->activity_type) {
            'followup' => 'Follow-up',
            'visit' => 'Site visit',
            'gmeet' => 'Meeting',
        };
        $isEarlyReminder = $this->reminderWindow === 'before';
        $notificationType = 'lead_activity_reminder_'.$this->reminderWindow;
        $title = $isEarlyReminder ? $label.' in 10 minutes' : $label.' now';
        $message = $activity->lead->name.' has a '.$label.' scheduled '.($isEarlyReminder ? 'in 10 minutes.' : 'now.');

        Notification::firstOrCreate(
            [
                'user_id' => $activity->lead->assigned_to,
                'activity_id' => $activity->id,
                'type' => $notificationType,
            ],
            [
                'company_id' => $activity->company_id,
                'lead_id' => $activity->lead_id,
                'title' => $title,
                'message' => $message,
                'data' => [
                    'scheduled_at' => $activity->scheduled_at->toIso8601String(),
                    'reminder_window' => $this->reminderWindow,
                    'url' => route('sales-lead-view', $activity->lead),
                ],
            ],
        );
    }
}
