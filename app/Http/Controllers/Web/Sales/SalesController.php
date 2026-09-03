<?php

namespace App\Http\Controllers\Web\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteLeadActivityRequest;
use App\Http\Requests\StoreLeadActivityRequest;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Jobs\SendLeadActivityNotification;
use App\Models\Company;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadAttachment;
use App\Models\LeadSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SalesController extends Controller
{
    /**
     * Show the sales kanban board.
     */
    public function kanban()
    {
        $companyId = request()->user()->company_id;
        $statuses = LeadSetting::query()
            ->where('setting_type', 'status')
            ->where('is_active', true)
            ->where(function ($query) use ($companyId) {
                $query->whereNull('company_id')->orWhere('company_id', $companyId);
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name');
        $leads = Lead::query()->where('company_id', $companyId)->with('assignee:id,name')->latest()->get();
        $formData = $this->leadFormData(request());

        return view('app.sales.kanban', compact('leads', 'statuses') + $formData);
    }

    public function updateKanbanStatus(Request $request, Lead $lead): JsonResponse
    {
        $this->ensureLeadBelongsToUserCompany($request, $lead);
        $companyId = (int) $request->user()->company_id;
        $status = $request->validate([
            'status' => ['required', 'string', Rule::exists('lead_settings', 'name')->where(fn ($query) => $query
                ->where('setting_type', 'status')->where('is_active', true)
                ->where(function ($query) use ($companyId) {
                    $query->whereNull('company_id')->orWhere('company_id', $companyId);
                }))],
        ])['status'];

        if ($lead->status !== $status) {
            $oldStatus = $lead->status;
            $lead->update(['status' => $status]);
            $this->recordLeadEvent($lead, $request->user()->id, 'Status changed', $oldStatus.' -> '.$status);
        }

        return response()->json(['message' => 'Lead status updated.', 'status' => $lead->status]);
    }

    /**
     * Show create lead form.
     */
    public function createLead(Request $request)
    {
        return view('app.sales.create-lead', $this->leadFormData($request));
    }

    /**
     * Store a new lead.
     */
    public function storeLead(StoreLeadRequest $request): RedirectResponse|JsonResponse
    {
        $companyId = $this->ensureCompany($request);
        $validated = $this->validatedLeadData($request->validated(), $companyId);
        $lead = Lead::create($validated + ['company_id' => $companyId, 'created_by' => $request->user()->id]);
        $this->recordLeadEvent($lead, $request->user()->id, 'Lead created', 'Lead was created.');

        if ($request->expectsJson()) {
            $lead->load('assignee:id,name');

            return response()->json([
                'message' => 'Lead created successfully!',
                'status' => $lead->status,
                'html' => view('components.sales.kanban-card', compact('lead'))->render(),
            ], 201);
        }

        return to_route('sales-all-list')->with('message', 'Lead created successfully!');
    }

    public function kanbanLeadDetails(Request $request, Lead $lead): JsonResponse
    {
        $this->ensureLeadBelongsToUserCompany($request, $lead);
        $lead->load([
            'assignee:id,name',
            'creator:id,name',
            'activities' => fn ($query) => $query->with('user:id,name')->latest(),
        ]);

        return response()->json([
            'name' => $lead->name,
            'html' => view('components.sales.kanban-lead-details', compact('lead'))->render(),
        ]);
    }

    public function editLead(Request $request, Lead $lead)
    {
        $this->ensureLeadBelongsToUserCompany($request, $lead);

        return view('app.sales.create-lead', $this->leadFormData($request, $lead));
    }

    public function updateLead(UpdateLeadRequest $request, Lead $lead): RedirectResponse
    {
        $this->ensureLeadBelongsToUserCompany($request, $lead);
        $oldStatus = $lead->status;
        $lead->update($this->validatedLeadData($request->validated(), (int) $request->user()->company_id));

        if ($oldStatus !== $lead->status) {
            $this->recordLeadEvent($lead, $request->user()->id, 'Status changed', $oldStatus.' -> '.$lead->status);
        }

        return to_route('sales-all-list')->with('message', 'Lead updated successfully!');
    }

    public function destroyLead(Request $request, Lead $lead): RedirectResponse
    {
        $this->ensureLeadBelongsToUserCompany($request, $lead);
        $lead->delete();

        return to_route('sales-all-list')->with('message', 'Lead deleted successfully!');
    }

    public function storeLeadActivity(StoreLeadActivityRequest $request, Lead $lead): RedirectResponse|JsonResponse
    {
        $this->ensureLeadBelongsToUserCompany($request, $lead);
        $validated = $request->validated();
        $activity = $lead->activities()->create($this->activityData($validated, $request->user()->id, $lead));
        $lead->update(['last_activity_at' => now()]);
        $this->queueLeadActivityNotification($activity);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('lead-attachments', 'public');
            LeadAttachment::create([
                'company_id' => $lead->company_id,
                'lead_id' => $lead->id,
                'uploaded_by' => $request->user()->id,
                'original_name' => $file->getClientOriginalName(),
                'file_name' => basename($path),
                'file_path' => $path,
                'disk' => 'public',
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'file_extension' => $file->getClientOriginalExtension(),
                'description' => $activity->subject,
            ]);
        }

        if ($request->boolean('mark_as_lead_address')) {
            $lead->update([
                'address' => $validated['visit_address'],
                'country' => $validated['visit_country'],
                'state' => $validated['visit_state'],
                'city' => $validated['visit_city'],
                'pincode' => $validated['visit_zip'],
            ]);
        }

        return $this->activityResponse($request, $lead, 'Activity added successfully!');
    }

    public function updateLeadActivity(StoreLeadActivityRequest $request, Lead $lead, LeadActivity $activity): RedirectResponse|JsonResponse
    {
        $this->ensureLeadBelongsToUserCompany($request, $lead);
        $this->ensureActivityBelongsToLead($activity, $lead);
        $isLatestNoteOrCall = in_array($activity->activity_type, ['notes', 'call'], true)
            && $activity->id === $lead->activities()->where('activity_type', $activity->activity_type)->latest()->value('id');
        abort_if($activity->status === 'completed' && ! $isLatestNoteOrCall, 403);
        abort_if(in_array($activity->activity_type, ['notes', 'call'], true) && ! $isLatestNoteOrCall, 403);
        abort_if(($activity->metadata['activity_status'] ?? null) === 'rescheduled', 403);
        $newData = $this->activityData($request->validated(), $request->user()->id, $lead);
        $oldSchedule = $activity->scheduled_at?->format('Y-m-d H:i');
        $newSchedule = $newData['scheduled_at'] ? Carbon::parse($newData['scheduled_at'])->format('Y-m-d H:i') : null;
        if (in_array($activity->activity_type, ['followup', 'visit', 'gmeet'], true) && $oldSchedule !== $newSchedule) {
            $activity->update(['metadata' => array_merge($activity->metadata ?? [], ['activity_status' => 'rescheduled'])]);
            $activity = $lead->activities()->create($newData);
        } else {
            $activity->update($newData);
        }
        $lead->update(['last_activity_at' => now()]);
        $this->queueLeadActivityNotification($activity);

        return $this->activityResponse($request, $lead, 'Activity updated successfully!');
    }

    public function destroyLeadActivity(Request $request, Lead $lead, LeadActivity $activity): RedirectResponse|JsonResponse
    {
        $this->ensureLeadBelongsToUserCompany($request, $lead);
        $this->ensureActivityBelongsToLead($activity, $lead);
        abort_if($activity->status === 'completed', 403);
        abort_if(($activity->metadata['activity_status'] ?? null) === 'rescheduled', 403);
        abort_if(in_array($activity->activity_type, ['notes', 'call'], true), 403);
        $activity->delete();

        return $this->activityResponse($request, $lead, 'Activity deleted successfully!');
    }

    public function completeLeadActivity(CompleteLeadActivityRequest $request, Lead $lead, LeadActivity $activity): RedirectResponse|JsonResponse
    {
        $this->ensureLeadBelongsToUserCompany($request, $lead);
        $this->ensureActivityBelongsToLead($activity, $lead);
        abort_if($activity->status === 'completed', 403);
        abort_unless(in_array($activity->activity_type, ['followup', 'visit', 'gmeet'], true), 403);
        $activity->update([
            'status' => 'completed',
            'completed_at' => now(),
            'summary' => trim(($activity->summary ? $activity->summary."\n\n" : '').$request->validated('final_note')),
            'metadata' => array_merge($activity->metadata ?? [], ['activity_status' => 'completed']),
        ]);
        $lead->update(['last_activity_at' => now()]);

        return $this->activityResponse($request, $lead, 'Activity marked as completed!');
    }

    /**
     * Show all leads list.
     */
    public function allList(Request $request)
    {
        $filters = $this->leadListFilters($request);
        $leads = $this->filteredLeads($request, $filters)->paginate(25)->withQueryString();
        $filterOptions = $this->leadListOptions($request);

        return view('app.sales.all-list', compact('leads', 'filters', 'filterOptions'));
    }

    public function exportLeads(Request $request)
    {
        $filters = $this->leadListFilters($request);
        $leads = $this->filteredLeads($request, $filters)->get([
            'name', 'email', 'mobile', 'job_title', 'deal_amount', 'stage', 'status', 'source', 'assigned_to', 'priority', 'created_at', 'last_activity_at',
        ]);

        return response()->streamDownload(function () use ($leads): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Email', 'Mobile', 'Job title', 'Deal amount', 'Stage', 'Status', 'Source', 'Contact person', 'Priority', 'Created at', 'Last activity']);

            foreach ($leads as $lead) {
                fputcsv($handle, [
                    $lead->name, $lead->email, $lead->mobile, $lead->job_title, $lead->deal_amount,
                    $lead->stage, $lead->status, $lead->source, $lead->assignee?->name, $lead->priority,
                    $lead->created_at?->toDateTimeString(), $lead->last_activity_at?->toDateTimeString(),
                ]);
            }

            fclose($handle);
        }, 'leads-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    public function importLeads(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $headers = fgetcsv($handle);
        $headers = array_map(fn ($header) => Str::snake(trim((string) $header, " \t\n\r\0\x0B\xEF\xBB\xBF")), $headers ?: []);
        $requiredHeaders = ['name', 'stage', 'status', 'source'];

        if (count(array_diff($requiredHeaders, $headers)) > 0) {
            fclose($handle);

            throw ValidationException::withMessages(['file' => 'The CSV must include name, stage, status, and source columns.']);
        }

        $rows = [];
        $rowNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            if (count(array_filter($row, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            $rows[] = ['number' => $rowNumber, 'data' => array_combine($headers, array_pad(array_slice($row, 0, count($headers)), count($headers), null))];
        }
        fclose($handle);

        $companyId = (int) $request->user()->company_id;
        $options = $this->leadListOptions($request);
        $users = $options['users']->keyBy(fn ($user) => Str::lower($user->name));
        $usersByEmail = $options['users']->filter(fn ($user) => $user->email)->keyBy(fn ($user) => Str::lower($user->email));
        $errors = [];
        $leads = [];

        foreach ($rows as $row) {
            $data = collect($row['data'])->map(fn ($value) => is_string($value) ? trim($value) : $value)->all();
            $contactPerson = $data['contact_person'] ?? $data['assigned_to'] ?? null;
            if ($contactPerson !== null && $contactPerson !== '') {
                $contact = $usersByEmail->get(Str::lower($contactPerson)) ?? $users->get(Str::lower($contactPerson));
                $data['assigned_to'] = $contact?->id;
                if (! $contact) {
                    $errors[] = "Row {$row['number']}: contact person does not match an active company user.";

                    continue;
                }
            }

            $validator = validator($data, [
                'name' => ['required', 'string', 'max:255'], 'email' => ['nullable', 'email', 'max:255'],
                'mobile' => ['nullable', 'string', 'max:30'], 'job_title' => ['nullable', 'string', 'max:255'],
                'deal_amount' => ['nullable', 'numeric', 'min:0'], 'stage' => ['required', Rule::in($options['stages']->pluck('name')->all())],
                'status' => ['required', Rule::in($options['statuses']->pluck('name')->all())], 'source' => ['required', Rule::in($options['sources']->pluck('name')->all())],
                'assigned_to' => ['nullable', 'integer'], 'priority' => ['nullable', Rule::in(['low', 'medium', 'high', 'urgent'])],
                'address' => ['nullable', 'string', 'max:65535'], 'country' => ['nullable', 'string', 'max:255'],
                'state' => ['nullable', 'string', 'max:255'], 'city' => ['nullable', 'string', 'max:255'],
                'pincode' => ['nullable', 'string', 'max:20'], 'description' => ['nullable', 'string', 'max:65535'],
            ]);

            if ($validator->fails()) {
                $errors[] = "Row {$row['number']}: ".implode(' ', $validator->errors()->all());

                continue;
            }

            $leads[] = $validator->validated() + ['company_id' => $companyId, 'created_by' => $request->user()->id];
        }

        if ($errors) {
            return back()->withErrors(['file' => $errors]);
        }

        DB::transaction(fn () => collect($leads)->each(fn ($lead) => Lead::create($lead)));

        return to_route('sales-all-list')->with('message', count($leads).' lead(s) imported successfully.');
    }

    public function downloadLeadImportSample()
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['name', 'email', 'mobile', 'job_title', 'deal_amount', 'stage', 'status', 'source', 'contact_person', 'priority', 'address', 'country', 'state', 'city', 'pincode', 'description']);
            fputcsv($handle, ['Jane Lead', 'jane@example.com', '1234567890', 'Buyer', '2500.50', 'Qualification', 'Open', 'Referral', '', 'medium', '1 Main Street', 'India', 'Maharashtra', 'Pulgaon', '442302', 'Interested in the service']);
            fclose($handle);
        }, 'lead-import-sample.csv', ['Content-Type' => 'text/csv']);
    }

    private function filteredLeads(Request $request, array $filters)
    {
        return Lead::query()
            ->where('company_id', $request->user()->company_id)
            ->with('assignee:id,name')
            ->when($filters['search'], function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'], fn ($query, $status) => $query->where('status', $status))
            ->when($filters['stage'], fn ($query, $stage) => $query->where('stage', $stage))
            ->when($filters['source'], fn ($query, $source) => $query->where('source', $source))
            ->when($filters['assigned_to'], fn ($query, $assignedTo) => $query->where('assigned_to', $assignedTo))
            ->when($filters['date_from'], fn ($query, $dateFrom) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($filters['date_to'], fn ($query, $dateTo) => $query->whereDate('created_at', '<=', $dateTo))
            ->latest();
    }

    private function leadListFilters(Request $request): array
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'date_range' => ['nullable', Rule::in(['today', 'week', 'month', '3_month', 'year', 'custom'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'status' => ['nullable', 'string', 'max:255'],
            'stage' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')->where(fn ($query) => $query->where('company_id', $request->user()->company_id))],
        ]);
        $range = $validated['date_range'] ?? null;
        $dateFrom = $validated['date_from'] ?? null;
        $dateTo = $validated['date_to'] ?? null;

        if ($range && $range !== 'custom') {
            $dateTo = now()->toDateString();
            $dateFrom = match ($range) {
                'today' => now()->toDateString(),
                'week' => now()->startOfWeek()->toDateString(),
                'month' => now()->startOfMonth()->toDateString(),
                '3_month' => now()->subMonths(3)->toDateString(),
                'year' => now()->startOfYear()->toDateString(),
            };
        }

        return [
            'search' => trim($validated['search'] ?? ''), 'date_range' => $range,
            'date_from' => $dateFrom, 'date_to' => $dateTo,
            'status' => $validated['status'] ?? '', 'stage' => $validated['stage'] ?? '',
            'source' => $validated['source'] ?? '', 'assigned_to' => $validated['assigned_to'] ?? '',
        ];
    }

    private function leadListOptions(Request $request): array
    {
        $companyId = $request->user()->company_id;
        $settings = LeadSetting::query()
            ->where('is_active', true)
            ->where(function ($query) use ($companyId) {
                $query->whereNull('company_id')->orWhere('company_id', $companyId);
            })
            ->whereIn('setting_type', ['stage', 'status', 'source'])
            ->orderBy('sort_order')->orderBy('name')
            ->get(['setting_type', 'name'])->groupBy('setting_type');
        $users = User::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(['id', 'name', 'email']);

        return [
            'stages' => $settings->get('stage', collect()), 'statuses' => $settings->get('status', collect()),
            'sources' => $settings->get('source', collect()), 'users' => $users,
        ];
    }

    private function leadFormData(Request $request, ?Lead $lead = null): array
    {
        $companyId = $request->user()->company_id;
        $settings = LeadSetting::query()
            ->where('is_active', true)
            ->where(function ($query) use ($companyId) {
                $query->whereNull('company_id')->orWhere('company_id', $companyId);
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('setting_type');
        $users = User::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return compact('lead', 'settings', 'users');
    }

    private function ensureCompany(Request $request): int
    {
        if (! $request->user()->company_id) {
            $company = Company::create([
                'name' => $request->user()->name.' Company',
                'slug' => Str::slug($request->user()->name).'-'.Str::lower(Str::random(6)),
                'email' => $request->user()->email,
            ]);
            $request->user()->update(['company_id' => $company->id]);
        }

        return (int) $request->user()->fresh()->company_id;
    }

    private function validatedLeadData(array $validated, int $companyId): array
    {
        $settings = LeadSetting::query()
            ->where('is_active', true)
            ->where(function ($query) use ($companyId) {
                $query->whereNull('company_id')->orWhere('company_id', $companyId);
            })
            ->whereIn('setting_type', ['stage', 'status', 'source'])
            ->get(['setting_type', 'name'])
            ->groupBy('setting_type');

        foreach (['stage', 'status', 'source'] as $field) {
            validator([$field => $validated[$field]], [
                $field => [Rule::in($settings->get($field, collect())->pluck('name')->all())],
            ])->validate();
        }

        if (isset($validated['assigned_to'])) {
            validator(['assigned_to' => $validated['assigned_to']], [
                'assigned_to' => [Rule::exists((new User)->getTable(), 'id')->where(fn ($query) => $query
                    ->where('company_id', $companyId)
                    ->where('is_active', true))],
            ])->validate();
        }

        return $validated;
    }

    private function ensureLeadBelongsToUserCompany(Request $request, Lead $lead): void
    {
        abort_unless((int) $lead->company_id === (int) $request->user()->company_id, 404);
    }

    /**
     * Show lead settings.
     */
    public function leadSettings(Request $request)
    {
        $companyId = $request->user()->company_id;
        $leadSettings = LeadSetting::query()
            ->where('is_active', true)
            ->where(function ($query) use ($companyId) {
                $query->whereNull('company_id')->orWhere('company_id', $companyId);
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('setting_type');

        return view('app.sales.lead-settings', compact('leadSettings'));
    }

    public function storeLeadSetting(Request $request): RedirectResponse
    {
        if (! $request->user()->company_id) {
            $company = Company::create([
                'name' => $request->user()->name.' Company',
                'slug' => Str::slug($request->user()->name).'-'.Str::lower(Str::random(6)),
                'email' => $request->user()->email,
            ]);

            $request->user()->update(['company_id' => $company->id]);
        }

        $companyId = $request->user()->company_id;

        $validated = $request->validate([
            'setting_type' => ['required', Rule::in(['stage', 'status', 'source'])],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('lead_settings', 'name')->where(function ($query) use ($companyId, $request) {
                    $query->where('setting_type', $request->string('setting_type'))
                        ->where(function ($query) use ($companyId) {
                            $query->where('company_id', $companyId)->orWhereNull('company_id');
                        });
                }),
            ],
        ]);

        LeadSetting::create([
            'company_id' => $companyId,
            'setting_type' => $validated['setting_type'],
            'name' => trim($validated['name']),
            'type' => 'manual',
            'sort_order' => 0,
        ]);

        return to_route('sales-lead-settings')->with('success', 'Lead setting added successfully.');
    }

    public function updateLeadSetting(Request $request, LeadSetting $leadSetting): RedirectResponse
    {
        abort_unless((int) $leadSetting->company_id === (int) $request->user()->company_id && $leadSetting->type === 'manual', 404);

        $validated = $request->validate([
            'setting_type' => ['required', Rule::in(['stage', 'status', 'source'])],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('lead_settings', 'name')->ignore($leadSetting->id)->where(function ($query) use ($request, $leadSetting) {
                    $query->where('setting_type', $request->string('setting_type'))
                        ->where(function ($query) use ($leadSetting) {
                            $query->where('company_id', $leadSetting->company_id)->orWhereNull('company_id');
                        });
                }),
            ],
        ]);

        $leadSetting->update([
            'setting_type' => $validated['setting_type'],
            'name' => trim($validated['name']),
        ]);

        return to_route('sales-lead-settings')->with('success', 'Lead setting updated successfully.');
    }

    public function destroyLeadSetting(Request $request, LeadSetting $leadSetting): RedirectResponse
    {
        abort_unless((int) $leadSetting->company_id === (int) $request->user()->company_id && $leadSetting->type === 'manual', 404);

        $leadSetting->delete();

        return to_route('sales-lead-settings')->with('success', 'Lead setting deleted successfully.');
    }

    public function leadView(Request $request, Lead $lead)
    {
        $this->ensureLeadBelongsToUserCompany($request, $lead);
        $data = $this->leadViewData($lead);

        return view('app.sales.lead-view', $data);
    }

    public function leadActivityFragments(Request $request, Lead $lead)
    {
        $this->ensureLeadBelongsToUserCompany($request, $lead);

        return response()->json(['html' => view('app.sales.lead-tabs', $this->leadViewData($lead))->render()]);
    }

    private function activityResponse(Request $request, Lead $lead, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'html' => view('app.sales.lead-tabs', $this->leadViewData($lead))->render()]);
        }

        return to_route('sales-lead-view', $lead)->with('message', $message);
    }

    private function leadViewData(Lead $lead): array
    {
        $lead->load(['assignee', 'creator', 'activities.user', 'attachments', 'activities' => fn ($query) => $query->latest()]);
        $activities = $lead->activities;
        $activityEntries = $activities->groupBy('activity_type');
        $timeline = $activities->sortByDesc('created_at')->values();

        return compact('lead', 'activities', 'activityEntries', 'timeline');
    }

    private function recordLeadEvent(Lead $lead, int $userId, string $subject, string $summary): void
    {
        $lead->activities()->create([
            'company_id' => $lead->company_id,
            'user_id' => $userId,
            'activity_type' => 'notes',
            'subject' => $subject,
            'summary' => $summary,
            'status' => 'completed',
        ]);
        $lead->update(['last_activity_at' => now()]);
    }

    private function activityData(array $validated, int $userId, Lead $lead): array
    {
        $activityType = $validated['activity_type'];
        $subject = $validated['subject'] ?? match ($activityType) {
            'notes' => 'Note added',
            'call' => 'Call note added',
            'followup' => 'Follow-up scheduled',
            'visit' => 'Site visit scheduled',
            'gmeet' => 'Meeting scheduled',
            'email' => $validated['email_subject'],
        };
        $scheduledAt = $validated['followup_date'] ?? null;
        $scheduledAt = $scheduledAt && isset($validated['followup_time']) ? $scheduledAt.' '.$validated['followup_time'] : $scheduledAt;
        $scheduledAt ??= $validated['visit_scheduled_at'] ?? $validated['meeting_scheduled_at'] ?? null;
        $summary = $validated['summary'] ?? $validated['email_body'] ?? $validated['visit_motive'] ?? $validated['meeting_motive'] ?? null;
        $metadata = collect($validated)->only([
            'visit_address', 'visit_country', 'visit_state', 'visit_city', 'visit_zip',
            'mark_as_lead_address', 'meeting_link',
        ])->filter(fn ($value) => $value !== null && $value !== '')->all();

        return [
            'company_id' => $lead->company_id,
            'user_id' => $userId,
            'activity_type' => $activityType,
            'followup_type' => $activityType === 'followup' ? 'call' : null,
            'subject' => $subject,
            'summary' => $summary,
            'scheduled_at' => $scheduledAt,
            'status' => $scheduledAt ? 'pending' : 'completed',
            'metadata' => $metadata ?: null,
        ];
    }

    private function ensureActivityBelongsToLead(LeadActivity $activity, Lead $lead): void
    {
        abort_unless((int) $activity->lead_id === (int) $lead->id, 404);
    }

    private function queueLeadActivityNotification(LeadActivity $activity): void
    {
        if ($activity->scheduled_at && in_array($activity->activity_type, ['followup', 'visit', 'gmeet'], true)) {
            SendLeadActivityNotification::dispatch($activity->id, 'before')->delay($activity->scheduled_at->copy()->subMinutes(10));
            SendLeadActivityNotification::dispatch($activity->id, 'at_time')->delay($activity->scheduled_at);
        }
    }
}
