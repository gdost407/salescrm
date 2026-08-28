<?php

namespace App\Http\Middleware;

use App\Models\Integration;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateWebhookApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken()
            ?? $request->header('X-API-Token')
            ?? $request->header('X-Webhook-Token')
            ?? $request->input('api_token')
            ?? $request->input('token');

        if (! $token || ! is_string($token)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. API token is missing.',
            ], 401);
        }

        $hashedToken = hash('sha256', $token);

        $integration = Integration::query()
            ->where('type', 'webhook')
            ->where('status', true)
            ->where('api_key', $hashedToken)
            ->with('company')
            ->first();

        if (! $integration || ! $integration->company) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Invalid or expired API token.',
            ], 401);
        }

        $request->attributes->set('integration', $integration);
        $request->attributes->set('company', $integration->company);

        return $next($request);
    }
}
