<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LeadActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        return view('app.calendar.index');
    }

    /**
     * Return CRM activities (followup, visit, gmeet) as FullCalendar JSON events.
     */
    public function events(Request $request): JsonResponse
    {
        $user = $request->user();

        $start = $request->query('start');
        $end = $request->query('end');

        $activities = LeadActivity::with([
            'lead:id,name,email,mobile,status,stage,source,assigned_to,address,country,state,city,pincode',
        ])
            ->where('company_id', $user->company_id)
            ->whereIn('activity_type', ['followup', 'visit', 'gmeet'])
            ->whereNotNull('scheduled_at')
            ->when($start, fn ($q) => $q->where('scheduled_at', '>=', $start))
            ->when($end, fn ($q) => $q->where('scheduled_at', '<=', $end))
            ->orderBy('scheduled_at')
            ->get();

        $events = $activities->map(function (LeadActivity $activity): array {
            $leadName = $activity->lead?->name ?? 'Unknown Lead';
            $type = $activity->activity_type;
            $followupType = $activity->followup_type;

            $label = match ($type) {
                'followup' => 'Follow-up ('.ucfirst($followupType ?? 'call').')',
                'visit' => 'Visit',
                'gmeet' => 'G-Meet',
                default => ucfirst($type),
            };

            $color = match ($type) {
                'followup' => '#696cff', // primary / blue-violet
                'visit' => '#fd7e14', // orange
                'gmeet' => '#71dd37', // green
                default => '#8592a3',
            };

            $textColor = match ($type) {
                'gmeet' => '#2d4a12',
                default => '#ffffff',
            };

            return [
                'id' => $activity->id,
                'title' => "{$label} — {$leadName}",
                'start' => $activity->scheduled_at->toIso8601String(),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => $textColor,
                'extendedProps' => [
                    'activityType' => $type,
                    'followupType' => $followupType,
                    'leadName' => $leadName,
                    'leadId' => $activity->lead_id,
                    'subject' => $activity->subject,
                    'summary' => $activity->summary,
                    'status' => $activity->status,
                    'scheduledAt' => $activity->scheduled_at->toIso8601String(),
                    'leadEmail' => $activity->lead?->email,
                    'leadMobile' => $activity->lead?->mobile,
                    'leadStatus' => $activity->lead?->status,
                    'leadStage' => $activity->lead?->stage,
                    'leadSource' => $activity->lead?->source,
                    'leadAddress' => $activity->lead?->address,
                    'leadCountry' => $activity->lead?->country,
                    'leadState' => $activity->lead?->state,
                    'leadCity' => $activity->lead?->city,
                    'leadPincode' => $activity->lead?->pincode,
                ],
            ];
        });

        return response()->json($events->values());
    }
}
