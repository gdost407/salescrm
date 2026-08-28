<?php

namespace App\Http\Controllers\Api\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Integration;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadSetting;
use App\Models\WebhookLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LeadWebhookController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $startTime = now();
        $requestId = (string) Str::uuid();

        /** @var Integration $integration */
        $integration = $request->attributes->get('integration');
        /** @var Company $company */
        $company = $request->attributes->get('company');

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:30'],
            'alternate_mobile' => ['nullable', 'string', 'max:30'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'deal_amount' => ['nullable', 'numeric', 'min:0'],
            'stage' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'address' => ['nullable', 'string', 'max:65535'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'pincode' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:65535'],
            'notes' => ['nullable', 'string', 'max:65535'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
        ]);

        if ($validator->fails()) {
            $errorResponse = [
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()->toArray(),
            ];

            WebhookLog::create([
                'company_id' => $company->id,
                'integration_id' => $integration->id,
                'event' => 'lead.create',
                'request_id' => $requestId,
                'payload' => $request->all(),
                'response' => $errorResponse,
                'status_code' => 422,
                'status' => 'failed',
                'error_message' => 'Validation failed: '.implode(', ', $validator->errors()->all()),
                'received_at' => $startTime,
                'processed_at' => now(),
            ]);

            return response()->json($errorResponse, 422);
        }

        $validated = $validator->validated();

        $defaultStatus = LeadSetting::query()
            ->where('company_id', $company->id)
            ->where('setting_type', 'status')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->value('name') ?? 'New';

        $defaultStage = LeadSetting::query()
            ->where('company_id', $company->id)
            ->where('setting_type', 'stage')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->value('name') ?? 'New';

        $lead = Lead::create([
            'company_id' => $company->id,
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'mobile' => $validated['mobile'] ?? null,
            'alternate_mobile' => $validated['alternate_mobile'] ?? null,
            'job_title' => $validated['job_title'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'deal_amount' => $validated['deal_amount'] ?? 0,
            'stage' => $validated['stage'] ?? $defaultStage,
            'status' => $validated['status'] ?? $defaultStatus,
            'source' => $validated['source'] ?? 'Webhook',
            'assigned_to' => $validated['assigned_to'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'country' => $validated['country'] ?? null,
            'pincode' => $validated['pincode'] ?? null,
            'description' => $validated['description'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'priority' => $validated['priority'] ?? 'medium',
            'last_activity_at' => now(),
        ]);

        LeadActivity::create([
            'company_id' => $company->id,
            'lead_id' => $lead->id,
            'user_id' => data_get($integration->configuration, 'generated_by'),
            'activity_type' => 'notes',
            'subject' => 'Lead created via Webhook API',
            'summary' => 'Lead was created from external API webhook integration.',
            'completed_at' => now(),
            'status' => 'completed',
            'metadata' => [
                'via' => 'webhook_api',
                'integration_id' => $integration->id,
                'request_id' => $requestId,
            ],
        ]);

        $successResponse = [
            'success' => true,
            'message' => 'Lead created successfully via webhook.',
            'data' => [
                'id' => $lead->id,
                'name' => $lead->name,
                'email' => $lead->email,
                'mobile' => $lead->mobile,
                'company_name' => $lead->company_name,
                'deal_amount' => (float) $lead->deal_amount,
                'status' => $lead->status,
                'stage' => $lead->stage,
                'source' => $lead->source,
                'priority' => $lead->priority,
                'created_at' => $lead->created_at->toIso8601String(),
            ],
        ];

        WebhookLog::create([
            'company_id' => $company->id,
            'integration_id' => $integration->id,
            'event' => 'lead.create',
            'request_id' => $requestId,
            'payload' => $request->all(),
            'response' => $successResponse,
            'status_code' => 201,
            'status' => 'success',
            'received_at' => $startTime,
            'processed_at' => now(),
        ]);

        return response()->json($successResponse, 201);
    }
}
