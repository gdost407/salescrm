<?php

use App\Models\Company;
use App\Models\LeadSetting;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

function leadSettingsUser(Company $company): User
{
    return User::factory()->for($company)->create();
}

test('company users see system and own settings only', function () {
    $company = Company::create(['name' => 'Acme', 'slug' => 'acme']);
    $otherCompany = Company::create(['name' => 'Other', 'slug' => 'other']);
    $user = leadSettingsUser($company);

    LeadSetting::create(['setting_type' => 'stage', 'name' => 'System stage', 'type' => 'system']);
    LeadSetting::create(['company_id' => $company->id, 'setting_type' => 'stage', 'name' => 'Own stage', 'type' => 'manual']);
    LeadSetting::create(['company_id' => $otherCompany->id, 'setting_type' => 'stage', 'name' => 'Other stage', 'type' => 'manual']);

    $this->actingAs($user)->get(route('sales-lead-settings'))
        ->assertSuccessful()
        ->assertSee('System stage')
        ->assertSee('Own stage')
        ->assertDontSee('Other stage');
});

test('company users can create a unique custom setting', function () {
    $company = Company::create(['name' => 'Acme', 'slug' => 'acme']);
    $user = leadSettingsUser($company);

    $this->actingAs($user)->post(route('sales-lead-settings.store'), [
        'setting_type' => 'source',
        'name' => 'Partner',
    ])->assertRedirect(route('sales-lead-settings'));

    $this->assertDatabaseHas('lead_settings', [
        'company_id' => $company->id,
        'setting_type' => 'source',
        'name' => 'Partner',
        'type' => 'manual',
    ]);
});

test('a company-less user is assigned a company when creating a setting', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('sales-lead-settings.store'), [
        'setting_type' => 'stage',
        'name' => 'First stage',
    ])->assertRedirect(route('sales-lead-settings'));

    expect($user->fresh()->company_id)->not->toBeNull();
    $this->assertDatabaseHas('lead_settings', [
        'company_id' => $user->fresh()->company_id,
        'name' => 'First stage',
        'type' => 'manual',
    ]);
});

test('a custom setting cannot duplicate a system setting for the same type', function () {
    $company = Company::create(['name' => 'Acme', 'slug' => 'acme']);
    $user = leadSettingsUser($company);
    LeadSetting::create(['setting_type' => 'status', 'name' => 'Open', 'type' => 'system']);

    $this->actingAs($user)->from(route('sales-lead-settings'))
        ->post(route('sales-lead-settings.store'), ['setting_type' => 'status', 'name' => 'Open'])
        ->assertRedirect(route('sales-lead-settings'))
        ->assertSessionHasErrors('name');
});

test('system settings cannot be edited or deleted', function () {
    $company = Company::create(['name' => 'Acme', 'slug' => 'acme']);
    $user = leadSettingsUser($company);
    $systemSetting = LeadSetting::create(['setting_type' => 'stage', 'name' => 'System stage', 'type' => 'system']);

    $this->actingAs($user)->put(route('sales-lead-settings.update', $systemSetting), [
        'setting_type' => 'stage',
        'name' => 'Changed',
    ])->assertNotFound();

    $this->actingAs($user)->delete(route('sales-lead-settings.destroy', $systemSetting))
        ->assertNotFound();

    $this->assertDatabaseHas('lead_settings', ['id' => $systemSetting->id, 'name' => 'System stage']);
});
