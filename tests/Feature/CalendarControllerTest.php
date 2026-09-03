<?php

use App\Models\Company;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Create a test company and an active user belonging to it.
 *
 * @return array{0: Company, 1: User}
 */
function calendarTestUser(): array
{
    $company = Company::create(['name' => 'Calendar Corp', 'slug' => 'calendar-corp']);
    $user = User::factory()->for($company)->create(['is_active' => true]);

    return [$company, $user];
}

/**
 * Create a minimal Lead for the given company.
 */
function calendarTestLead(Company $company, User $user, string $name = 'Test Lead'): Lead
{
    return Lead::create([
        'company_id' => $company->id,
        'created_by' => $user->id,
        'name' => $name,
        'stage' => 'New',
        'status' => 'Open',
        'source' => 'Direct',
    ]);
}

// ------------------------------------------------------------------
// Auth
// ------------------------------------------------------------------

test('guests are redirected from the calendar page', function () {
    $this->get(route('calendar'))->assertRedirect('/login');
});

test('guests are redirected from the calendar events endpoint', function () {
    $this->get(route('calendar.events'))->assertRedirect('/login');
});

test('authenticated users can visit the calendar page', function () {
    [$company, $user] = calendarTestUser();
    $this->actingAs($user)
        ->get(route('calendar'))
        ->assertSuccessful()
        ->assertSee('id="mini-calendar"', false)
        ->assertSee('miniCalendarInstance', false)
        ->assertSee('dateClick', false)
        ->assertSee('calendarDayModal', false)
        ->assertSee('day-summary', false)
        ->assertSee('Followup', false)
        ->assertSee('Meet', false)
        ->assertSee('calendar-record', false)
        ->assertSee('calendarActivityModal', false)
        ->assertSee('data-calendar-edit', false)
        ->assertSee('data-calendar-complete', false);
});

// ------------------------------------------------------------------
// Events JSON endpoint
// ------------------------------------------------------------------

test('calendar events endpoint returns an empty json array when no activities exist', function () {
    [$company, $user] = calendarTestUser();

    $this->actingAs($user)
        ->getJson(route('calendar.events'))
        ->assertSuccessful()
        ->assertJsonIsArray()
        ->assertJsonCount(0);
});

test('calendar events includes followup activities with correct shape', function () {
    [$company, $user] = calendarTestUser();
    $lead = calendarTestLead($company, $user, '364 Beat');

    LeadActivity::create([
        'company_id' => $company->id,
        'lead_id' => $lead->id,
        'user_id' => $user->id,
        'activity_type' => 'followup',
        'followup_type' => 'call',
        'subject' => 'Reminder call',
        'scheduled_at' => now()->addDay(),
        'status' => 'pending',
    ]);

    $this->actingAs($user)
        ->getJson(route('calendar.events'))
        ->assertSuccessful()
        ->assertJsonCount(1)
        ->assertJsonFragment([
            'activityType' => 'followup',
            'followupType' => 'call',
            'leadName' => '364 Beat',
            'status' => 'pending',
        ])
        ->assertJsonFragment(['leadStatus' => 'Open', 'leadStage' => 'New', 'leadSource' => 'Direct']);
});

test('calendar events includes visit and gmeet activity types', function () {
    [$company, $user] = calendarTestUser();
    $lead = calendarTestLead($company, $user, 'Site Client');

    LeadActivity::create([
        'company_id' => $company->id,
        'lead_id' => $lead->id,
        'activity_type' => 'visit',
        'scheduled_at' => now()->addDays(2),
        'status' => 'pending',
    ]);

    LeadActivity::create([
        'company_id' => $company->id,
        'lead_id' => $lead->id,
        'activity_type' => 'gmeet',
        'scheduled_at' => now()->addDays(3),
        'status' => 'pending',
    ]);

    $this->actingAs($user)
        ->getJson(route('calendar.events'))
        ->assertSuccessful()
        ->assertJsonCount(2)
        ->assertJsonFragment(['activityType' => 'visit'])
        ->assertJsonFragment(['activityType' => 'gmeet']);
});

test('calendar events excludes notes and call activity types', function () {
    [$company, $user] = calendarTestUser();
    $lead = calendarTestLead($company, $user);

    LeadActivity::create([
        'company_id' => $company->id,
        'lead_id' => $lead->id,
        'activity_type' => 'notes',
        'scheduled_at' => now()->addDay(),
        'status' => 'completed',
    ]);

    LeadActivity::create([
        'company_id' => $company->id,
        'lead_id' => $lead->id,
        'activity_type' => 'call',
        'scheduled_at' => now()->addDay(),
        'status' => 'completed',
    ]);

    $this->actingAs($user)
        ->getJson(route('calendar.events'))
        ->assertSuccessful()
        ->assertJsonCount(0);
});

test('calendar events excludes activities with null scheduled_at', function () {
    [$company, $user] = calendarTestUser();
    $lead = calendarTestLead($company, $user);

    LeadActivity::create([
        'company_id' => $company->id,
        'lead_id' => $lead->id,
        'activity_type' => 'followup',
        'followup_type' => 'call',
        'scheduled_at' => null, // no schedule → should not appear
        'status' => 'completed',
    ]);

    $this->actingAs($user)
        ->getJson(route('calendar.events'))
        ->assertSuccessful()
        ->assertJsonCount(0);
});

// ------------------------------------------------------------------
// Multi-tenancy
// ------------------------------------------------------------------

test('calendar events are scoped to the authenticated users company', function () {
    [$company, $user] = calendarTestUser();

    $otherCompany = Company::create(['name' => 'Rival Corp', 'slug' => 'rival-corp']);
    $otherUser = User::factory()->for($otherCompany)->create(['is_active' => true]);
    $otherLead = calendarTestLead($otherCompany, $otherUser, 'Rival Lead');

    LeadActivity::create([
        'company_id' => $otherCompany->id,
        'lead_id' => $otherLead->id,
        'activity_type' => 'followup',
        'followup_type' => 'call',
        'scheduled_at' => now()->addDay(),
        'status' => 'pending',
    ]);

    $this->actingAs($user)
        ->getJson(route('calendar.events'))
        ->assertSuccessful()
        ->assertJsonCount(0);
});

// ------------------------------------------------------------------
// Date range filtering
// ------------------------------------------------------------------

test('calendar events can be filtered by start and end date range', function () {
    [$company, $user] = calendarTestUser();
    $lead = calendarTestLead($company, $user, 'Range Lead');

    // Activity within the next 7 days — should be included
    LeadActivity::create([
        'company_id' => $company->id,
        'lead_id' => $lead->id,
        'activity_type' => 'followup',
        'followup_type' => 'call',
        'scheduled_at' => now()->addDays(5),
        'status' => 'pending',
    ]);

    // Activity 30 days out — should be excluded
    LeadActivity::create([
        'company_id' => $company->id,
        'lead_id' => $lead->id,
        'activity_type' => 'visit',
        'scheduled_at' => now()->addDays(30),
        'status' => 'pending',
    ]);

    $start = now()->toIso8601String();
    $end = now()->addDays(10)->toIso8601String();

    $this->actingAs($user)
        ->getJson(route('calendar.events', ['start' => $start, 'end' => $end]))
        ->assertSuccessful()
        ->assertJsonCount(1)
        ->assertJsonFragment(['activityType' => 'followup']);
});
