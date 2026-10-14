<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWorkspaceAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $company = $request->user()?->company;

        if (! $company || $company->onboarding_completed_at === null
            || $request->routeIs('home', 'login', 'register', 'logout', 'password.*', 'verification.*', 'subscription.*')
            || $company->hasWorkspaceAccess()) {
            return $next($request);
        }

        if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
            if ($request->routeIs(
                'clients.index', 'taxes.index', 'items.index', 'catalog-items.index',
                'quotations.index', 'jobs.index', 'invoices.index', 'payments.index', 'ledger.index',
                'staff.attendance.index', 'staff-manage', 'staff-roles',
                'sales-all-list', 'sales-lead-settings', 'sale-kanban', 'sales-leads.kanban-data',
                'integration-api-token', 'reports.index'
            )) {
                $request->attributes->set('company_read_only', true);

                return $next($request);
            }
        }

        $message = 'Your free trial or subscription has expired. Only module lists are available. Subscribe to continue.';

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], 403);
        }

        return redirect()->route('subscription.index')->with('access_error', $message);
    }
}
