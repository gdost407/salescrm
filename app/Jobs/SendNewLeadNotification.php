<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendNewLeadNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $leadId) {}

    public function handle(): void
    {
        $lead = Lead::query()->find($this->leadId);

        if (! $lead) {
            return;
        }

        $recipients = User::query()
            ->where('company_id', $lead->company_id)
            ->where('is_active', true)
            ->with('role.permissions')
            ->get()
            ->filter(fn (User $user): bool => (int) $user->id === (int) $lead->assigned_to || $user->hasPermission('view_all_leads'));

        foreach ($recipients as $recipient) {
            Notification::firstOrCreate(
                [
                    'user_id' => $recipient->id,
                    'lead_id' => $lead->id,
                    'type' => 'new_lead',
                ],
                [
                    'company_id' => $lead->company_id,
                    'title' => 'New lead added',
                    'message' => $lead->name.' was added to the sales pipeline.',
                    'data' => ['url' => route('sales-lead-view', $lead)],
                ],
            );
        }
    }
}
