<?php

use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\Integration;
use App\Models\Lead;
use App\Models\SubscriptionPlan;
use App\Models\Tax;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Livewire\Volt\Volt;

beforeEach(function () {
    $this->company = Company::factory()->create(['onboarding_completed_at' => now()->subMonths(2)]);
    $this->owner = User::factory()->for($this->company)->create(['user_type' => 'owner']);
    $this->actingAs($this->owner);
});

test('the trial ends exactly one calendar month after onboarding including month end', function () {
    $this->travelTo(CarbonImmutable::parse('2027-01-31 10:00:00'));
    $this->company->update(['onboarding_completed_at' => now()]);
    expect($this->company->trialEndsAt()->format('Y-m-d H:i:s'))->toBe('2027-02-28 10:00:00');
    $this->travelTo(CarbonImmutable::parse('2027-02-28 09:59:59'));
    expect($this->company->fresh()->hasWorkspaceAccess())->toBeTrue();
    $this->travelTo(CarbonImmutable::parse('2027-02-28 10:00:00'));
    expect($this->company->fresh()->hasWorkspaceAccess())->toBeFalse();
});

test('expired companies are redirected to subscriptions but can browse module lists', function (string $routeName) {
    $writes = [];
    DB::listen(function (QueryExecuted $query) use (&$writes): void {
        if (preg_match('/^\s*(insert|update|delete)\b/i', $query->sql)) {
            $writes[] = $query->sql;
        }
    });
    $this->get(route('dashboard'))->assertRedirect(route('subscription.index'));
    $this->get(route($routeName))->assertSuccessful()->assertSee('Only module lists are available');
    $this->get(route('subscription.index'))->assertSuccessful()->assertSee('Your free trial ended');
    expect($writes)->toBe([]);
})->with(['taxes.index', 'clients.index', 'items.index', 'quotations.index', 'jobs.index', 'invoices.index', 'payments.index', 'ledger.index', 'staff.attendance.index', 'staff-manage', 'staff-roles', 'sales-all-list', 'integration-api-token']);

test('expired companies cannot insert through any module endpoint', function (string $routeName) {
    $writes = [];
    DB::listen(function (QueryExecuted $query) use (&$writes): void {
        if (preg_match('/^\s*(insert|update|delete)\b/i', $query->sql)) {
            $writes[] = $query->sql;
        }
    });
    $this->postJson(route($routeName), [])->assertForbidden();
    expect($writes)->toBe([]);
})->with(['taxes.store', 'clients.store', 'items.store', 'catalog-items.store', 'quotations.store', 'jobs.store', 'invoices.store', 'payments.store', 'ledger.store', 'staff.store', 'staff.roles.store', 'staff.attendance.punch', 'integration-api-token.store', 'sales-leads.store', 'sales-leads.import', 'sales-lead-settings.store']);

test('expired companies cannot update delete or open creation and edit pages', function () {
    $tax = Tax::factory()->for($this->company)->create(['name' => 'Original tax']);
    $this->get(route('taxes.create'))->assertRedirect(route('subscription.index'));
    $this->get(route('taxes.edit', $tax))->assertRedirect(route('subscription.index'));
    $this->get(route('taxes.show', $tax))->assertRedirect(route('subscription.index'));
    $this->putJson(route('taxes.update', $tax), ['name' => 'Changed', 'rate' => 5])->assertForbidden();
    $this->deleteJson(route('taxes.destroy', $tax))->assertForbidden();
    expect($tax->fresh()->name)->toBe('Original tax');
});

test('an active trial allows normal module writes', function () {
    $this->company->update(['onboarding_completed_at' => now()]);
    $this->owner->unsetRelation('company');
    $this->get(route('taxes.create'))->assertSuccessful();
    $this->post(route('taxes.store'), ['name' => 'Trial GST', 'rate' => 18, 'is_active' => 1])
        ->assertSessionHasNoErrors()->assertRedirect();
    expect(Tax::forCompany($this->company->id)->where('name', 'Trial GST')->exists())->toBeTrue();
    $this->get(route('subscription.index'))->assertSuccessful()->assertSee('Your one-month free trial is active');
});

