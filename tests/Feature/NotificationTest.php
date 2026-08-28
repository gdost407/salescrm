<?php

use App\Jobs\SendLeadActivityNotification;
use App\Models\Company;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Notification;
use App\Models\User;

function notificationTestUser(Company $company): User
{
    return User::factory()->for($company)->create(['is_active' => true]);
}

test('a due lead activity creates a notification for its responsible users', function () {
    $company = Company::create(['name' => 'Acme', 'slug' => 'acme']);
    $user = notificationTestUser($company);
    $assignee = notificationTestUser($company);
    $lead = Lead::create([
        'company_id' => $company->id,
        'created_by' => $user->id,
        'assigned_to' => $assignee->id,
        'name' => 'Reminder Lead',
    ]);
    $activity = LeadActivity::create([
        'company_id' => $company->id,
        'lead_id' => $lead->id,
        'user_id' => $user->id,
        'activity_type' => 'visit',
        'subject' => 'Site visit scheduled',
        'scheduled_at' => now()->subMinute(),
        'status' => 'pending',
    ]);

    (new SendLeadActivityNotification($activity->id))->handle();

    expect(Notification::query()->where('activity_id', $activity->id)->count())->toBe(2);
    expect(Notification::query()->where('user_id', $assignee->id)->first()->data['url'])
        ->toBe(route('sales-lead-view', $lead));
});

test('a rescheduled activity cannot create its old reminder', function () {
    $company = Company::create(['name' => 'Acme', 'slug' => 'acme']);
    $user = notificationTestUser($company);
    $lead = Lead::create(['company_id' => $company->id, 'created_by' => $user->id, 'name' => 'Rescheduled Lead']);
    $activity = LeadActivity::create([
        'company_id' => $company->id,
        'lead_id' => $lead->id,
        'user_id' => $user->id,
        'activity_type' => 'followup',
        'subject' => 'Follow-up scheduled',
        'scheduled_at' => now()->subMinute(),
        'status' => 'pending',
        'metadata' => ['activity_status' => 'rescheduled'],
    ]);

    (new SendLeadActivityNotification($activity->id))->handle();

    expect(Notification::query()->where('activity_id', $activity->id)->exists())->toBeFalse();
});

test('users can only read their own company notifications', function () {
    $company = Company::create(['name' => 'Acme', 'slug' => 'acme']);
    $otherCompany = Company::create(['name' => 'Other', 'slug' => 'other']);
    $user = notificationTestUser($company);
    $otherUser = notificationTestUser($otherCompany);
    $notification = Notification::create([
        'company_id' => $otherCompany->id,
        'user_id' => $otherUser->id,
        'type' => 'lead_activity_reminder',
        'title' => 'Private reminder',
        'message' => 'Private message',
    ]);

    $this->actingAs($user)->get(route('notifications.unread'))
        ->assertSuccessful()
        ->assertJson(['notifications' => []]);
    $this->actingAs($user)->patch(route('notifications.read', $notification))
        ->assertNotFound();
});

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
