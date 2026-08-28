<?php

use App\Http\Middleware\EnsureCompanyOnboardingComplete;
use App\Models\Company;
use App\Models\Integration;
use App\Models\User;

beforeEach(function () {
    $this->withoutMiddleware(EnsureCompanyOnboardingComplete::class);
});

test('a company user can generate a webhook token that is only displayed once', function () {
    $company = Company::create(['name' => 'Acme', 'slug' => 'acme']);
    $user = User::factory()->create(['company_id' => $company->id]);
    $previousToken = Integration::create([
        'company_id' => $company->id,
        'name' => 'Webhook API Token',
        'type' => 'webhook',
        'api_key' => hash('sha256', 'old-token'),
        'configuration' => ['token_preview' => 'crm_old***token'],
        'status' => true,
    ]);

    $response = $this->actingAs($user)->post(route('integration-api-token.store'));

    $response->assertRedirect(route('integration-api-token', absolute: false))
        ->assertSessionHas('api_token');
    $plainToken = $response->getSession()->get('api_token');
    $newToken = Integration::query()->where('company_id', $company->id)->latest('id')->firstOrFail();

    expect($plainToken)->toStartWith('crm_')
        ->and($newToken->status)->toBeTrue()
        ->and($newToken->api_key)->toBe(hash('sha256', $plainToken))
        ->and(data_get($newToken->configuration, 'token_preview'))->not->toBe($plainToken)
        ->and($previousToken->fresh()->status)->toBeFalse();
    $this->assertDatabaseMissing('integrations', ['api_key' => $plainToken]);

    $this->actingAs($user)->get(route('integration-api-token'))
        ->assertSuccessful()
        ->assertSee($plainToken);
    $this->actingAs($user)->get(route('integration-api-token'))
        ->assertSuccessful()
        ->assertDontSee($plainToken)
        ->assertSee(data_get($newToken->configuration, 'token_preview'));
});

test('a company user only sees their company webhook tokens', function () {
    $company = Company::create(['name' => 'Acme', 'slug' => 'acme']);
    $otherCompany = Company::create(['name' => 'Other', 'slug' => 'other']);
    $user = User::factory()->create(['company_id' => $company->id]);

    Integration::create([
        'company_id' => $company->id,
        'name' => 'Webhook API Token',
        'type' => 'webhook',
        'configuration' => ['token_preview' => 'crm_own***token'],
        'status' => true,
    ]);
    Integration::create([
        'company_id' => $otherCompany->id,
        'name' => 'Webhook API Token',
        'type' => 'webhook',
        'configuration' => ['token_preview' => 'crm_other***token'],
        'status' => true,
    ]);

    $this->actingAs($user)->get(route('integration-api-token'))
        ->assertSuccessful()
        ->assertSee('crm_own***token')
        ->assertDontSee('crm_other***token');
});
