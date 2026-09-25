<?php

use App\Models\Attendance;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Carbon\CarbonImmutable;

beforeEach(function () {
    config(['attendance.timezone' => 'Asia/Kolkata']);
    $this->travelTo(CarbonImmutable::parse('2026-09-25 04:30:00', 'UTC'));
    $this->company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $this->staff = User::factory()->for($this->company)->create(['user_type' => 'staff']);
    $this->owner = User::factory()->for($this->company)->create(['user_type' => 'owner']);
});

test('staff can record one shift and one break with server calculated hours', function () {
    $this->actingAs($this->staff)->postJson(route('staff.attendance.punch'), [
        'action' => 'punch_in', 'company_id' => 999, 'user_id' => $this->owner->id,
        'punch_in' => '2000-01-01', 'working_seconds' => 999999,
    ])->assertSuccessful();
    $record = Attendance::sole();
    expect($record->user_id)->toBe($this->staff->id)->and($record->company_id)->toBe($this->company->id)
        ->and($record->punch_in->format('H:i'))->toBe('04:30');
    $this->postJson(route('staff.attendance.punch'), ['action' => 'punch_in'])->assertUnprocessable();
    $this->travel(3)->hours();
    $this->postJson(route('staff.attendance.punch'), ['action' => 'break_in'])->assertSuccessful();
    $this->postJson(route('staff.attendance.punch'), ['action' => 'break_in'])->assertUnprocessable();
    $this->postJson(route('staff.attendance.punch'), ['action' => 'punch_out'])->assertUnprocessable();
    $this->travel(30)->minutes();
    expect($record->fresh()->workingHours())->toBe('03:00');
    $this->postJson(route('staff.attendance.punch'), ['action' => 'break_out'])->assertSuccessful();
    $this->postJson(route('staff.attendance.punch'), ['action' => 'break_out'])->assertUnprocessable();
    $this->postJson(route('staff.attendance.punch'), ['action' => 'break_in'])->assertUnprocessable();
    $this->travel(4)->hours();
    $this->postJson(route('staff.attendance.punch'), ['action' => 'punch_out'])->assertSuccessful();
    expect($record->fresh()->working_seconds)->toBe(25200)->and($record->fresh()->workingHours())->toBe('07:00');
    $this->postJson(route('staff.attendance.punch'), ['action' => 'punch_out'])->assertUnprocessable();
    $this->postJson(route('staff.attendance.punch'), ['action' => 'punch_in'])->assertUnprocessable();
    expect(Attendance::count())->toBe(1);
});

test('punch actions require an active staff account and a valid sequence', function () {
    $this->postJson(route('staff.attendance.punch'), ['action' => 'punch_in'])->assertUnauthorized();
    $this->actingAs($this->owner)->postJson(route('staff.attendance.punch'), ['action' => 'punch_in'])->assertForbidden();
    $this->actingAs($this->staff);
    foreach (['punch_out', 'break_in', 'break_out', 'invalid'] as $action) {
        $this->postJson(route('staff.attendance.punch'), compact('action'))->assertUnprocessable();
    }
    $this->staff->update(['is_active' => false]);
    $this->postJson(route('staff.attendance.punch'), ['action' => 'punch_in'])->assertForbidden();
    $this->getJson(route('staff.attendance.index'))->assertForbidden();
    expect(Attendance::count())->toBe(0);
});

test('attendance uses the local day and allows completing an overnight shift', function () {
    $this->travelTo(CarbonImmutable::parse('2026-09-25 18:00:00', 'UTC'));
    $this->actingAs($this->staff)->postJson(route('staff.attendance.punch'), ['action' => 'punch_in'])->assertSuccessful();
    $this->travel(2)->hours();
    $this->get(route('dashboard'))->assertSuccessful()->assertSee('Your earlier shift is still open.');
    $this->postJson(route('staff.attendance.punch'), ['action' => 'punch_in'])->assertUnprocessable();
    $this->postJson(route('staff.attendance.punch'), ['action' => 'punch_out'])->assertSuccessful();
    expect(Attendance::sole()->date->toDateString())->toBe('2026-09-25')->and(Attendance::sole()->working_seconds)->toBe(7200);
    $this->postJson(route('staff.attendance.punch'), ['action' => 'punch_in'])->assertSuccessful();
    expect(Attendance::latest('id')->first()->date->toDateString())->toBe('2026-09-26');
});

test('dashboard punching is staff only and shows local time', function () {
    $this->actingAs($this->owner)->get(route('dashboard'))->assertSuccessful()->assertDontSee('id="attendance-punch"', false);
    $this->actingAs($this->staff)->get(route('dashboard'))->assertSuccessful()->assertSee('Punch in')->assertSee('staff/attendance');
    $this->postJson(route('staff.attendance.punch'), ['action' => 'punch_in'])->assertSuccessful();
    $this->get(route('dashboard'))->assertSuccessful()->assertSee('10:00 AM')->assertSee('Start break')->assertSee('Punch out');
});

test('attendance table and calendar isolate staff and companies with admin filters', function (string $view) {
    $own = Attendance::factory()->for($this->staff)->create(['company_id' => $this->company->id]);
    $other = User::factory()->for($this->company)->create(['user_type' => 'staff']);
    $otherRecord = Attendance::factory()->for($other)->create(['company_id' => $this->company->id]);
    $foreign = Attendance::factory()->create();
    $url = route('staff.attendance.index', ['month' => '2026-09', 'view' => $view]);
    $response = $this->actingAs($this->staff)->get($url)->assertSuccessful();
    expect($response['records']->modelKeys())->toBe([$own->id]);
    $this->getJson(route('staff.attendance.index', ['staff_id' => $other->id]))->assertForbidden();
    $this->getJson(route('staff.attendance.index', ['staff_id' => $foreign->user_id]))->assertUnprocessable();
    $response = $this->actingAs($this->owner)->get($url)->assertSuccessful();
    expect($response['records']->modelKeys())->toContain($own->id, $otherRecord->id)->not->toContain($foreign->id);
    $response = $this->get(route('staff.attendance.index', ['staff_id' => $other->id, 'view' => $view]))->assertSuccessful();
    expect($response['records']->modelKeys())->toBe([$otherRecord->id]);
    if ($view === 'calendar') {
        expect($response['calendarDays']->filter()->sum('count'))->toBe(1);
    }
    $role = Role::create(['company_id' => $this->company->id, 'name' => 'Administrator', 'slug' => 'admin', 'status' => true]);
    $this->staff->update(['role_id' => $role->id]);
    $response = $this->actingAs($this->staff->fresh())->get($url)->assertSuccessful();
    expect($response['records']->total())->toBe(2);
})->with(['table', 'calendar']);

test('attendance filters validate dates and filter status and selected day', function () {
    Attendance::factory()->for($this->staff)->create(['company_id' => $this->company->id]);
    $this->actingAs($this->owner);
    foreach ([['month' => 'bad'], ['view' => 'invalid'], ['status' => 'absent'], ['date' => 'bad']] as $filter) {
        $this->getJson(route('staff.attendance.index', $filter))->assertUnprocessable();
    }
    foreach ([['month' => '2026-08'], ['status' => 'completed'], ['date' => '2026-09-24']] as $filter) {
        $response = $this->get(route('staff.attendance.index', $filter))->assertSuccessful();
        expect($response['records']->total())->toBe(0);
    }
});
