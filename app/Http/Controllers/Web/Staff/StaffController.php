<?php

namespace App\Http\Controllers\Web\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffRequest;
use App\Mail\StaffCredentials;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StaffController extends Controller
{
    /**
     * Show create staff form.
     */
    public function create(): View
    {
        return view('app.staff.create');
    }

    /**
     * Store a new staff member.
     */
    public function store(StoreStaffRequest $request): RedirectResponse
    {
        $temporaryPassword = Str::random(16);

        DB::transaction(function () use ($request, $temporaryPassword): void {
            $company = Company::query()
                ->whereKey($request->user()->company_id)
                ->lockForUpdate()
                ->firstOrFail();
            $activeSubscription = $company->subscriptions()
                ->where('status', 'active')
                ->with('plan')
                ->latest('starts_at')
                ->first();
            $staffLimit = $activeSubscription?->plan?->max_users ?? $company->staff_limit;

            if ($company->users()->where('user_type', 'staff')->count() >= $staffLimit) {
                abort(422, 'Your current plan does not allow any more staff members.');
            }

            $staff = User::create([
                ...$request->validated(),
                'company_id' => $company->id,
                'user_type' => 'staff',
                'password' => $temporaryPassword,
            ]);

            Mail::to($staff)->send(new StaffCredentials($staff, $temporaryPassword));
        });

        return redirect()->route('staff-manage')->with('message', 'Staff member created successfully!');
    }

    /**
     * Show manage staff list.
     */
    public function manage(Request $request): View
    {
        $staffMembers = User::query()
            ->where('company_id', $request->user()->company_id)
            ->where('user_type', 'staff')
            ->latest()
            ->get();

        return view('app.staff.manage', compact('staffMembers'));
    }

    /**
     * Show roles and permissions management.
     */
    public function roles()
    {
        $roles = [
            [
                'id' => 1,
                'name' => 'Sales Executive',
                'description' => 'Can manage own leads and activities',
                'permissions' => ['view_leads', 'create_leads', 'edit_own_leads'],
            ],
            [
                'id' => 2,
                'name' => 'Sales Manager',
                'description' => 'Can manage team leads and performance',
                'permissions' => ['view_leads', 'create_leads', 'edit_all_leads', 'view_reports', 'manage_team'],
            ],
            [
                'id' => 3,
                'name' => 'Team Lead',
                'description' => 'Can oversee team activities',
                'permissions' => ['view_leads', 'create_leads', 'edit_team_leads', 'view_reports'],
            ],
            [
                'id' => 4,
                'name' => 'Administrator',
                'description' => 'Full system access',
                'permissions' => ['all'],
            ],
        ];

        $permissions = [
            ['name' => 'view_leads', 'label' => 'View Leads'],
            ['name' => 'create_leads', 'label' => 'Create Leads'],
            ['name' => 'edit_own_leads', 'label' => 'Edit Own Leads'],
            ['name' => 'edit_team_leads', 'label' => 'Edit Team Leads'],
            ['name' => 'edit_all_leads', 'label' => 'Edit All Leads'],
            ['name' => 'delete_leads', 'label' => 'Delete Leads'],
            ['name' => 'view_reports', 'label' => 'View Reports'],
            ['name' => 'manage_team', 'label' => 'Manage Team'],
        ];

        return view('app.staff.roles', compact('roles', 'permissions'));
    }
}
