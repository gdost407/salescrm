<?php

use App\Http\Middleware\EnsureCompanyOnboardingComplete;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    $this->withoutMiddleware(EnsureCompanyOnboardingComplete::class);

    $this->company = Company::create([
        'name' => 'Acme CRM',
        'slug' => 'acme-crm',
        'staff_limit' => 10,
    ]);

    $this->owner = User::factory()->create([
        'company_id' => $this->company->id,
        'user_type' => 'owner',
    ]);
});

test('company owner can create a saved role with a slug and permissions', function () {
    $response = $this->actingAs($this->owner)->post(route('staff.roles.store', absolute: false), [
        'name' => 'Sales Staff',
        'slug' => 'sales-staff',
        'description' => 'Can manage assigned leads',
        'permissions' => ['view_leads', 'create_leads', 'edit_own_leads', 'export_leads'],
    ]);

    $response->assertRedirect(route('staff-roles', absolute: false));

    $role = Role::query()->where('company_id', $this->company->id)->where('slug', 'sales-staff')->firstOrFail();

    expect($role->name)->toBe('Sales Staff')
        ->and($role->permissions()->pluck('slug')->sort()->values()->all())
            ->toBe(['create_leads', 'edit_own_leads', 'export_leads', 'print_leads', 'view_leads']);
});

test('staff can be assigned a saved role and the role grants access to the configured section', function () {
    $role = Role::query()->create([
        'company_id' => $this->company->id,
        'name' => 'Lead Staff',
        'slug' => 'lead-staff',
        'description' => 'Lead access',
        'status' => true,
    ]);

    $permission = Permission::query()->create([
        'company_id' => $this->company->id,
        'name' => 'Create Leads',
        'slug' => 'create_leads',
        'module' => 'lead',
        'description' => 'Creates leads',
        'status' => true,
    ]);

    $role->permissions()->sync([$permission->id]);

    $staff = User::factory()->create([
        'company_id' => $this->company->id,
        'user_type' => 'staff',
        'role_id' => $role->id,
    ]);

    expect($staff->role_id)->toBe($role->id)
        ->and($staff->hasPermission('create_leads'))->toBeTrue()
        ->and($staff->hasPermission('edit_all_leads'))->toBeFalse();
});
