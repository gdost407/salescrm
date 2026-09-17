<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLeadAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $method = $request->route()->getActionMethod();
        $activityAction = match ($method) {
            'storeLeadActivity' => 'create',
            'updateLeadActivity' => 'edit',
            'destroyLeadActivity' => 'delete',
            'completeLeadActivity' => 'complete',
            default => null,
        };
        if ($activityAction !== null) {
            $lead = $request->route('lead');
            $activity = $request->route('activity');
            abort_unless((int) $lead->company_id === (int) $request->user()->company_id, 404);
            abort_if($activity && ((int) $activity->company_id !== (int) $lead->company_id || (int) $activity->lead_id !== (int) $lead->id), 404);
            $type = $activity?->activity_type ?? $request->input('activity_type');
            abort_unless(is_string($type) && $request->user()->canAccessLeadActivity($lead, $activityAction, $type, $activity), 403);
            abort_if($request->boolean('mark_as_lead_address') && ! $request->user()->canAccessLead($lead, 'edit'), 403);

            return $next($request);
        }
        $action = match ($method) {
            'createLead', 'storeLead', 'importLeads', 'downloadLeadImportSample' => 'create',
            'editLead', 'updateLead', 'assignKanbanLead', 'updateKanbanStatus' => 'edit',
            'destroyLead' => 'delete',
            'leadSettings', 'storeLeadSetting', 'updateLeadSetting', 'destroyLeadSetting' => 'settings',
            'kanban', 'kanbanLeads', 'allList', 'exportLeads', 'kanbanLeadDetails',
            'leadView', 'leadActivityFragments', 'index', 'events' => 'view',
            default => null,
        };

        abort_if($action === null, 403);
        $user = $request->user();
        if ($lead = $request->route('lead')) {
            abort_unless((int) $lead->company_id === (int) $user->company_id, 404);
            abort_unless($user->canAccessLead($lead, $action), 403);
        } else {
            $permission = match ($action) {
                'create' => 'create_leads',
                'settings' => 'edit_all_leads',
                default => $action.'_own_leads',
            };
            abort_unless($user->hasPermission($permission), 403);
        }

        return $next($request);
    }
}
