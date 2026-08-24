<?php

use App\Models\Company;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadSetting;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

function leadCrudUser(Company $company): User
{
    return User::factory()->for($company)->create(['is_active' => true]);
}

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
        ->assertSee($user->name);
});

test('a user can create update and delete a lead', function () {
    $company = Company::create(['name' => 'Acme', 'slug' => 'acme']);
    $user = leadCrudUser($company);
    LeadSetting::insert([
        ['setting_type' => 'stage', 'name' => 'Qualification', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'status', 'name' => 'Open', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
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
    $user = leadCrudUser($company);
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
