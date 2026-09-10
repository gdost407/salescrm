<?php

namespace App\Http\Controllers\Web\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Jobs\SendStaffCredentialsEmail;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StaffController extends Controller
{
    /**
     * Show create staff form.
     */
    public function create(Request $request): View
    {
        return view('app.staff.create', [
            'roles' => Role::query()
                ->where('company_id', $request->user()->company_id)
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Store a new staff member.
     */
    public function store(StoreStaffRequest $request): RedirectResponse
    {
        $temporaryPassword = Str::random(16);

        $staff = DB::transaction(function () use ($request, $temporaryPassword): User {
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

            return User::create([
                ...$request->validated(),
                'company_id' => $company->id,
                'user_type' => 'staff',
                'password' => $temporaryPassword,
            ]);
        });

        SendStaffCredentialsEmail::dispatch($staff, $temporaryPassword);

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
     * Show edit staff form.
     */
    public function edit(Request $request, User $staffUser): View
    {
        $currentUser = $request->user();
        if ($currentUser && $staffUser->company_id !== $currentUser->company_id) {
            abort(404);
        }

        return view('app.staff.edit', [
            'staff' => $staffUser,
            'roles' => Role::query()
                ->where('company_id', $currentUser->company_id)
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Update staff member details.
     */
    public function update(UpdateStaffRequest $request, User $staffUser): RedirectResponse
    {
        $currentUser = $request->user();
        if ($currentUser && $staffUser->company_id !== $currentUser->company_id) {
            abort(404);
        }

        $staffUser->update($request->validated());

        return redirect()->route('staff-manage')->with('message', 'Staff member updated successfully!');
    }

    /**
     * Resend password credentials email to staff user.
     */
    public function resendPassword(Request $request, User $staffUser): RedirectResponse
    {
        $currentUser = $request->user();
        if ($currentUser && $staffUser->company_id !== $currentUser->company_id) {
            abort(404);
        }

        $temporaryPassword = Str::random(16);
        $staffUser->update(['password' => $temporaryPassword]);

        SendStaffCredentialsEmail::dispatch($staffUser, $temporaryPassword);

        return redirect()->route('staff-manage')->with('message', 'Password email sent to '.$staffUser->email.' successfully!');
    }

    /**
     * Show roles and permissions management.
     */
    public function roles(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $this->ensureDefaultPermissions($companyId);

        $roles = Role::query()
            ->where('company_id', $companyId)
            ->with('permissions:id,slug,name,module')
            ->orderBy('name')
            ->get();

        $permissions = Permission::query()
            ->where('company_id', $companyId)
            ->orderBy('module')
            ->orderBy('name')
            ->get();

        return view('app.staff.roles', compact('roles', 'permissions'));
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $companyId = $request->user()->company_id;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'max:120'],
        ]);

        $this->ensureDefaultPermissions($companyId);

        $slug = $validated['slug'] ?? Str::slug($validated['name']);
        $slug = $this->uniqueRoleSlug($companyId, $slug);

        $role = Role::query()->firstOrCreate(
            ['company_id' => $companyId, 'slug' => $slug],
            [
                'company_id' => $companyId,
                'name' => $validated['name'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'status' => true,
            ]
        );

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $selectedPermissions = Permission::query()
            ->where('company_id', $companyId)
            ->whereIn('slug', $validated['permissions'] ?? [])
            ->pluck('id');

        $role->permissions()->sync($selectedPermissions);

        return redirect()->route('staff-roles')->with('message', 'Role created successfully.');
    }

    private function uniqueRoleSlug(int $companyId, string $baseSlug): string
    {
        $slug = Str::slug($baseSlug) ?: 'role';
        $candidate = $slug;
        $counter = 1;

        while (Role::query()->where('company_id', $companyId)->where('slug', $candidate)->exists()) {
            $candidate = $slug.'-'.$counter;
            $counter++;
        }

        return $candidate;
    }

    private function ensureDefaultPermissions(int $companyId): void
    {
        $defaultPermissions = [
            ['name' => 'View Leads', 'slug' => 'view_leads', 'module' => 'lead'],
            ['name' => 'Create Leads', 'slug' => 'create_leads', 'module' => 'lead'],
            ['name' => 'Edit Own Leads', 'slug' => 'edit_own_leads', 'module' => 'lead'],
            ['name' => 'Edit All Leads', 'slug' => 'edit_all_leads', 'module' => 'lead'],
            ['name' => 'Delete Leads', 'slug' => 'delete_leads', 'module' => 'lead'],
            ['name' => 'Export Leads', 'slug' => 'export_leads', 'module' => 'lead'],
            ['name' => 'Print Leads', 'slug' => 'print_leads', 'module' => 'lead'],
            ['name' => 'View Reports', 'slug' => 'view_reports', 'module' => 'report'],
            ['name' => 'Manage Team', 'slug' => 'manage_team', 'module' => 'staff'],
            ['name' => 'Admin Access', 'slug' => 'admin_access', 'module' => 'admin'],
        ];

        foreach ($defaultPermissions as $permission) {
            Permission::query()->firstOrCreate(
                ['company_id' => $companyId, 'slug' => $permission['slug']],
                [
                    'company_id' => $companyId,
                    'name' => $permission['name'],
                    'slug' => $permission['slug'],
                    'module' => $permission['module'],
                    'description' => $permission['name'],
                    'status' => true,
                ]
            );
        }

        Role::query()->firstOrCreate(
            ['company_id' => $companyId, 'slug' => 'admin'],
            [
                'company_id' => $companyId,
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Full system access',
                'status' => true,
            ]
        );

        Role::query()->firstOrCreate(
            ['company_id' => $companyId, 'slug' => 'sales-staff'],
            [
                'company_id' => $companyId,
                'name' => 'Sales Staff',
                'slug' => 'sales-staff',
                'description' => 'Can manage assigned leads',
                'status' => true,
            ]
        );

        $adminRole = Role::query()->where('company_id', $companyId)->where('slug', 'admin')->first();
        $salesStaff = Role::query()->where('company_id', $companyId)->where('slug', 'sales-staff')->first();

        if ($adminRole) {
            $adminRole->permissions()->sync(Permission::query()->where('company_id', $companyId)->pluck('id'));
        }

        if ($salesStaff) {
            $salesStaff->permissions()->sync(
                Permission::query()->where('company_id', $companyId)->whereIn('slug', ['view_leads', 'create_leads', 'edit_own_leads', 'export_leads', 'print_leads'])->pluck('id')
            );
        }
    }
}

