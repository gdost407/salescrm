<?php

use App\Http\Middleware\EnsureCompanyOnboardingComplete;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

function roleAccessStaff(Company $company, array $permissions): User
{
    $role = Role::create([
        'company_id' => $company->id,
        'name' => 'Custom staff role',
        'slug' => fake()->uuid(),
        'status' => true,
    ]);
    $role->permissions()->sync(Permission::where('company_id', $company->id)->whereIn('slug', $permissions)->pluck('id'));

    return User::factory()->for($company)->create(['user_type' => 'staff', 'role_id' => $role->id]);
}

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

    $role = Role::query()->where('company_id', $this->company->id)->where('slug', 'sales-staff-1')->firstOrFail();

    expect($role->name)->toBe('Sales Staff')
        ->and($role->permissions()->pluck('slug')->sort()->values()->all())
        ->toBe(['create_leads', 'edit_own_leads', 'export_leads', 'view_leads']);
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

test('different companies can initialize identical default roles and permissions', function () {
    $this->actingAs($this->owner)->get(route('staff-roles'))->assertSuccessful();
    $otherCompany = Company::factory()->create();
    $otherOwner = User::factory()->for($otherCompany)->create(['user_type' => 'owner']);

    $this->actingAs($otherOwner)->get(route('staff-roles'))->assertSuccessful();

    expect(Role::where('slug', 'admin')->count())->toBe(2)
        ->and(Permission::where('slug', 'view_leads')->count())->toBe(2);

    foreach ([$this->company, $otherCompany] as $company) {
        $admin = Role::where('company_id', $company->id)->where('slug', 'admin')->firstOrFail();
        expect($admin->permissions()->where('permissions.company_id', '!=', $company->id)->exists())->toBeFalse()
            ->and($admin->permissions()->count())->toBe(15);
    }
});

test('editing an existing role keeps its identity and saves exact permissions across page loads', function () {
    $this->actingAs($this->owner)->get(route('staff-roles'))->assertSuccessful();
    $role = Role::where('company_id', $this->company->id)->where('slug', 'sales-staff')->firstOrFail();
    $staff = User::factory()->for($this->company)->create(['user_type' => 'staff', 'role_id' => $role->id]);

    $this->get(route('staff.roles.edit', $role))
        ->assertSuccessful()
        ->assertSee('Update Role')
        ->assertSee(route('staff.roles.update', $role), false);

    $this->put(route('staff.roles.update', $role), [
        'name' => 'Read Only Sales',
        'description' => 'View only',
        'permissions' => ['view_leads'],
    ])->assertSessionHasNoErrors()->assertRedirect(route('staff-roles'));

    $this->get(route('staff-roles'))->assertSuccessful();
    $this->get(route('staff.edit', $staff))->assertSuccessful()->assertSee('Read Only Sales');

    expect($role->fresh()->name)->toBe('Read Only Sales')
        ->and($staff->fresh()->role_id)->toBe($role->id)
        ->and($role->permissions()->pluck('slug')->all())->toBe(['view_leads']);

    $this->put(route('staff.roles.update', $role), ['name' => 'No Access'])
        ->assertSessionHasNoErrors()->assertRedirect(route('staff-roles'));
    $this->get(route('staff-roles'))->assertSuccessful();
    expect($role->permissions()->count())->toBe(0);
});

test('owners cannot edit another company role or select its custom permissions', function () {
    $otherCompany = Company::factory()->create();
    $role = Role::create(['company_id' => $otherCompany->id, 'name' => 'Other', 'slug' => 'other']);
    Permission::create(['company_id' => $otherCompany->id, 'name' => 'Private', 'slug' => 'private']);

    $this->actingAs($this->owner)->get(route('staff.roles.edit', $role))->assertNotFound();
    $this->put(route('staff.roles.update', $role), ['name' => 'Changed'])->assertNotFound();
    $this->post(route('staff.roles.store'), ['name' => 'Invalid', 'permissions' => ['private']])
        ->assertInvalid(['permissions.0']);

    expect($role->fresh()->name)->toBe('Other');
});

test('staff cannot grant themselves permissions through role management', function () {
    $staff = User::factory()->for($this->company)->create(['user_type' => 'staff']);

    $this->actingAs($staff)->get(route('staff-roles'))->assertForbidden();
    $this->post(route('staff.roles.store'), ['name' => 'Admin', 'permissions' => ['admin_access']])->assertForbidden();
});

test('inactive and foreign roles or permissions do not grant access', function () {
    $this->actingAs($this->owner)->get(route('staff-roles'))->assertSuccessful();
    $role = Role::where('company_id', $this->company->id)->where('slug', 'admin')->firstOrFail();
    $staff = User::factory()->for($this->company)->create(['user_type' => 'staff', 'role_id' => $role->id]);

    $this->actingAs($staff)->get(route('staff-roles'))->assertSuccessful();
    $role->update(['status' => false]);
    expect($staff->fresh()->hasPermission('admin_access'))->toBeFalse();

    $role->update(['status' => true]);
    Permission::where('company_id', $this->company->id)->where('slug', 'admin_access')->update(['status' => false]);
    expect($staff->fresh()->hasPermission('admin_access'))->toBeFalse();

    $otherCompany = Company::factory()->create();
    $staff->update(['company_id' => $otherCompany->id]);
    expect($staff->fresh()->hasPermission('view_leads'))->toBeFalse();
});

