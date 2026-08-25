<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyOnboardingComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->user_type === 'owner'
            && $user->company
            && $user->company->onboarding_completed_at === null
            && ! $request->routeIs('company.onboarding')) {
            return redirect()->route('company.onboarding');
        }

        return $next($request);
    }
}
