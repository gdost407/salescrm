<?php

use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    $this->staff = User::factory()->for(Company::factory()->create(['onboarding_completed_at' => now()]))->create(['user_type' => 'staff']);
});

test('unauthorized browser requests redirect safely with a visible error', function (string $route) {
    $this->actingAs($this->staff)->withHeader('Referer', 'https://untrusted.example/')
        ->get(route($route))->assertRedirect(route('dashboard'))->assertSessionHas('access_error');
    $this->get(route('dashboard'))->assertSuccessful()->assertSee('You do not have permission to perform that action.');
})->with(['sale-kanban', 'calendar', 'sales-all-list', 'staff-manage', 'staff-roles']);

test('denied browser writes cannot create roles', function () {
    $this->actingAs($this->staff)->post(route('staff.roles.store'), ['name' => 'Forbidden'])
        ->assertRedirect(route('dashboard'))->assertSessionHas('access_error');
    $this->assertDatabaseMissing('roles', ['name' => 'Forbidden']);
});

test('AJAX and JSON permission failures remain errors rather than successful HTML responses', function () {
    $this->actingAs($this->staff)->getJson(route('sales-all-list'))->assertForbidden();
    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest', 'Accept' => '*/*'])->get(route('staff-manage'))->assertForbidden();
});

test('authorization exceptions redirect guests to login with an error', function () {
    Route::middleware('web')->get('/test-denied', function () {
        throw new AuthorizationException;
    });
    $this->get('/test-denied')->assertRedirect(route('login'))->assertSessionHas('access_error');
    $this->get(route('login'))->assertSuccessful()->assertSee('You do not have permission to perform that action.');
});

test('foreign records stay hidden with a not found response', function () {
    $owner = User::factory()->for($this->staff->company)->create(['user_type' => 'owner']);
    $foreign = User::factory()->for(Company::factory())->create(['user_type' => 'staff']);
    $this->actingAs($owner)->get(route('staff.edit', $foreign))->assertNotFound();
});