test('a paid active subscription restores writes and expired cancelled or future subscriptions do not', function () {
    $plan = SubscriptionPlan::create(['name' => 'Team', 'slug' => 'team', 'monthly_price' => 499, 'is_active' => true]);
    $subscription = CompanySubscription::create([
        'company_id' => $this->company->id, 'plan_id' => $plan->id,
        'billing_cycle' => 'monthly', 'amount' => 499, 'status' => 'active',
        'starts_at' => now(), 'ends_at' => now()->addMonthNoOverflow(),
    ]);
    $this->get(route('taxes.create'))->assertSuccessful();
    $this->post(route('taxes.store'), ['name' => 'New tax', 'rate' => 5, 'is_active' => 1])->assertRedirect();
    expect(Tax::forCompany($this->company->id)->where('name', 'New tax')->exists())->toBeTrue();
    foreach ([['status' => 'cancelled'], ['status' => 'active', 'ends_at' => now()], ['ends_at' => now()->addMonth(), 'starts_at' => now()->addDay()]] as $attributes) {
        $subscription->update($attributes);
        $this->postJson(route('taxes.store'), [])->assertForbidden();
    }
});

test('expired staff can reach subscription information without gaining checkout access', function () {
    $staff = User::factory()->for($this->company)->create(['user_type' => 'staff']);
    $plan = SubscriptionPlan::create(['name' => 'Team', 'slug' => 'team', 'monthly_price' => 499, 'is_active' => true]);
    $this->actingAs($staff)->get(route('dashboard'))->assertRedirect(route('subscription.index'));
    $this->get(route('subscription.index'))->assertSuccessful()->assertSee('Contact your company owner');
    $this->getJson(route('subscription.checkout', $plan))->assertForbidden();
    $this->postJson(route('staff.attendance.punch'), ['action' => 'punch_in'])->assertForbidden();
});

test('expired webhook tokens cannot create leads on either endpoint', function () {
    Integration::create([
        'company_id' => $this->company->id, 'name' => 'Webhook', 'type' => 'webhook',
        'api_key' => hash('sha256', 'trial-token'), 'status' => true,
    ]);
    auth()->logout();
    foreach (['/webhook/v1/lead/create', '/api/webhook/v1/lead/create'] as $path) {
        $this->postJson($path, ['name' => 'Blocked'], ['Authorization' => 'Bearer trial-token'])->assertForbidden();
    }
    expect(Lead::where('company_id', $this->company->id)->count())->toBe(0);
});

test('expired Livewire onboarding cannot restart the trial or modify the company', function () {
    $originalName = $this->company->name;
    $startedAt = $this->company->onboarding_completed_at;
    Volt::actingAs($this->owner)->test('company.onboarding')
        ->set('name', 'Changed')->set('address', '1 Main St')->set('city', 'Mumbai')
        ->set('state', 'Maharashtra')->set('country', 'India')->set('pincode', '400001')
        ->call('save')->assertForbidden();
    expect($this->company->fresh()->name)->toBe($originalName)
        ->and($this->company->fresh()->onboarding_completed_at->equalTo($startedAt))->toBeTrue();
});

test('expired owners can still open checkout and log out', function () {
    $plan = SubscriptionPlan::create(['name' => 'Team', 'slug' => 'team', 'monthly_price' => 499, 'is_active' => true]);
    $this->get(route('subscription.checkout', $plan))->assertSuccessful();
    $this->post(route('logout'))->assertRedirect();
});

test('expired Livewire update requests are rejected before component actions', function () {
    $this->postJson(route('default-livewire.update'), ['components' => []])->assertForbidden();
});
