<?php

use App\Models\Company;
use App\Models\Tax;
use App\Models\User;
use Livewire\Volt\Volt;

function onboardingCompany(array $attributes = []): Company
{
    return Company::create(array_merge([
        'name' => 'Acme Company',
        'slug' => 'acme-company',
        'email' => 'company@example.com',
    ], $attributes));
}

test('incomplete company owners are redirected to onboarding', function () {
    $company = onboardingCompany();
    $owner = User::factory()->create([
        'company_id' => $company->id,
        'user_type' => 'owner',
    ]);

    $response = $this->actingAs($owner)->get(route('dashboard'));

    $response->assertRedirect(route('company.onboarding', absolute: false));
});

test('onboarding renders with isolated Flux styling', function () {
    $company = onboardingCompany();
    $owner = User::factory()->create([
        'company_id' => $company->id,
        'user_type' => 'owner',
    ]);

    $this->actingAs($owner)->get(route('company.onboarding'))
        ->assertSuccessful()
        ->assertSee('data-company-onboarding', false)
        ->assertSee('Set up your company')
        ->assertSee('Save and continue')
        ->assertDontSee('assets/vendor/css/core.css', false)
        ->assertDontSee('assets/vendor/css/theme-default.css', false);
});

test('owner can complete company onboarding', function () {
    $company = onboardingCompany();
    $owner = User::factory()->create([
        'company_id' => $company->id,
        'user_type' => 'owner',
    ]);

    Volt::actingAs($owner)->test('company.onboarding')
        ->set('name', 'Acme Industries')
        ->set('phone', '1234567890')
        ->set('website', 'https://acme.example.com')
        ->set('address', '1 Main Street')
        ->set('city', 'Lagos')
        ->set('state', 'Lagos')
        ->set('country', 'Nigeria')
        ->set('pincode', '100001')
        ->call('save')
        ->assertRedirect(route('dashboard', absolute: false));

    $company->refresh();
    expect($company->name)->toBe('Acme Industries')
        ->and($company->address)->toBe('1 Main Street')
        ->and($company->onboarding_completed_at)->not->toBeNull();
    expect($company->hasActiveTrial())->toBeTrue()
        ->and($company->trialEndsAt()->equalTo($company->onboarding_completed_at->copy()->addMonthNoOverflow()))->toBeTrue();

    $taxes = Tax::forCompany($company->id)->orderBy('rate')->get();
    expect($taxes->pluck('name')->all())->toBe(['0% GST', '5% GST', '18% GST', '40% GST'])
        ->and($taxes->pluck('rate')->all())->toBe(['0.0000', '5.0000', '18.0000', '40.0000'])
        ->and($taxes->every(fn (Tax $tax): bool => $tax->is_active))->toBeTrue();
});

test('onboarding defaults belong to the company and are not duplicated on repeat saves', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $existingTax = Tax::factory()->create([
        'company_id' => $company->id, 'name' => '18% GST', 'rate' => 18,
    ]);
    Tax::factory()->create([
        'company_id' => $otherCompany->id, 'name' => '5% GST', 'rate' => 5,
    ]);
    $owner = User::factory()->create(['company_id' => $company->id, 'user_type' => 'owner']);

    $component = Volt::actingAs($owner)->test('company.onboarding')
        ->set('address', '1 Main Street')->set('city', 'Mumbai')
        ->set('state', 'Maharashtra')->set('country', 'India')->set('pincode', '400001');
    $component->call('save')->assertHasNoErrors();
    $startedAt = $company->fresh()->onboarding_completed_at;
    $this->travel(1)->days();
    $component->call('save')->assertHasNoErrors();

    expect(Tax::forCompany($company->id)->count())->toBe(4)
        ->and(Tax::forCompany($otherCompany->id)->count())->toBe(1);
    expect($company->fresh()->onboarding_completed_at->equalTo($startedAt))->toBeTrue();
    $this->assertModelExists($existingTax);
});

test('invalid onboarding does not create predefined taxes', function () {
    $company = Company::factory()->create();
    $owner = User::factory()->create(['company_id' => $company->id, 'user_type' => 'owner']);

    Volt::actingAs($owner)->test('company.onboarding')->call('save')->assertHasErrors(['address']);

    expect(Tax::forCompany($company->id)->count())->toBe(0)
        ->and($company->fresh()->onboarding_completed_at)->toBeNull();
});
