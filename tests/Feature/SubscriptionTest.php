<?php

use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Models\User;

test('subscription page shows database prices and only the company payment history', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $owner = User::factory()->for($company)->create(['user_type' => 'owner']);
    $plan = SubscriptionPlan::create(['name' => 'Team Plan', 'slug' => 'team', 'monthly_price' => '499.99', 'yearly_price' => '4999.90', 'currency' => 'INR', 'max_users' => 5, 'is_active' => true]);
    SubscriptionPlan::create(['name' => 'Hidden Plan', 'slug' => 'hidden', 'is_active' => false]);
    $subscription = CompanySubscription::create(['company_id' => $company->id, 'plan_id' => $plan->id, 'billing_cycle' => 'monthly', 'amount' => '499.99', 'currency' => 'INR', 'status' => 'active', 'starts_at' => now(), 'ends_at' => now()->addMonth()]);
    SubscriptionPayment::create(['company_id' => $company->id, 'subscription_id' => $subscription->id, 'plan_id' => $plan->id, 'amount' => '499.99', 'currency' => 'INR', 'gateway' => 'razorpay', 'transaction_id' => 'pay_own', 'status' => 'paid']);
    $otherCompany = Company::factory()->create();
    $otherSubscription = CompanySubscription::create(['company_id' => $otherCompany->id, 'plan_id' => $plan->id, 'billing_cycle' => 'monthly', 'amount' => '499.99', 'status' => 'active']);
    SubscriptionPayment::create(['company_id' => $otherCompany->id, 'subscription_id' => $otherSubscription->id, 'plan_id' => $plan->id, 'amount' => '499.99', 'gateway' => 'razorpay', 'transaction_id' => 'pay_other_company', 'status' => 'paid']);

    $this->actingAs($owner)->get(route('subscription.index', ['company_id' => $otherCompany->id]))
        ->assertSuccessful()->assertSee('Team Plan')->assertSee('499.99')->assertSee('4999.90')
        ->assertSee('pay_own')->assertDontSee('pay_other_company')->assertDontSee('Hidden Plan');
    $subscription->update(['ends_at' => now()->subDay()]);
    $this->get(route('subscription.index'))->assertSuccessful()->assertSee('No active subscription');
});

test('subscription page handles empty plans and denies guests and staff', function () {
    $this->get(route('subscription.index'))->assertRedirect(route('login'));
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $staff = User::factory()->for($company)->create(['user_type' => 'staff']);
    $this->actingAs($staff)->getJson(route('subscription.index'))->assertForbidden();
    $owner = User::factory()->for($company)->create(['user_type' => 'owner']);
    $this->actingAs($owner)->get(route('subscription.index'))->assertSuccessful()->assertSee('No plans available yet')->assertSee('No payments yet.');
});
