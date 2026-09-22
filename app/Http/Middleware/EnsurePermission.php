<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        abort_unless($request->user()?->hasPermission($permission), 403);

        if ($staff = $request->route('staffUser')) {
            abort_unless((int) $staff->company_id === (int) $request->user()->company_id, 404);
            abort_unless($request->user()->canManageStaffAccount($staff, explode('_', $permission)[0]), 403);
        }

        return $next($request);
    }
}
