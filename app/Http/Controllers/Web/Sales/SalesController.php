<?php

namespace App\Http\Controllers\Web\Sales;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\LeadSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SalesController extends Controller
{
    /**
     * Show the sales kanban board.
     */
    public function kanban()
    {
        $leads = [
            ['id' => 1, 'title' => 'Lead 1', 'status' => 'Prospecting', 'value' => '$5,000'],
            ['id' => 2, 'title' => 'Lead 2', 'status' => 'Qualified', 'value' => '$8,000'],
            ['id' => 3, 'title' => 'Lead 3', 'status' => 'Negotiation', 'value' => '$12,000'],
        ];

        return view('app.sales.kanban', compact('leads'));
    }

    /**
     * Show create lead form.
     */
    public function createLead()
    {
        return view('app.sales.create-lead');
    }

    /**
     * Store a new lead.
     */
    public function storeLead()
    {
        // TODO: Implement lead storage logic
        return redirect()->route('sales-all-list')->with('message', 'Lead created successfully!');
    }

    /**
     * Show all leads list.
     */
    public function allList()
    {
        $leads = [
            ['id' => 1, 'name' => 'John Smith', 'email' => 'john@example.com', 'company' => 'Tech Corp', 'value' => '$5,000', 'status' => 'Prospecting'],
            ['id' => 2, 'name' => 'Jane Doe', 'email' => 'jane@example.com', 'company' => 'Innovation Inc', 'value' => '$8,000', 'status' => 'Qualified'],
            ['id' => 3, 'name' => 'Mike Johnson', 'email' => 'mike@example.com', 'company' => 'Digital Solutions', 'value' => '$12,000', 'status' => 'Negotiation'],
            ['id' => 4, 'name' => 'Sarah Williams', 'email' => 'sarah@example.com', 'company' => 'Future Tech', 'value' => '$15,000', 'status' => 'Closed'],
        ];

        return view('app.sales.all-list', compact('leads'));
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

    /**
     * Show lead view.
     */
    public function leadView()
    {
        $lead = [
            'id' => 1,
            'name' => 'John Smith',
            'email' => 'john@example.com',
            'company' => 'Tech Corp',
            'value' => '$5,000',
            'status' => 'Prospecting',
            'notes' => [
                ['id' => 1, 'note' => 'Initial contact', 'date' => '2023-08-01'],
                ['id' => 2, 'note' => 'Follow-up call', 'date' => '2023-08-05'],
            ],
            'activities' => [
                ['id' => 1, 'activity' => 'Email sent', 'date' => '2023-08-01'],
                ['id' => 2, 'activity' => 'Call scheduled', 'date' => '2023-08-05'],
            ],
        ];

        return view('app.sales.lead-view', compact('lead'));
    }
}
