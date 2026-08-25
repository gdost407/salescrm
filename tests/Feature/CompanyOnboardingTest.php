<?php

use App\Models\Company;
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
});
