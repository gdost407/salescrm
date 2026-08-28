<?php

use App\Models\Company;
use App\Models\Integration;

test('webhook lead creation fails when token is missing', function () {
    $response = $this->postJson(route('webhook.v1.lead.create'), [
        'name' => 'John Doe',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Unauthenticated. API token is missing.',
        ]);
});

test('webhook lead creation fails when token is invalid', function () {
    $response = $this->postJson(route('webhook.v1.lead.create'), [
        'name' => 'John Doe',
    ], [
        'Authorization' => 'Bearer crm_invalid_token_123',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Unauthenticated. Invalid or expired API token.',
        ]);
});

test('webhook lead creation fails when token is expired/inactive', function () {
    $company = Company::create(['name' => 'Test Co', 'slug' => 'test-co']);
    $token = 'crm_'.str_repeat('a', 64);
    Integration::create([
        'company_id' => $company->id,
        'name' => 'Webhook API Token',
        'type' => 'webhook',
        'api_key' => hash('sha256', $token),
        'status' => false,
    ]);

    $response = $this->postJson(route('webhook.v1.lead.create'), [
        'name' => 'John Doe',
    ], [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertStatus(401);
});

test('webhook lead creation creates lead successfully with valid token in bearer header', function () {
    $company = Company::create(['name' => 'Acme Corp', 'slug' => 'acme-corp']);
    $token = 'crm_valid_token_test_1234567890';
    $integration = Integration::create([
        'company_id' => $company->id,
        'name' => 'Webhook API Token',
        'type' => 'webhook',
        'api_key' => hash('sha256', $token),
        'status' => true,
    ]);

    $payload = [
        'name' => 'Alice Smith',
        'email' => 'alice@example.com',
        'mobile' => '+1234567890',
        'company_name' => 'Initech',
        'deal_amount' => 15000,
        'source' => 'Website Webhook',
    ];

    $response = $this->postJson(route('webhook.v1.lead.create'), $payload, [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Lead created successfully via webhook.',
            'data' => [
                'name' => 'Alice Smith',
                'email' => 'alice@example.com',
                'mobile' => '+1234567890',
                'company_name' => 'Initech',
                'deal_amount' => 15000,
                'source' => 'Website Webhook',
            ],
        ]);

    $this->assertDatabaseHas('leads', [
        'company_id' => $company->id,
        'name' => 'Alice Smith',
        'email' => 'alice@example.com',
        'source' => 'Website Webhook',
    ]);

    $this->assertDatabaseHas('webhook_logs', [
        'company_id' => $company->id,
        'integration_id' => $integration->id,
        'event' => 'lead.create',
        'status_code' => 201,
        'status' => 'success',
    ]);
});

test('webhook lead creation accepts token via X-API-Token header', function () {
    $company = Company::create(['name' => 'Beta Corp', 'slug' => 'beta-corp']);
    $token = 'crm_header_token_test_123';
    Integration::create([
        'company_id' => $company->id,
        'name' => 'Webhook API Token',
        'type' => 'webhook',
        'api_key' => hash('sha256', $token),
        'status' => true,
    ]);

    $response = $this->postJson(route('webhook.v1.lead.create'), [
        'name' => 'Bob Johnson',
    ], [
        'X-API-Token' => $token,
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('leads', [
        'company_id' => $company->id,
        'name' => 'Bob Johnson',
    ]);
});

test('webhook lead creation returns validation errors for missing name', function () {
    $company = Company::create(['name' => 'Gamma Corp', 'slug' => 'gamma-corp']);
    $token = 'crm_val_token_test_123';
    $integration = Integration::create([
        'company_id' => $company->id,
        'name' => 'Webhook API Token',
        'type' => 'webhook',
        'api_key' => hash('sha256', $token),
        'status' => true,
    ]);

    $response = $this->postJson(route('webhook.v1.lead.create'), [
        'email' => 'invalid-email-format',
    ], [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Validation failed.',
        ]);

    $this->assertDatabaseHas('webhook_logs', [
        'company_id' => $company->id,
        'integration_id' => $integration->id,
        'event' => 'lead.create',
        'status_code' => 422,
        'status' => 'failed',
    ]);
});
