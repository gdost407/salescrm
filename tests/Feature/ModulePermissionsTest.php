<?php

use App\Models\Company;
use App\Models\Lead;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

test('saved lead roles open the lead pages and enforce visibility', function (string $permission) {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $owner = User::factory()->for($company)->create(['user_type' => 'owner']);
    $this->actingAs($owner)->get(route('staff-roles'))
        ->assertSuccessful()->assertSee('value="view_all_leads"', false);
    $this->post(route('staff.roles.store'), [
        'name' => 'Lead Reader', 'permissions' => [$permission],
    ])->assertSessionHasNoErrors()->assertRedirect();
    $role = Role::where('company_id', $company->id)->where('slug', 'lead-reader')->firstOrFail();
    $staff = User::factory()->for($company)->create(['user_type' => 'staff', 'role_id' => $role->id]);
    $assigned = Lead::factory()->for($company)->create(['assigned_to' => $staff->id]);
    $unassigned = Lead::factory()->for($company)->create();
    $foreign = Lead::factory()->create();

    $this->actingAs($staff);
    foreach (['sale-kanban', 'calendar', 'sales-all-list', 'calendar.events', 'sales-leads.kanban-data'] as $route) {
        $this->get(route($route, $route === 'sales-leads.kanban-data' ? ['status' => 'Open'] : []))->assertSuccessful();
    }
    expect(Lead::visibleTo($staff)->pluck('id')->all())
        ->toEqual($permission === 'view_all_leads' ? [$assigned->id, $unassigned->id] : [$assigned->id]);
    $this->get(route('sales-lead-view', $foreign))->assertNotFound();
    expect($staff->hasPermission('edit_all_leads'))->toBeFalse();

    $this->actingAs($owner)->put(route('staff.roles.update', $role), [
        'name' => 'No access', 'permissions' => [],
    ])->assertSessionHasNoErrors()->assertRedirect();
    $this->actingAs($staff->fresh());
    foreach (['sale-kanban', 'calendar', 'sales-all-list'] as $route) {
        $this->get(route($route))->assertRedirect(route('dashboard'))->assertSessionHas('access_error');
    }
})->with(['view_own_leads', 'view_all_leads']);

test('module lead access rejects inactive and foreign permissions and roles', function () {
    $company = Company::factory()->create();
    $role = Role::create(['company_id' => $company->id, 'name' => 'Reader', 'slug' => 'reader', 'status' => true]);
    $permission = Permission::create(['company_id' => $company->id, 'name' => 'View Leads', 'slug' => 'view_own_leads', 'module' => 'lead', 'status' => true]);
    $role->permissions()->attach($permission);
    $staff = User::factory()->for($company)->create(['user_type' => 'staff', 'role_id' => $role->id]);
    expect($staff->hasPermission('view_own_leads'))->toBeTrue();
    $permission->update(['status' => false]);
    expect($staff->fresh()->hasPermission('view_own_leads'))->toBeFalse();
    $permission->update(['status' => true, 'company_id' => Company::factory()->create()->id]);
    expect($staff->fresh()->hasPermission('view_own_leads'))->toBeFalse();
    $permission->update(['company_id' => $company->id]);
    $role->update(['status' => false]);
    expect($staff->fresh()->hasPermission('view_own_leads'))->toBeFalse();
    $role->update(['status' => true, 'company_id' => $permission->company_id + 1]);
    expect($staff->fresh()->hasPermission('view_own_leads'))->toBeFalse();
});

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('module catalog keeps lead scopes and staff CRUD without administration', function () {
    expect(array_values(Permission::MODULES['lead']))->toBe(['Create', 'View self', 'Edit self', 'Delete self', 'View all', 'Edit all', 'Delete all'])
        ->and(array_values(Permission::MODULES['staff']))->toBe(['Create', 'View', 'Edit', 'Delete'])
        ->and(Permission::MODULES['lead_activity'])->toHaveCount(12);
    $owner = User::factory()->for(Company::factory())->create(['user_type' => 'owner']);
    expect($owner->hasPermission('admin_access'))->toBeFalse()
        ->and($owner->hasPermission('manage_roles'))->toBeFalse();
});

test('staff CRUD visibility and deletion stay within the company', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $role = Role::create(['company_id' => $company->id, 'name' => 'Staff access', 'slug' => 'staff-access', 'status' => true]);
    foreach (['view', 'delete'] as $action) {
        $permission = Permission::create(['company_id' => $company->id, 'name' => $action, 'slug' => $action.'_staff', 'module' => 'staff', 'status' => true]);
        $role->permissions()->attach($permission);
    }
    $staff = User::factory()->for($company)->create(['user_type' => 'staff', 'role_id' => $role->id]);
    $other = User::factory()->for($company)->create(['user_type' => 'staff']);
    $foreign = User::factory()->for(Company::factory())->create(['user_type' => 'staff']);
    $this->actingAs($staff)->get(route('staff-manage'))->assertSuccessful()
        ->assertViewHas('staffMembers', fn ($rows) => $rows->pluck('id')->sort()->values()->all() === [$staff->id, $other->id]);
    $this->delete(route('staff.destroy', $foreign))->assertNotFound();
    $this->delete(route('staff.destroy', $other))->assertRedirect();
    $this->assertModelMissing($other);
    $this->delete(route('staff.destroy', $staff))->assertRedirect(route('login'));
    $this->assertGuest();
});

test('lead record actions respect assigned and company boundaries independently', function (string $action, string $scope) {
    $company = Company::factory()->create();
    $role = Role::create(['company_id' => $company->id, 'name' => 'Lead access', 'slug' => 'lead-access', 'status' => true]);
    $permission = Permission::create(['company_id' => $company->id, 'name' => $action, 'slug' => $action.'_'.$scope.'_leads', 'module' => 'lead', 'status' => true]);
    $role->permissions()->attach($permission);
    $staff = User::factory()->for($company)->create(['user_type' => 'staff', 'role_id' => $role->id]);
    $own = Lead::factory()->for($company)->create(['assigned_to' => $staff->id]);
    $other = Lead::factory()->for($company)->create();
    $foreign = Lead::factory()->create(['assigned_to' => $staff->id]);
    expect($staff->canAccessLead($own, $action))->toBeTrue()
        ->and($staff->canAccessLead($other, $action))->toBe($scope === 'all')
        ->and($staff->canAccessLead($foreign, $action))->toBeFalse()
        ->and($staff->hasPermission('create_leads'))->toBeFalse();
})->with(['view', 'edit', 'delete'])->with(['own', 'all']);

test('permission seeder converts legacy access once within its company', function () {
    $company = Company::factory()->create();
    $role = Role::create(['company_id' => $company->id, 'name' => 'Legacy', 'slug' => 'legacy', 'status' => true]);
    $legacy = Permission::create(['company_id' => $company->id, 'name' => 'View Leads', 'slug' => 'view_leads', 'module' => 'lead', 'status' => true]);
    $role->permissions()->attach($legacy);
    $foreignRole = Role::create(['company_id' => Company::factory()->create()->id, 'name' => 'Foreign', 'slug' => 'foreign', 'status' => true]);
    $foreignRole->permissions()->attach($legacy);
    $this->seed(RolePermissionSeeder::class);
    $this->seed(RolePermissionSeeder::class);
    expect($role->permissions()->where('slug', 'view_own_leads')->count())->toBe(1)
        ->and($foreignRole->permissions()->where('slug', 'view_own_leads')->exists())->toBeFalse();
    $role->permissions()->detach();
    $this->seed(RolePermissionSeeder::class);
    expect($role->permissions()->count())->toBe(0);
});
