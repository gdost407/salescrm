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
        $action = match ($method) {
            'createLead', 'storeLead', 'importLeads', 'downloadLeadImportSample' => 'create',
            'editLead', 'updateLead', 'assignKanbanLead', 'updateKanbanStatus',
            'storeLeadActivity', 'updateLeadActivity', 'destroyLeadActivity', 'completeLeadActivity' => 'edit',
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
