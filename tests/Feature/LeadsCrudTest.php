<?php

use App\Models\City;
use App\Models\Company;
use App\Models\Country;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadSetting;
use App\Models\Permission;
use App\Models\Role;
use App\Models\State;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

function leadCrudPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Jane Lead',
        'email' => 'jane@example.com',
        'mobile' => '1234567890',
        'job_title' => 'Buyer',
        'deal_amount' => '2500.50',
        'stage' => 'Qualification',
        'status' => 'Open',
        'source' => 'Referral',
        'address' => '1 Main Street',
        'country' => 'India',
        'state' => 'Maharashtra',
        'city' => 'Pulgaon',
        'pincode' => '442302',
        'description' => 'Interested in the service',
    ], $overrides);
}

function leadCrudUser(Company $company, array $permissions = []): User
{
    $user = User::factory()->for($company)->create(['is_active' => true]);
    if ($permissions !== []) {
        app(RolePermissionSeeder::class)->seedCompany($company->id);
        $role = Role::create(['company_id' => $company->id, 'name' => 'Test permissions', 'slug' => 'test-permissions', 'status' => true]);
        $role->permissions()->sync(Permission::where('company_id', $company->id)->whereIn('slug', $permissions)->pluck('id'));
        $user->update(['role_id' => $role->id]);
    }

    return $user;
}

test('kanban status columns keep system defaults first, custom statuses next, and trailing system statuses last', function () {
    $company = Company::create(['name' => 'Acme', 'slug' => 'acme', 'onboarding_completed_at' => now()]);
    $user = User::factory()->for($company)->create(['user_type' => 'owner', 'is_active' => true, 'company_id' => $company->id]);

    LeadSetting::insert([
        ['setting_type' => 'status', 'name' => 'New', 'type' => 'system', 'is_active' => true, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'status', 'name' => 'Open', 'type' => 'system', 'is_active' => true, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'status', 'name' => 'In Progress', 'type' => 'system', 'is_active' => true, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'status', 'name' => 'Follow Up', 'type' => 'system', 'is_active' => true, 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'status', 'name' => 'Prospect Review', 'type' => 'manual', 'is_active' => true, 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'status', 'name' => 'Hot Lead', 'type' => 'manual', 'is_active' => true, 'sort_order' => 6, 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'status', 'name' => 'Converted', 'type' => 'system', 'is_active' => true, 'sort_order' => 7, 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'status', 'name' => 'Lost', 'type' => 'system', 'is_active' => true, 'sort_order' => 8, 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'status', 'name' => 'Cancelled', 'type' => 'system', 'is_active' => true, 'sort_order' => 9, 'created_at' => now(), 'updated_at' => now()],
    ]);

    $this->actingAs($user)
        ->get(route('sale-kanban'))
        ->assertSuccessful()
        ->assertSeeInOrder(['New', 'Open', 'In Progress', 'Follow Up', 'Prospect Review', 'Hot Lead', 'Converted', 'Lost', 'Cancelled']);
});

test('lead form uses active company settings and users', function () {
    $company = Company::create(['name' => 'Acme', 'slug' => 'acme']);
    $user = leadCrudUser($company);
    LeadSetting::create(['setting_type' => 'stage', 'name' => 'Qualification', 'type' => 'system']);
    LeadSetting::create(['setting_type' => 'status', 'name' => 'Open', 'type' => 'system']);
    LeadSetting::create(['setting_type' => 'source', 'name' => 'Referral', 'type' => 'system']);

    $this->actingAs($user)->get(route('sales-create-lead'))
        ->assertSuccessful()
        ->assertSee('Qualification')
        ->assertSee('Open')
        ->assertSee('Referral')
        ->assertSee('name="stage"', false)
        ->assertSee('name="status"', false)
        ->assertSee('name="source"', false)
        ->assertSee('Import leads')
        ->assertSee('Download sample sheet')
        ->assertSee(route('sales-leads.import'), false)
        ->assertSee(route('sales-leads.import.sample'), false)
        ->assertSee($user->name);
});

