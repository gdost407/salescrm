<?php

use App\Models\Client;
use App\Models\Company;
use App\Models\Lead;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
});

test('dashboard counts only company records and excludes deleted leads', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $owner = User::factory()->for($company)->create(['user_type' => 'owner']);
    Lead::factory()->count(2)->create(['company_id' => $company->id, 'status' => 'New']);
    Lead::factory()->create(['company_id' => $company->id, 'status' => 'Converted']);
    Lead::factory()->create(['company_id' => $company->id, 'status' => 'Qualified']);
    Lead::factory()->create(['company_id' => $company->id, 'status' => 'New'])->delete();
    $foreign = Lead::factory()->create(['name' => 'Private foreign lead']);
    Client::factory()->count(2)->for($company)->create();
    Client::factory()->create();
    User::factory()->for($company)->create(['user_type' => 'staff']);
    User::factory()->for($company)->create(['user_type' => 'staff', 'is_active' => false]);
    $response = $this->actingAs($owner)->get(route('dashboard'))->assertSuccessful()->assertDontSee($foreign->name);
    $counts = collect($response['cards'])->pluck('count', 'label')->all();
    expect($counts)->toBe(['Total leads' => 4, 'New leads' => 2, 'Converted leads' => 1, 'Clients' => 2, 'Staff' => 2])
        ->and($response['conversionRate'])->toEqual(25)
        ->and($response['recentLeads'])->toHaveCount(4);
});

test('dashboard limits staff to assigned leads and hides unauthorized metrics', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    app(RolePermissionSeeder::class)->seedCompany($company->id);
    $role = Role::create(['company_id' => $company->id, 'name' => 'Dashboard reader', 'slug' => 'dashboard-reader', 'status' => true]);
    $role->permissions()->sync(Permission::where('company_id', $company->id)->where('slug', 'view_own_leads')->pluck('id'));
    $staff = User::factory()->for($company)->create(['user_type' => 'staff', 'role_id' => $role->id]);
    $own = Lead::factory()->create(['company_id' => $company->id, 'assigned_to' => $staff->id, 'status' => 'New']);
    $hidden = Lead::factory()->create(['company_id' => $company->id, 'name' => 'Unassigned private lead', 'status' => 'Converted']);
    $response = $this->actingAs($staff)->get(route('dashboard'))->assertSuccessful()->assertSee($own->name)->assertDontSee($hidden->name);
    expect(collect($response['cards'])->pluck('count', 'label')->all())->toBe(['Total leads' => 1, 'New leads' => 1, 'Converted leads' => 0]);
    $role->permissions()->sync(Permission::where('company_id', $company->id)->where('slug', 'view_all_leads')->pluck('id'));
    $response = $this->actingAs($staff->fresh())->get(route('dashboard'))->assertSuccessful()->assertSee($hidden->name);
    expect($response['recentLeads'])->toHaveCount(2);
});

test('dashboard handles empty companies and users without permissions', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $owner = User::factory()->for($company)->create(['user_type' => 'owner']);
    $this->actingAs($owner)->get(route('dashboard'))->assertSuccessful()->assertSee('No leads available.')
        ->assertViewHas('conversionRate', 0);
    $staff = User::factory()->for($company)->create(['user_type' => 'staff', 'role_id' => null]);
    $this->actingAs($staff)->get(route('dashboard'))->assertSuccessful()
        ->assertViewHas('cards', [])->assertViewHas('canViewLeads', false)->assertSee('No dashboard metrics are available');
});

test('recent leads are limited to the six newest visible records', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $owner = User::factory()->for($company)->create(['user_type' => 'owner']);
    $old = Lead::factory()->create(['company_id' => $company->id, 'created_at' => now()->subMonth()]);
    Lead::factory()->count(7)->create(['company_id' => $company->id]);
    $response = $this->actingAs($owner)->get(route('dashboard'))->assertSuccessful();
    expect($response['recentLeads'])->toHaveCount(6)
        ->and($response['recentLeads']->modelKeys())->not->toContain($old->id);
});
