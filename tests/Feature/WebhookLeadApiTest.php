<?php

use App\Models\Attendance;
use App\Models\Company;
use App\Models\Integration;
use App\Models\Lead;
use App\Models\User;
use Carbon\CarbonImmutable;

/** @return array{0: Company, 1: array<string, string>} */
function webhookAssignmentCompany(): array
{
    $company = Company::factory()->create();
    $token = 'assignment-test-token';
    Integration::create([
        'company_id' => $company->id, 'name' => 'Webhook', 'type' => 'webhook',
        'api_key' => hash('sha256', $token), 'status' => true,
    ]);

    return [$company, ['Authorization' => 'Bearer '.$token]];
}

test('webhook leads are evenly distributed in random rounds among present staff including breaks', function () {
    [$company, $headers] = webhookAssignmentCompany();
    $staff = User::factory()->count(3)->for($company)->create(['user_type' => 'staff']);
    foreach ($staff as $index => $user) {
        Attendance::factory()->for($user)->create([
            'company_id' => $company->id,
            'break_in' => $index === 0 ? now() : null,
        ]);
    }
    $round = [];
    for ($index = 0; $index < 12; $index++) {
        $response = $this->postJson(route('webhook.v1.lead.create'), ['name' => 'Incoming '.$index], $headers)->assertCreated();
        $round[] = $response->json('data.assigned_to');
        $counts = Lead::where('company_id', $company->id)->select('assigned_to')->selectRaw('COUNT(*) as total')->groupBy('assigned_to')->pluck('total', 'assigned_to');
        $totals = $staff->map(fn (User $user): int => (int) ($counts[$user->id] ?? 0));
        expect($totals->max() - $totals->min())->toBeLessThanOrEqual(1);
        if (count($round) === 3) {
            expect($round)->toHaveCount(3)->and(array_unique($round))->toHaveCount(3);
            $round = [];
        }
    }
    expect($totals->all())->toBe([4, 4, 4]);
});

test('webhook ignores supplied assignees and excludes absent inactive owners and other companies', function () {
    [$company, $headers] = webhookAssignmentCompany();
    $present = User::factory()->for($company)->create(['user_type' => 'staff']);
    $absent = User::factory()->for($company)->create(['user_type' => 'staff']);
    $inactive = User::factory()->for($company)->create(['user_type' => 'staff', 'is_active' => false]);
    $owner = User::factory()->for($company)->create(['user_type' => 'owner']);
    foreach ([$present, $inactive, $owner] as $user) {
        Attendance::factory()->for($user)->create(['company_id' => $company->id]);
    }
    $foreign = Attendance::factory()->create();
    foreach ([$absent->id, $foreign->user_id, 999999] as $suppliedId) {
        $this->postJson(route('webhook.v1.lead.create'), ['name' => 'Automatic assignment', 'assigned_to' => $suppliedId], $headers)
            ->assertCreated()->assertJsonPath('data.assigned_to', $present->id);
    }
    Attendance::where('user_id', $present->id)->update(['punch_out' => now()]);
    $this->postJson(route('webhook.v1.lead.create'), ['name' => 'Nobody present', 'assigned_to' => $present->id], $headers)
        ->assertCreated()->assertJsonPath('data.assigned_to', null);
});

test('webhook balances todays workload using the attendance timezone and excludes stale punches', function () {
    config(['attendance.timezone' => 'Asia/Kolkata']);
    $this->travelTo(CarbonImmutable::parse('2026-09-25 19:00:00', 'UTC'));
    [$company, $headers] = webhookAssignmentCompany();
    $busy = User::factory()->for($company)->create(['user_type' => 'staff']);
    $available = User::factory()->for($company)->create(['user_type' => 'staff']);
    $stale = User::factory()->for($company)->create(['user_type' => 'staff']);
    foreach ([$busy, $available] as $user) {
        Attendance::factory()->for($user)->create(['company_id' => $company->id]);
    }
    Attendance::factory()->for($stale)->create(['company_id' => $company->id, 'date' => '2026-09-25', 'punch_in' => now()->subDay()]);
    Lead::factory()->count(2)->for($company)->create(['assigned_to' => $busy->id]);
    Lead::factory()->count(5)->for($company)->create(['assigned_to' => $available->id, 'created_at' => now()->subDay()]);
    for ($index = 0; $index < 2; $index++) {
        $this->postJson(route('webhook.v1.lead.create'), ['name' => 'Balance '.$index], $headers)
            ->assertCreated()->assertJsonPath('data.assigned_to', $available->id);
    }
});

test('webhook leaves lead unassigned when there is no attendance', function () {
    [$company, $headers] = webhookAssignmentCompany();
    User::factory()->for($company)->create(['user_type' => 'staff']);
    $this->postJson(route('webhook.v1.lead.create'), ['name' => 'Unassigned lead'], $headers)
        ->assertCreated()->assertJsonPath('data.assigned_to', null);
});

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

    $this->assertDatabaseHas('lead_activities', [
        'company_id' => $company->id,
        'lead_id' => $response->json('data.id'),
        'activity_type' => 'notes',
        'subject' => 'Lead created via Webhook API',
        'status' => 'completed',
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

test('webhook lead creation rejects a duplicate email or mobile in the same company', function () {
    $company = Company::create(['name' => 'Duplicate Corp', 'slug' => 'duplicate-corp']);
    $token = 'crm_duplicate_token_test_123';
    Integration::create([
        'company_id' => $company->id,
        'name' => 'Webhook API Token',
        'type' => 'webhook',
        'api_key' => hash('sha256', $token),
        'status' => true,
    ]);
    $payload = [
        'name' => 'Duplicate Lead',
        'email' => 'duplicate@example.com',
        'mobile' => '+1234567890',
    ];

    $this->postJson(route('webhook.v1.lead.create'), $payload, [
        'Authorization' => 'Bearer '.$token,
    ])->assertCreated();

    $duplicateResponse = $this->postJson(route('webhook.v1.lead.create'), $payload, [
        'Authorization' => 'Bearer '.$token,
    ]);

    $duplicateResponse->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Duplicate lead.',
        ])
        ->assertJsonPath('errors.duplicate.0', 'A lead with the same email or mobile already exists.');

    expect(Lead::query()->where('company_id', $company->id)->count())->toBe(1);
    $this->assertDatabaseHas('webhook_logs', [
        'company_id' => $company->id,
        'event' => 'lead.create',
        'status_code' => 422,
        'status' => 'failed',
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