test('location endpoints return states and cities for selected parents', function () {
    $company = Company::create(['name' => 'Acme', 'slug' => 'acme']);
    $user = leadCrudUser($company);
    $country = Country::create(['id' => 101, 'shortname' => 'IN', 'name' => 'India', 'phonecode' => 91]);
    $state = State::create(['id' => 22, 'name' => 'Maharashtra', 'country_id' => $country->id]);
    City::create(['id' => 1, 'name' => 'Pulgaon', 'state_id' => $state->id]);

    $this->actingAs($user)
        ->getJson(route('locations.countries'))
        ->assertSuccessful()
        ->assertJsonPath('data.0.name', 'India');

    $this->actingAs($user)
        ->getJson(route('locations.states', ['country' => 'India']))
        ->assertSuccessful()
        ->assertJsonPath('data.0.name', 'Maharashtra');

    $this->actingAs($user)
        ->getJson(route('locations.cities', ['country' => 'India', 'state' => 'Maharashtra']))
        ->assertSuccessful()
        ->assertJsonPath('data.0.name', 'Pulgaon');
});

test('a user can create update and delete a lead', function () {
    $company = Company::create(['name' => 'Acme', 'slug' => 'acme']);
    $user = leadCrudUser($company);
    LeadSetting::insert([
        ['setting_type' => 'stage', 'name' => 'New', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'stage', 'name' => 'Qualification', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'status', 'name' => 'New', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'status', 'name' => 'Open', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'source', 'name' => 'Self', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'source', 'name' => 'Referral', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
    ]);

    $this->actingAs($user)->post(route('sales-leads.store'), leadCrudPayload(['assigned_to' => $user->id]))
        ->assertRedirect(route('sales-all-list'));

    $lead = Lead::query()->firstOrFail();
    expect($lead->company_id)->toBe($company->id);
    $this->assertModelExists($lead);

    $this->actingAs($user)->put(route('sales-leads.update', $lead), leadCrudPayload([
        'name' => 'Updated Lead',
        'status' => 'Open',
        'assigned_to' => $user->id,
    ]))->assertRedirect(route('sales-all-list'));
    expect($lead->fresh()->name)->toBe('Updated Lead');

    $this->actingAs($user)->delete(route('sales-leads.destroy', $lead))
        ->assertRedirect(route('sales-all-list'));
    expect($lead->fresh()->trashed())->toBeTrue();
});

test('editing a lead preserves unlisted webhook locations when saving other fields', function () {
    $company = Company::factory()->create();
    $user = leadCrudUser($company);
    foreach (['stage' => 'New', 'status' => 'New', 'source' => 'Self'] as $type => $name) {
        LeadSetting::create(['setting_type' => $type, 'name' => $name, 'type' => 'system']);
    }

    $lead = Lead::factory()->for($company)->create([
        'created_by' => $user->id,
        'country' => 'IN',
        'state' => 'MH',
        'city' => 'Bombay',
    ]);

    $this->actingAs($user)->get(route('sales-leads.edit', $lead))
        ->assertSuccessful()
        ->assertSee('data-selected="MH"', false)
        ->assertSee('data-selected="Bombay"', false);

    $this->put(route('sales-leads.update', $lead), leadCrudPayload([
        'name' => 'Updated webhook lead',
        'stage' => 'New',
        'status' => 'New',
        'source' => 'Self',
        'country' => 'IN',
        'state' => 'MH',
        'city' => 'Bombay',
    ]))->assertSessionHasNoErrors()->assertRedirect(route('sales-all-list'));

    expect($lead->fresh())
        ->name->toBe('Updated webhook lead')
        ->country->toBe('IN')
        ->state->toBe('MH')
        ->city->toBe('Bombay');
});

test('a user cannot modify another company lead', function () {
    $company = Company::create(['name' => 'Acme', 'slug' => 'acme']);
    $otherCompany = Company::create(['name' => 'Other', 'slug' => 'other']);
    $user = leadCrudUser($company);
    $otherUser = leadCrudUser($otherCompany);
    $lead = Lead::create([
        'company_id' => $otherCompany->id,
        'created_by' => $otherUser->id,
        'name' => 'Private Lead',
    ]);

    $this->actingAs($user)->get(route('sales-leads.edit', $lead))->assertNotFound();
    $this->actingAs($user)->delete(route('sales-leads.destroy', $lead))->assertNotFound();
    $this->assertModelExists($lead);
});

test('lead list filters, paginates, and exports only the company leads', function () {
    $company = Company::create(['name' => 'Acme', 'slug' => 'acme']);
    $otherCompany = Company::create(['name' => 'Other', 'slug' => 'other']);
    $user = leadCrudUser($company);
    $assignee = leadCrudUser($company);
    LeadSetting::insert([
        ['setting_type' => 'stage', 'name' => 'New', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'stage', 'name' => 'Qualification', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'status', 'name' => 'New', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'status', 'name' => 'Open', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'source', 'name' => 'Self', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'source', 'name' => 'Referral', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
    ]);
    Lead::create([
        'company_id' => $company->id, 'created_by' => $user->id, 'assigned_to' => $assignee->id,
        'name' => 'Matching Lead', 'email' => 'match@example.com', 'stage' => 'Qualification',
        'status' => 'Open', 'source' => 'Referral', 'created_at' => now(), 'updated_at' => now(),
    ]);
    $oldLead = Lead::create([
        'company_id' => $company->id, 'created_by' => $user->id, 'name' => 'Other Lead',
        'stage' => 'Qualification', 'status' => 'Open', 'source' => 'Referral',
    ]);
    $oldLead->forceFill(['created_at' => now()->subMonths(6), 'updated_at' => now()->subMonths(6)])->saveQuietly();
    Lead::create([
        'company_id' => $otherCompany->id, 'name' => 'Private Matching Lead',
        'stage' => 'Qualification', 'status' => 'Open', 'source' => 'Referral',
    ]);

    $this->actingAs($user)->get(route('sales-all-list', [
        'search' => 'Matching', 'status' => 'Open', 'stage' => 'Qualification',
        'source' => 'Referral', 'assigned_to' => $assignee->id, 'date_range' => 'year',
    ]))->assertSuccessful()->assertSee('Matching Lead')->assertDontSee('Private Matching Lead')->assertDontSee('Other Lead');

    $this->actingAs($user)->get(route('sales-all-list', [
        'date_range' => 'custom', 'date_from' => now()->subDay()->toDateString(), 'date_to' => now()->toDateString(),
    ]))->assertSuccessful()->assertSee('Matching Lead')->assertDontSee('Other Lead');

    $this->actingAs($user)->getJson(route('sales-all-list', ['search' => 'Matching']))
        ->assertSuccessful()
        ->assertJsonPath('count', 1)
        ->assertJsonPath('html', fn ($html) => str_contains($html, 'Matching Lead'))
        ->assertJsonMissing(['html' => 'Private Matching Lead']);

    $exportResponse = $this->actingAs($user)->get(route('sales-leads.export', ['search' => 'Matching']))
        ->assertSuccessful()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    expect($exportResponse->streamedContent())->toContain('Matching Lead')
        ->not->toContain('Private Matching Lead');
});

test('staff users default to their own leads and cannot filter by other staff members', function () {
    $company = Company::create(['name' => 'Acme', 'slug' => 'acme', 'onboarding_completed_at' => now()]);
    $owner = User::factory()->for($company)->create(['user_type' => 'owner', 'is_active' => true, 'company_id' => $company->id]);
    $staff = User::factory()->for($company)->create(['user_type' => 'staff', 'is_active' => true, 'company_id' => $company->id]);
    $otherStaff = User::factory()->for($company)->create(['user_type' => 'staff', 'is_active' => true]);
    LeadSetting::insert([
        ['setting_type' => 'stage', 'name' => 'New', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'stage', 'name' => 'Qualification', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'status', 'name' => 'New', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'status', 'name' => 'Open', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'source', 'name' => 'Self', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'source', 'name' => 'Referral', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
    ]);

    Lead::create(['company_id' => $company->id, 'created_by' => $owner->id, 'assigned_to' => $staff->id, 'name' => 'Staff Lead', 'email' => 'staff@example.com', 'stage' => 'Qualification', 'status' => 'Open', 'source' => 'Referral']);
    Lead::create(['company_id' => $company->id, 'created_by' => $owner->id, 'assigned_to' => $otherStaff->id, 'name' => 'Other Staff Lead', 'email' => 'other-staff@example.com', 'stage' => 'Qualification', 'status' => 'Open', 'source' => 'Referral']);
    Lead::create(['company_id' => $company->id, 'created_by' => $owner->id, 'assigned_to' => null, 'name' => 'Unassigned Lead', 'email' => 'unassigned@example.com', 'stage' => 'Qualification', 'status' => 'Open', 'source' => 'Referral']);

    $this->actingAs($staff)->get(route('sales-all-list'))
        ->assertSuccessful()
        ->assertSee('Staff Lead')
        ->assertDontSee('Other Staff Lead')
        ->assertDontSee('Unassigned Lead')
        ->assertDontSee('name="assigned_to"');

    $this->actingAs($staff)->get(route('sale-kanban'))
        ->assertSuccessful()
        ->assertDontSee('name="assigned_to"');
});

test('owners can view all unassigned and assigned leads', function () {
    $company = Company::create(['name' => 'Acme', 'slug' => 'acme', 'onboarding_completed_at' => now()]);
    $owner = User::factory()->for($company)->create(['user_type' => 'owner', 'is_active' => true, 'company_id' => $company->id]);
    $staff = User::factory()->for($company)->create(['user_type' => 'staff', 'is_active' => true, 'company_id' => $company->id]);
    LeadSetting::insert([
        ['setting_type' => 'stage', 'name' => 'New', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'stage', 'name' => 'Qualification', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'status', 'name' => 'New', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'status', 'name' => 'Open', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'source', 'name' => 'Self', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'source', 'name' => 'Referral', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
    ]);

    Lead::create(['company_id' => $company->id, 'created_by' => $owner->id, 'assigned_to' => $staff->id, 'name' => 'Assigned Lead', 'email' => 'assigned@example.com', 'stage' => 'Qualification', 'status' => 'Open', 'source' => 'Referral']);
    Lead::create(['company_id' => $company->id, 'created_by' => $owner->id, 'assigned_to' => null, 'name' => 'Unassigned Lead', 'email' => 'unassigned-owner@example.com', 'stage' => 'Qualification', 'status' => 'Open', 'source' => 'Referral']);

    $this->actingAs($owner)->get(route('sales-all-list'))
        ->assertSuccessful()
        ->assertSee('Assigned Lead')
        ->assertSee('Unassigned Lead')
        ->assertSee('name="assigned_to"');
});

test('a user can import leads from a CSV sample format', function () {
    $company = Company::create(['name' => 'Acme', 'slug' => 'acme']);
    $user = leadCrudUser($company);
    LeadSetting::insert([
        ['setting_type' => 'stage', 'name' => 'New', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'stage', 'name' => 'Qualification', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'status', 'name' => 'New', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'status', 'name' => 'Open', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'source', 'name' => 'Self', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'source', 'name' => 'Referral', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
    ]);

    $csv = "name,email,mobile,job_title,deal_amount,stage,status,source,priority\nImported Lead,imported@example.com,1234567890,Buyer,1000,Qualification,Open,Referral,high\n";

    $this->actingAs($user)->post(route('sales-leads.import'), [
        'file' => UploadedFile::fake()->createWithContent('leads.csv', $csv),
    ])->assertViewIs('app.sales.import-result')
        ->assertViewHas('importedCount', 1)
        ->assertViewHas('skippedDuplicates', 0)
        ->assertViewHas('skippedInvalid', 0);

    $this->assertDatabaseHas('leads', [
        'company_id' => $company->id, 'name' => 'Imported Lead', 'email' => 'imported@example.com',
        'assigned_to' => $user->id, 'priority' => 'high',
    ]);

    $otherCompany = Company::create(['name' => 'Other Company', 'slug' => 'other-company']);
    Lead::create([
        'company_id' => $otherCompany->id, 'name' => 'Other Company Lead',
        'email' => 'shared@example.com', 'mobile' => '9999999999',
        'stage' => 'New', 'status' => 'New', 'source' => 'Self',
    ]);

    $duplicateCsv = "name,email,mobile\nDuplicate Existing,imported@example.com,1234567890\nOther Company Match,shared@example.com,9999999999\nUnique Imported,unique@example.com,1111111111\nDuplicate In File,unique@example.com,2222222222\n";

    $this->actingAs($user)->post(route('sales-leads.import'), [
        'file' => UploadedFile::fake()->createWithContent('duplicates.csv', $duplicateCsv),
    ])->assertViewIs('app.sales.import-result')
        ->assertViewHas('importedCount', 2)
        ->assertViewHas('skippedDuplicates', 2)
        ->assertViewHas('skippedInvalid', 0);

    $this->assertDatabaseHas('leads', [
        'company_id' => $company->id, 'name' => 'Other Company Match', 'email' => 'shared@example.com',
        'mobile' => '9999999999', 'assigned_to' => $user->id,
    ]);
    $this->assertDatabaseMissing('leads', ['company_id' => $company->id, 'name' => 'Duplicate Existing']);
    $this->assertDatabaseMissing('leads', ['company_id' => $company->id, 'name' => 'Duplicate In File']);

    $this->actingAs($user)->post(route('sales-leads.import'), [
        'file' => UploadedFile::fake()->createWithContent('defaults.csv', "name,email,mobile,deal_amount,stage,status,source,priority\nDefaulted Lead,defaulted@example.com,3333333333,,,,,\n"),
    ])->assertViewIs('app.sales.import-result')
        ->assertViewHas('importedCount', 1)
        ->assertViewHas('skippedDuplicates', 0)
        ->assertViewHas('skippedInvalid', 0);

    $this->assertDatabaseHas('leads', [
        'company_id' => $company->id, 'name' => 'Defaulted Lead', 'email' => 'defaulted@example.com',
        'deal_amount' => 0, 'stage' => 'New', 'status' => 'New', 'source' => 'Self',
        'priority' => 'low', 'assigned_to' => $user->id,
    ]);

    $sampleResponse = $this->actingAs($user)->get(route('sales-leads.import.sample'))
        ->assertSuccessful()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    expect($sampleResponse->streamedContent())
        ->toContain('name,email,mobile')
        ->not->toContain('contact_person');

    $this->actingAs($user)->post(route('sales-leads.import'), [
        'file' => UploadedFile::fake()->createWithContent('invalid.csv', "name,email,mobile,stage,status,source\nInvalid Email,invalid-email,1234567890,Unknown Stage,Open,Referral\nScientific Mobile,valid@example.com,9.19411E+11,New,New,Self\nPlus Mobile,plus@example.com,+918956235656,New,New,Self\nPlain Mobile,plain@example.com,8956235656,New,New,Self\n"),
    ])->assertViewIs('app.sales.import-result')
        ->assertViewHas('importedCount', 2)
        ->assertViewHas('skippedDuplicates', 0)
        ->assertViewHas('skippedInvalid', 2)
        ->assertViewHas('failedRows', fn ($rows) => count($rows) === 2);

    expect(Lead::query()->whereIn('name', ['Invalid Email', 'Scientific Mobile'])->exists())->toBeFalse();
    $this->assertDatabaseHas('leads', ['company_id' => $company->id, 'name' => 'Plus Mobile', 'mobile' => '+918956235656']);
    $this->assertDatabaseHas('leads', ['company_id' => $company->id, 'name' => 'Plain Mobile', 'mobile' => '8956235656']);
});

test('a user can view a company lead with its details', function () {
    $company = Company::create(['name' => 'Acme', 'slug' => 'acme']);
    $user = leadCrudUser($company);
    $lead = Lead::create([
        'company_id' => $company->id,
        'created_by' => $user->id,
        'name' => 'Visible Lead',
        'email' => 'visible@example.com',
        'stage' => 'New',
        'status' => 'New',
        'source' => 'Self',
    ]);

    $this->actingAs($user)->get(route('sales-lead-view', $lead))
        ->assertSuccessful()
        ->assertSee('Visible Lead')
        ->assertSee('visible@example.com')
        ->assertSee(route('sales-leads.edit', $lead), false)
        ->assertSee(route('sales-leads.destroy', $lead), false);
});

test('a user can add an activity to a lead', function () {
    $company = Company::create(['name' => 'Acme', 'slug' => 'acme']);
    $user = leadCrudUser($company, ['view_all_leads', 'create_all_activities']);
    $lead = Lead::create([
        'company_id' => $company->id,
        'created_by' => $user->id,
        'name' => 'Activity Lead',
        'stage' => 'New',
        'status' => 'New',
        'source' => 'Self',
    ]);

    $this->actingAs($user)->post(route('sales-lead-activities.store', $lead), [
        'activity_type' => 'call',
        'subject' => 'Introductory call',
        'summary' => 'Discussed requirements.',
    ])->assertRedirect(route('sales-lead-view', $lead));

    $activity = LeadActivity::query()->firstOrFail();
    expect($activity->lead_id)->toBe($lead->id)
        ->and($activity->user_id)->toBe($user->id)
        ->and($activity->status)->toBe('completed');

    $this->actingAs($user)->get(route('sales-lead-view', $lead))
        ->assertSee('Introductory call')
        ->assertSee('Discussed requirements.');
});

test('kanban supports in-place lead creation, details, and status changes', function () {
    $company = Company::create(['name' => 'Acme', 'slug' => 'acme']);
    $user = leadCrudUser($company);
    LeadSetting::insert([
        ['setting_type' => 'stage', 'name' => 'Qualification', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'status', 'name' => 'Open', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'status', 'name' => 'Won', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'source', 'name' => 'Referral', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
    ]);

    $this->actingAs($user)->get(route('sale-kanban'))
        ->assertSuccessful()
        ->assertSee('Sales pipeline')
        ->assertSee('Open');

    $this->actingAs($user)->getJson(route('sales-leads.kanban-data', ['status' => 'Open']))
        ->assertSuccessful()
        ->assertJsonPath('total', 0)
        ->assertJsonPath('nextPage', null);

    $this->actingAs($user)->postJson(route('sales-leads.store'), leadCrudPayload(['assigned_to' => $user->id]))
        ->assertCreated()
        ->assertJsonPath('status', 'Open')
        ->assertJsonPath('message', 'Lead created successfully!');

    $lead = Lead::query()->firstOrFail();
    LeadActivity::create([
        'company_id' => $company->id,
        'lead_id' => $lead->id,
        'user_id' => $user->id,
        'activity_type' => 'notes',
        'subject' => 'Qualification note',
        'summary' => 'Ready for a follow-up.',
        'status' => 'completed',
    ]);

    $detailsResponse = $this->actingAs($user)->getJson(route('sales-leads.kanban-details', $lead))
        ->assertSuccessful()
        ->assertJsonPath('name', 'Jane Lead');

    expect($detailsResponse->json('html'))->toContain('Qualification note');

    $this->actingAs($user)->patchJson(route('sales-leads.status', $lead), ['status' => 'Won'])
        ->assertSuccessful()
        ->assertJsonPath('status', 'Won');

    $this->actingAs($user)->patchJson(route('sales-leads.assignee', $lead), ['assigned_to' => $user->id])
        ->assertSuccessful()
        ->assertJsonPath('assignee', $user->name);

    expect($lead->fresh()->status)->toBe('Won');
});

test('a visit activity can update the lead address', function () {
    $company = Company::create(['name' => 'Acme', 'slug' => 'acme']);
    $user = leadCrudUser($company, ['view_all_leads', 'edit_all_leads', 'schedule_all_followups']);
    $lead = Lead::create([
        'company_id' => $company->id,
        'created_by' => $user->id,
        'name' => 'Visit Lead',
        'stage' => 'New',
        'status' => 'New',
        'source' => 'Self',
    ]);

    $this->actingAs($user)->postJson(route('sales-lead-activities.store', $lead), [
        'activity_type' => 'visit',
        'visit_address' => '1 Main Street',
        'visit_country' => 'India',
        'visit_state' => 'Maharashtra',
        'visit_city' => 'Pulgaon',
        'visit_zip' => '442302',
        'visit_motive' => 'Site survey',
        'visit_scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
        'mark_as_lead_address' => true,
    ])->assertSuccessful();

    expect($lead->fresh()->address)->toBe('1 Main Street')
        ->and($lead->fresh()->city)->toBe('Pulgaon');
});
