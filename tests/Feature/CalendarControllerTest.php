<?php

use App\Models\Company;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Create a test company and an active user belonging to it.
 *
 * @return array{0: Company, 1: User}
 */
function calendarTestUser(): array
{
    $company = Company::create(['name' => 'Calendar Corp', 'slug' => 'calendar-corp', 'onboarding_completed_at' => now()]);
    $user = User::factory()->for($company)->create(['is_active' => true, 'user_type' => 'owner']);

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

test('calendar events preserve scheduled wall clock times without a timezone conversion', function (string $activityType, string $time) {
    [$company, $user] = calendarTestUser();
    $lead = calendarTestLead($company, $user);

    LeadActivity::create([
        'company_id' => $company->id,
        'lead_id' => $lead->id,
        'user_id' => $user->id,
        'activity_type' => $activityType,
        'scheduled_at' => '2026-09-25 '.$time,
        'status' => 'pending',
    ]);

    $this->actingAs($user)
        ->getJson(route('calendar.events', ['start' => '2026-09-25', 'end' => '2026-09-26']))
        ->assertSuccessful()
        ->assertJsonCount(1)
        ->assertJsonPath('0.start', '2026-09-25T'.$time)
        ->assertJsonPath('0.extendedProps.scheduledAt', '2026-09-25T'.$time);
})->with(['followup', 'visit', 'gmeet'])->with(['10:00:00', '23:30:00']);

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

test('today event feed includes assignees and actions and excludes other days and companies', function () {
    [$company, $user] = calendarTestUser();
    $lead = calendarTestLead($company, $user);
    $lead->update(['assigned_to' => $user->id]);
    $today = now()->startOfDay();

    foreach ([$today->copy()->subDay(), $today->copy()->setTime(10, 0), $today->copy()->setTime(23, 59, 59), $today->copy()->addDay()] as $scheduledAt) {
        LeadActivity::create([
            'company_id' => $company->id, 'lead_id' => $lead->id,
            'activity_type' => 'followup', 'subject' => 'Call customer',
            'scheduled_at' => $scheduledAt, 'status' => 'pending',
        ]);
    }
    $foreignLead = Lead::factory()->create();
    LeadActivity::create([
        'company_id' => $foreignLead->company_id, 'lead_id' => $foreignLead->id,
        'activity_type' => 'visit', 'scheduled_at' => $today, 'status' => 'pending',
    ]);

    $this->actingAs($user)->getJson(route('calendar.events', [
        'start' => $today->format('Y-m-d').' 00:00:00',
        'end' => $today->format('Y-m-d').' 23:59:59',
    ]))->assertSuccessful()->assertJsonCount(2)
        ->assertJsonPath('0.start', $today->format('Y-m-d').'T10:00:00')
        ->assertJsonPath('0.extendedProps.assignedTo', $user->name)
        ->assertJsonPath('0.extendedProps.subject', 'Call customer')
        ->assertJsonPath('0.extendedProps.canEdit', true)
        ->assertJsonPath('0.extendedProps.canComplete', true);
});

test('event feed limits staff to assigned leads and reports their activity permissions', function () {
    [$company, $owner] = calendarTestUser();
    app(RolePermissionSeeder::class)->seedCompany($company->id);
    $role = Role::create(['company_id' => $company->id, 'name' => 'Calendar reader', 'slug' => 'calendar-reader', 'status' => true]);
    $role->permissions()->sync(Permission::where('company_id', $company->id)->where('slug', 'view_own_leads')->pluck('id'));
    $staff = User::factory()->for($company)->create(['user_type' => 'staff', 'role_id' => $role->id]);
    $own = calendarTestLead($company, $owner);
    $own->update(['assigned_to' => $staff->id]);
    $hidden = calendarTestLead($company, $owner, 'Hidden lead');
    foreach ([$own, $hidden] as $lead) {
        LeadActivity::create([
            'company_id' => $company->id, 'lead_id' => $lead->id,
            'activity_type' => 'followup', 'scheduled_at' => now(), 'status' => 'pending',
        ]);
    }
    $this->actingAs($staff)->getJson(route('calendar.events'))->assertSuccessful()->assertJsonCount(1)
        ->assertJsonPath('0.extendedProps.leadId', $own->id)
        ->assertJsonPath('0.extendedProps.assignedTo', $staff->name)
        ->assertJsonPath('0.extendedProps.canEdit', false)
        ->assertJsonPath('0.extendedProps.canComplete', false);
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
