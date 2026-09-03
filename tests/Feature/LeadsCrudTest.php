<?php

use App\Models\Company;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadSetting;
use App\Models\User;
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
        ->assertSee('Import leads')
        ->assertSee('Download sample sheet')
        ->assertSee(route('sales-leads.import'), false)
        ->assertSee(route('sales-leads.import.sample'), false)
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

test('lead list filters, paginates, and exports only the company leads', function () {
    $company = Company::create(['name' => 'Acme', 'slug' => 'acme']);
    $otherCompany = Company::create(['name' => 'Other', 'slug' => 'other']);
    $user = leadCrudUser($company);
    $assignee = leadCrudUser($company);
    LeadSetting::insert([
        ['setting_type' => 'stage', 'name' => 'Qualification', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'status', 'name' => 'Open', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
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

    $exportResponse = $this->actingAs($user)->get(route('sales-leads.export', ['search' => 'Matching']))
        ->assertSuccessful()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    expect($exportResponse->streamedContent())->toContain('Matching Lead')
        ->not->toContain('Private Matching Lead');
});

test('a user can import leads from a CSV sample format', function () {
    $company = Company::create(['name' => 'Acme', 'slug' => 'acme']);
    $user = leadCrudUser($company);
    LeadSetting::insert([
        ['setting_type' => 'stage', 'name' => 'Qualification', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'status', 'name' => 'Open', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ['setting_type' => 'source', 'name' => 'Referral', 'type' => 'system', 'created_at' => now(), 'updated_at' => now()],
    ]);

    $csv = "name,email,mobile,job_title,deal_amount,stage,status,source,contact_person,priority\nImported Lead,imported@example.com,1234567890,Buyer,1000,Qualification,Open,Referral,{$user->name},high\n";

    $this->actingAs($user)->post(route('sales-leads.import'), [
        'file' => UploadedFile::fake()->createWithContent('leads.csv', $csv),
    ])->assertRedirect(route('sales-all-list'));

    $this->assertDatabaseHas('leads', [
        'company_id' => $company->id, 'name' => 'Imported Lead', 'email' => 'imported@example.com',
        'assigned_to' => $user->id, 'priority' => 'high',
    ]);

    $sampleResponse = $this->actingAs($user)->get(route('sales-leads.import.sample'))
        ->assertSuccessful()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    expect($sampleResponse->streamedContent())->toContain('name,email,mobile');

    $this->actingAs($user)->post(route('sales-leads.import'), [
        'file' => UploadedFile::fake()->createWithContent('invalid.csv', "name,stage,status,source,contact_person\nInvalid Lead,Qualification,Open,Referral,Unknown User\n"),
    ])->assertSessionHasErrors('file');

    expect(Lead::query()->where('name', 'Invalid Lead')->exists())->toBeFalse();
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

    expect($lead->fresh()->status)->toBe('Won');
});

test('a visit activity can update the lead address', function () {
    $company = Company::create(['name' => 'Acme', 'slug' => 'acme']);
    $user = leadCrudUser($company);
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
