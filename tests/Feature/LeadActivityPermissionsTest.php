<?php

use App\Models\Company;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    $this->company = Company::factory()->create(['onboarding_completed_at' => now()]);
    app(RolePermissionSeeder::class)->seedCompany($this->company->id);
    $this->role = Role::create(['company_id' => $this->company->id, 'name' => 'Activity staff', 'slug' => 'activity-staff', 'status' => true]);
    $this->staff = User::factory()->for($this->company)->create(['user_type' => 'staff', 'role_id' => $this->role->id]);
    $this->lead = Lead::factory()->for($this->company)->create(['assigned_to' => $this->staff->id]);
    $this->role->permissions()->sync(Permission::where('company_id', $this->company->id)->where('slug', 'view_all_leads')->pluck('id'));
});

test('activity operations enforce their own action and assigned or all scope', function (string $operation, string $prefix, string $scope, bool $assigned) {
    $this->lead->update(['assigned_to' => $assigned ? $this->staff->id : null]);
    $slug = str_replace('%s', $scope, $prefix);
    $this->role->permissions()->attach(Permission::where('company_id', $this->company->id)->where('slug', $slug)->firstOrFail());
    $type = in_array($operation, ['add', 'update'], true) ? 'notes' : 'followup';
    $activity = LeadActivity::create([
        'company_id' => $this->company->id, 'lead_id' => $this->lead->id,
        'activity_type' => $type, 'status' => $type === 'notes' ? 'completed' : 'pending',
        'summary' => 'Existing', 'scheduled_at' => $type === 'followup' ? now()->addDay() : null,
    ]);
    $payload = ['activity_type' => $type, 'summary' => 'Updated', 'followup_date' => now()->addDays(2)->toDateString(), 'followup_time' => '10:00'];
    $this->actingAs($this->staff);
    $response = match ($operation) {
        'add', 'schedule' => $this->postJson(route('sales-lead-activities.store', $this->lead), $payload),
        'update', 'manage' => $this->putJson(route('sales-lead-activities.update', [$this->lead, $activity]), $payload),
        'delete' => $this->deleteJson(route('sales-lead-activities.destroy', [$this->lead, $activity])),
        'complete' => $this->postJson(route('sales-lead-activities.complete', [$this->lead, $activity]), ['final_note' => 'Done']),
    };
    if ($scope === 'all' || $assigned) {
        $response->assertSuccessful();
    } else {
        $response->assertForbidden();
        expect($activity->fresh()->summary)->toBe('Existing');
    }
    expect($this->staff->hasPermission('edit_all_leads'))->toBeFalse();
})->with([
    ['add', 'create_%s_activities'], ['update', 'edit_%s_activities'],
    ['delete', 'delete_%s_activities'], ['schedule', 'schedule_%s_followups'],
    ['manage', 'manage_%s_followups'], ['complete', 'work_%s_followups'],
])->with(['own', 'all'])->with([true, false]);

test('lead editing alone cannot mutate activities and activity permissions cannot cross companies', function () {
    $this->role->permissions()->attach(Permission::where('company_id', $this->company->id)->where('slug', 'edit_all_leads')->firstOrFail());
    $this->actingAs($this->staff)->postJson(route('sales-lead-activities.store', $this->lead), ['activity_type' => 'notes', 'summary' => 'No access'])->assertForbidden();
    $this->role->permissions()->attach(Permission::where('company_id', $this->company->id)->where('slug', 'create_all_activities')->firstOrFail());
    $foreign = Lead::factory()->create();
    $this->actingAs($this->staff->fresh())->postJson(route('sales-lead-activities.store', $foreign), ['activity_type' => 'notes'])->assertNotFound();
});

test('follow-up work can complete but cannot reschedule delete or switch activity types', function () {
    $this->role->permissions()->attach(Permission::where('company_id', $this->company->id)->where('slug', 'work_own_followups')->firstOrFail());
    $activity = LeadActivity::create(['company_id' => $this->company->id, 'lead_id' => $this->lead->id, 'activity_type' => 'followup', 'status' => 'pending', 'scheduled_at' => now()->addDay()]);
    $this->actingAs($this->staff)->putJson(route('sales-lead-activities.update', [$this->lead, $activity]), ['activity_type' => 'notes', 'summary' => 'Spoof'])->assertForbidden();
    $this->deleteJson(route('sales-lead-activities.destroy', [$this->lead, $activity]))->assertForbidden();
    $this->postJson(route('sales-lead-activities.complete', [$this->lead, $activity]), ['final_note' => 'Completed work'])->assertSuccessful();
    expect($activity->fresh()->status)->toBe('completed');
});

test('activity creators can work their own visible follow-ups and foreign activity bindings are rejected', function () {
    $this->lead->update(['assigned_to' => null]);
    $this->role->permissions()->attach(Permission::where('company_id', $this->company->id)->where('slug', 'work_own_followups')->firstOrFail());
    $activity = LeadActivity::create(['company_id' => $this->company->id, 'lead_id' => $this->lead->id, 'user_id' => $this->staff->id, 'activity_type' => 'followup', 'status' => 'pending']);
    $otherLead = Lead::factory()->for($this->company)->create();
    $this->actingAs($this->staff)->postJson(route('sales-lead-activities.complete', [$otherLead, $activity]), ['final_note' => 'No'])->assertNotFound();
    $this->postJson(route('sales-lead-activities.complete', [$this->lead, $activity]), ['final_note' => 'Done'])->assertSuccessful();
});

test('follow-up managers cannot change activity type or edit lead fields through activity requests', function () {
    $this->role->permissions()->attach(Permission::where('company_id', $this->company->id)->whereIn('slug', ['manage_own_followups', 'schedule_own_followups'])->pluck('id'));
    $activity = LeadActivity::create(['company_id' => $this->company->id, 'lead_id' => $this->lead->id, 'activity_type' => 'followup', 'status' => 'pending', 'scheduled_at' => now()->addDay()]);
    $this->actingAs($this->staff)->putJson(route('sales-lead-activities.update', [$this->lead, $activity]), ['activity_type' => 'notes', 'summary' => 'Spoof'])->assertUnprocessable()->assertJsonValidationErrors('activity_type');
    $this->postJson(route('sales-lead-activities.store', $this->lead), ['activity_type' => 'visit', 'mark_as_lead_address' => true])->assertForbidden();
    expect($activity->fresh()->activity_type)->toBe('followup');
});

test('activity permissions still require visibility of the lead', function () {
    $this->role->permissions()->sync(Permission::where('company_id', $this->company->id)->where('slug', 'create_all_activities')->pluck('id'));
    $this->actingAs($this->staff)->postJson(route('sales-lead-activities.store', $this->lead), ['activity_type' => 'notes', 'summary' => 'Hidden lead'])->assertForbidden();
});
