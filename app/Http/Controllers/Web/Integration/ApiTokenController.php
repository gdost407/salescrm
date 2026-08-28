<?php

namespace App\Http\Controllers\Web\Integration;

use App\Actions\Integrations\RotateWebhookApiToken;
use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateApiTokenRequest;
use App\Models\Integration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiTokenController extends Controller
{
    public function index(Request $request): View
    {
        $tokens = Integration::query()
            ->where('company_id', $request->user()->company_id)
            ->where('type', 'webhook')
            ->latest()
            ->get();

        return view('app.integration.api-token', compact('tokens'));
    }

    public function store(GenerateApiTokenRequest $request, RotateWebhookApiToken $rotateWebhookApiToken): RedirectResponse
    {
        $token = $rotateWebhookApiToken->handle($request->user()->company, $request->user());

        return to_route('integration-api-token')->with('api_token', $token);
    }

    public function googleSheet(Request $request): View
    {
        return view('app.integration.google-sheet');
    }
}
