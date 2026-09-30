<?php

use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
    Storage::fake('local');
    config(['services.upi.id' => 'merchant@example', 'services.upi.payee_name' => 'SalesCRM']);
    $this->company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $this->owner = User::factory()->for($this->company)->create(['user_type' => 'owner']);
    $this->plan = SubscriptionPlan::create(['name' => 'Team', 'slug' => 'team', 'monthly_price' => '499.99', 'yearly_price' => '4999.90', 'currency' => 'INR', 'max_users' => 5, 'is_active' => true]);
    $this->actingAs($this->owner);
    $this->proof = fn (): UploadedFile => UploadedFile::fake()->createWithContent('payment.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='));
    $this->checkout = fn (string $cycle = 'monthly'): string => $this->get(route('subscription.checkout', ['plan' => $this->plan, 'cycle' => $cycle]))->assertSuccessful()->viewData('submitUrl');
});

test('upi qr payload uses configured payee and database price instead of request values', function (string $cycle, string $amount) {
    config(['services.upi.id' => 'mynamehere@okaxis', 'services.upi.payee_name' => 'Aniket Golhar']);
    $this->plan->update(['name' => 'Team & Sales + Pro']);
    $response = $this->get(route('subscription.checkout', [
        'plan' => $this->plan, 'cycle' => $cycle, 'am' => '1.00', 'amount' => '1.00',
        'pa' => 'other@example', 'pn' => 'Other', 'tn' => 'Forged note',
    ]))->assertSuccessful()->assertViewHas('checkoutReady', true);
    $url = $response->viewData('upiUrl');
    expect($url)->toStartWith('upi://pay?')->toContain('%20', '%26', '%2B');
    parse_str(parse_url($url, PHP_URL_QUERY), $parameters);
    expect($parameters)->toBe([
        'pa' => 'mynamehere@okaxis', 'pn' => 'Aniket Golhar', 'am' => $amount,
        'cu' => 'INR', 'tn' => 'OneCRM Team & Sales + Pro Subscription',
    ]);
    $response->assertSee('data-upi-url="'.e($url).'"', false)->assertSee('qrcodejs@1.0.0/qrcode.min.js');
})->with([['monthly', '499.99'], ['yearly', '4999.90']]);

test('upi proof activates subscription immediately with separate pending payment status', function () {
    $url = ($this->checkout)();
    $this->post($url, ['utr' => '  upi1234567890  ', 'screenshot' => ($this->proof)(), 'amount' => '1', 'cycle' => 'yearly', 'company_id' => 999, 'status' => 'paid'])
        ->assertSessionHasNoErrors()->assertRedirect();
    $payment = SubscriptionPayment::sole();
    expect($payment->status)->toBe('pending_verification')->and($payment->transaction_id)->toBe('UPI1234567890')
        ->and($payment->company_id)->toBe($this->company->id)->and($payment->amount)->toBe('499.99')
        ->and($payment->paid_at)->toBeNull()->and($payment->subscription->status)->toBe('active')
        ->and($payment->subscription->billing_cycle)->toBe('monthly')->and($payment->subscription->auto_renew)->toBeFalse();
    Storage::disk('local')->assertExists($payment->gateway_response['screenshot_path']);
    $this->get(route('subscription.success', $payment))->assertSuccessful()->assertSee('Your subscription is active!')->assertSee('Pending verification');
    $this->get(route('subscription.screenshot', $payment))->assertSuccessful()->assertHeader('X-Content-Type-Options', 'nosniff');
    $payment->update(['status' => 'failed']);
    expect($payment->subscription->fresh()->status)->toBe('active');
});

test('duplicate utr and repeat checkout cannot extend a subscription twice', function () {
    $url = ($this->checkout)();
    $this->post($url, ['utr' => 'UPI123456', 'screenshot' => ($this->proof)()])->assertSessionHasNoErrors();
    $end = CompanySubscription::sole()->ends_at->toDateTimeString();
    $this->post($url, ['utr' => 'UPI999999', 'screenshot' => ($this->proof)()])->assertSessionHasErrors('utr');
    $this->post(($this->checkout)(), ['utr' => 'upi123456', 'screenshot' => ($this->proof)()])->assertSessionHasErrors('utr');
    expect(SubscriptionPayment::count())->toBe(1)->and(CompanySubscription::sole()->ends_at->toDateTimeString())->toBe($end);
    expect(Storage::disk('local')->allFiles())->toHaveCount(1);

    $payment = SubscriptionPayment::sole();
    expect(fn () => $payment->replicate()->save())->toThrow(UniqueConstraintViolationException::class);
});

test('checkout and payment proofs enforce company ownership and owner access', function () {
    $url = ($this->checkout)();
    $this->post($url, ['utr' => 'UPI123456', 'screenshot' => ($this->proof)()])->assertSessionHasNoErrors();
    $payment = SubscriptionPayment::sole();
    $otherCompany = Company::factory()->create(['onboarding_completed_at' => now()]);
    $otherOwner = User::factory()->for($otherCompany)->create(['user_type' => 'owner']);
    $this->actingAs($otherOwner)->postJson($url, ['utr' => 'UPI999999', 'screenshot' => ($this->proof)()])->assertForbidden();
    $this->getJson(route('subscription.success', $payment))->assertNotFound();
    $this->getJson(route('subscription.screenshot', $payment))->assertNotFound();
    $this->post(($this->checkout)(), ['utr' => 'UPI123456', 'screenshot' => ($this->proof)()])->assertSessionHasErrors('utr');
    $staff = User::factory()->for($this->company)->create(['user_type' => 'staff']);
    $this->actingAs($staff)->getJson(route('subscription.checkout', $this->plan))->assertForbidden();
    $this->postJson($url, ['utr' => 'UPI888888', 'screenshot' => ($this->proof)()])->assertForbidden();
    $this->getJson(route('subscription.screenshot', $payment))->assertForbidden();
    auth()->logout();
    $this->get(route('subscription.checkout', $this->plan))->assertRedirect(route('login'));
});

