<?php

use App\Models\Company;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Livewire\Volt\Volt;

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
    config(['services.upi.id' => 'merchant@example', 'services.upi.payee_name' => 'OneCRM', 'services.upi.usd_to_inr_rate' => '99.99']);
    $this->plan = SubscriptionPlan::create(['name' => 'Public Team', 'slug' => 'public-team', 'monthly_price' => '15.00', 'yearly_price' => '150.00', 'currency' => 'USD', 'max_users' => 5, 'is_active' => true]);
});

test('public home shows active database plans with monthly and yearly checkout links', function () {
    SubscriptionPlan::create(['name' => 'Hidden Plan', 'slug' => 'hidden', 'is_active' => false]);
    $this->get(route('home'))->assertSuccessful()->assertSee('Public Team')
        ->assertSee('USD 15.00')->assertSee('USD 150.00')->assertDontSee('Hidden Plan')
        ->assertSee(route('subscription.checkout', ['plan' => $this->plan, 'cycle' => 'monthly']))
        ->assertSee(route('subscription.checkout', ['plan' => $this->plan, 'cycle' => 'yearly']));
    $this->plan->update(['is_active' => false]);
    $this->get(route('home'))->assertSuccessful()->assertSee('Subscription plans will be available soon.');
});

test('guest selection survives failed login and returns to the chosen checkout', function (string $cycle) {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $owner = User::factory()->for($company)->create(['user_type' => 'owner']);
    $url = route('subscription.checkout', ['plan' => $this->plan, 'cycle' => $cycle]);
    $this->get($url)->assertRedirect(route('login'))->assertSessionHas('url.intended', $url);
    $this->get(route('login'))->assertSuccessful()->assertSee(route('register'));
    $this->post(route('login'), ['email' => $owner->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
    $this->post(route('login'), ['email' => $owner->email, 'password' => 'password'])->assertRedirect($url);
    $this->get($url)->assertSuccessful()->assertViewHas('cycle', $cycle)->assertViewHas('checkoutReady', true);
})->with(['monthly', 'yearly']);

test('selected plan survives signup and company onboarding', function () {
    $url = route('subscription.checkout', ['plan' => $this->plan, 'cycle' => 'yearly']);
    $this->get($url)->assertRedirect(route('login'));
    $this->get(route('register'))->assertSuccessful()->assertSessionHas('url.intended', $url);
    $this->post(route('register'), ['name' => 'New Owner', 'email' => 'new-owner@example.com', 'password' => 'password', 'password_confirmation' => 'password'])
        ->assertRedirect(route('company.onboarding'))->assertSessionHas('url.intended', $url);
    Volt::test('company.onboarding')
        ->set('address', '1 Main Street')->set('city', 'Pune')->set('state', 'Maharashtra')
        ->set('country', 'India')->set('pincode', '411001')->call('save')->assertHasNoErrors()->assertRedirect($url);
    $this->get($url)->assertSuccessful()->assertViewHas('cycle', 'yearly')->assertViewHas('checkoutReady', true);
});

test('existing owner selection survives required onboarding after login', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => null]);
    $owner = User::factory()->for($company)->create(['user_type' => 'owner']);
    $url = route('subscription.checkout', ['plan' => $this->plan, 'cycle' => 'monthly']);
    $this->get($url)->assertRedirect(route('login'));
    $this->post(route('login'), ['email' => $owner->email, 'password' => 'password'])->assertRedirect($url);
    $this->get($url)->assertRedirect(route('company.onboarding'))->assertSessionHas('url.intended', $url);
    Volt::test('company.onboarding')
        ->set('address', '1 Main Street')->set('city', 'Pune')->set('state', 'Maharashtra')
        ->set('country', 'India')->set('pincode', '411001')->call('save')->assertHasNoErrors()->assertRedirect($url);
    $this->get($url)->assertSuccessful()->assertViewHas('checkoutReady', true);
});