test('role forms group lead and staff permissions and preserve selections', function () {
    $this->actingAs($this->owner)->get(route('staff-roles'))
        ->assertSuccessful()
        ->assertSeeInOrder(['Lead permissions', 'Staff permissions', 'Reports', 'Administration'])
        ->assertSee('value="view_staff"', false)
        ->assertSee('value="create_staff"', false)
        ->assertSee('value="edit_staff"', false)
        ->assertSee('value="resend_staff_password"', false)
        ->assertSee('value="manage_roles"', false);

    $this->post(route('staff.roles.store'), [
        'name' => 'Lead and Staff Reader',
        'permissions' => ['view_leads', 'view_staff'],
    ])->assertSessionHasNoErrors();
    $role = Role::where('company_id', $this->company->id)->where('slug', 'lead-and-staff-reader')->firstOrFail();
    $response = $this->get(route('staff.roles.edit', $role))->assertSuccessful();
    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);

    expect($xpath->query('//fieldset[legend="Staff permissions"]//input[@value="view_staff" and @checked]')->length)->toBe(1)
        ->and($xpath->query('//fieldset[legend="Lead permissions"]//input[@value="view_leads" and @checked]')->length)->toBe(1)
        ->and($xpath->query('//input[@value="edit_staff" and @checked]')->length)->toBe(0);
});

test('staff routes enforce each assigned action independently', function (string $permission) {
    Queue::fake();
    $this->actingAs($this->owner)->get(route('staff-roles'))->assertSuccessful();
    $staff = roleAccessStaff($this->company, [$permission]);
    $target = User::factory()->for($this->company)->create(['user_type' => 'staff']);
    $this->actingAs($staff);

    foreach ([
        'view_staff' => ['get', route('staff-manage')],
        'create_staff' => ['get', route('staff-create')],
        'edit_staff' => ['get', route('staff.edit', $target)],
        'resend_staff_password' => ['post', route('staff.resend-password', $target)],
        'manage_roles' => ['get', route('staff-roles')],
    ] as $required => [$method, $url]) {
        $response = $this->{$method}($url);
        if ($required !== $permission) {
            $response->assertForbidden();
        } elseif ($method === 'post') {
            $response->assertRedirect();
        } else {
            $response->assertSuccessful();
        }
    }
})->with(['view_staff', 'create_staff', 'edit_staff', 'resend_staff_password', 'manage_roles']);

test('legacy manage team grants staff actions without role management', function () {
    $this->actingAs($this->owner)->get(route('staff-roles'))->assertSuccessful();
    $staff = roleAccessStaff($this->company, ['manage_team']);

    foreach (['view_staff', 'create_staff', 'edit_staff', 'resend_staff_password'] as $permission) {
        expect($staff->hasPermission($permission))->toBeTrue();
    }
    expect($staff->hasPermission('manage_roles'))->toBeFalse();
});

test('staff action buttons and navigation reflect read only access', function () {
    $this->actingAs($this->owner)->get(route('staff-roles'))->assertSuccessful();
    $staff = roleAccessStaff($this->company, ['view_staff']);
    $target = User::factory()->for($this->company)->create(['user_type' => 'staff']);

    $this->actingAs($staff)->get(route('staff-manage'))->assertSuccessful()
        ->assertDontSee(route('staff.edit', $target), false)
        ->assertDontSee(route('staff.resend-password', $target), false)
        ->assertDontSee(route('staff-create'), false)
        ->assertDontSee(route('staff-roles'), false);
    $this->post(route('staff.store'), [])->assertForbidden();
    $this->put(route('staff.update', $target), [])->assertForbidden();
});

test('staff editors cannot assign stronger roles or modify administrator accounts', function () {
    Queue::fake();
    $this->actingAs($this->owner)->get(route('staff-roles'))->assertSuccessful();
    $staff = roleAccessStaff($this->company, ['manage_team']);
    $admin = roleAccessStaff($this->company, ['admin_access']);
    $target = User::factory()->for($this->company)->create(['user_type' => 'staff']);
    $payload = [
        'name' => 'Updated staff', 'email' => $target->email, 'department' => 'Sales',
        'job_role' => 'Employee', 'is_active' => true,
    ];
    $this->actingAs($staff)->put(route('staff.update', $target), $payload)->assertSessionHasNoErrors()->assertRedirect();
    $this->put(route('staff.update', $target), [...$payload, 'role_id' => $admin->role_id])->assertForbidden();
    $this->post(route('staff.store'), [...$payload, 'email' => 'new-staff@example.com', 'role_id' => $admin->role_id])->assertForbidden();
    $this->get(route('staff.edit', $admin))->assertForbidden();
    $this->post(route('staff.resend-password', $admin))->assertForbidden();
    $this->get(route('staff.edit', $this->owner))->assertForbidden();
    expect($target->fresh()->role_id)->toBeNull();
    Queue::assertNothingPushed();
});