test('checkout requires payment configuration and an active positive INR price', function () {
    config(['services.upi.id' => null]);
    $this->get(route('subscription.checkout', $this->plan))->assertViewHas('checkoutReady', false)->assertViewHas('submitUrl', null);
    config(['services.upi.id' => 'merchant@example']);
    $this->plan->update(['currency' => 'USD']);
    $this->get(route('subscription.checkout', $this->plan))->assertViewHas('checkoutReady', false)->assertSee('UPI pricing is not available');
    $this->plan->update(['currency' => 'INR', 'monthly_price' => '0.00']);
    $this->get(route('subscription.checkout', $this->plan))->assertViewHas('checkoutReady', false);
    $this->plan->update(['is_active' => false]);
    $this->get(route('subscription.checkout', $this->plan))->assertNotFound();
});

test('tampered expired and stale quotes cannot activate a subscription', function () {
    $url = ($this->checkout)();
    $this->postJson(str_replace('quote_amount=499.99', 'quote_amount=1.00', $url), ['utr' => 'UPI123456', 'screenshot' => ($this->proof)()])->assertForbidden();
    $this->plan->update(['monthly_price' => '599.99']);
    $this->post($url, ['utr' => 'UPI123456', 'screenshot' => ($this->proof)()])->assertSessionHasErrors('plan');
    $this->travel(2)->days();
    $this->postJson($url, ['utr' => 'UPI123456', 'screenshot' => ($this->proof)()])->assertForbidden();
    expect(CompanySubscription::count())->toBe(0)->and(SubscriptionPayment::count())->toBe(0);
    expect(Storage::disk('local')->allFiles())->toBeEmpty();
});

test('utr and a valid bounded screenshot are required', function () {
    $url = ($this->checkout)();
    $this->post($url, [])->assertSessionHasErrors(['utr', 'screenshot']);
    $this->post($url, ['utr' => 'bad!', 'screenshot' => UploadedFile::fake()->create('proof.svg', 1, 'image/svg+xml')])->assertSessionHasErrors(['utr', 'screenshot']);
    $this->post($url, ['utr' => 'UPI123456', 'screenshot' => ($this->proof)()->size(5121)])->assertSessionHasErrors('screenshot');
    expect(SubscriptionPayment::count())->toBe(0)->and(CompanySubscription::count())->toBe(0);
});

test('same plan renewal extends expiry and switching plans starts a new period', function () {
    $this->travelTo(now()->startOfDay());
    $oldEnd = now()->addDays(10);
    $current = CompanySubscription::create(['company_id' => $this->company->id, 'plan_id' => $this->plan->id, 'billing_cycle' => 'monthly', 'amount' => '499.99', 'currency' => 'INR', 'status' => 'active', 'starts_at' => now()->subDays(20), 'ends_at' => $oldEnd]);
    $this->post(($this->checkout)('yearly'), ['utr' => 'UPI123456', 'screenshot' => ($this->proof)()])->assertSessionHasNoErrors();
    expect($current->fresh()->ends_at->toDateTimeString())->toBe($oldEnd->copy()->addYearNoOverflow()->toDateTimeString());
    expect(SubscriptionPayment::sole()->amount)->toBe('4999.90');
    $this->plan = SubscriptionPlan::create(['name' => 'Larger', 'slug' => 'larger', 'monthly_price' => '799.99', 'yearly_price' => '7999.90', 'currency' => 'INR', 'max_users' => 10, 'is_active' => true]);
    $this->post(($this->checkout)(), ['utr' => 'UPI999999', 'screenshot' => ($this->proof)()])->assertSessionHasNoErrors();
    expect($current->fresh()->status)->toBe('cancelled');
    $active = CompanySubscription::where('status', 'active')->sole();
    expect($active->plan_id)->toBe($this->plan->id)->and($active->starts_at->toDateTimeString())->toBe(now()->toDateTimeString())
        ->and($active->ends_at->toDateTimeString())->toBe(now()->addMonthNoOverflow()->toDateTimeString());
});

test('failed payment persistence rolls back activation and deletes the screenshot', function () {
    $url = ($this->checkout)();
    $event = 'eloquent.creating: '.SubscriptionPayment::class;
    Event::listen($event, function (): void {
        throw new RuntimeException('Payment insert failed');
    });
    try {
        $this->post($url, ['utr' => 'UPI123456', 'screenshot' => ($this->proof)()])->assertServerError();
    } finally {
        Event::forget($event);
    }
    expect(CompanySubscription::count())->toBe(0)->and(SubscriptionPayment::count())->toBe(0);
    expect(Storage::disk('local')->allFiles())->toBeEmpty();
});
