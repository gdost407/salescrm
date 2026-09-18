<?php

use App\Actions\SaveLead;
use App\Models\Client;
use App\Models\Company;
use App\Models\Integration;
use App\Models\Lead;
use App\Models\LeadSetting;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
    $this->company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $this->owner = User::factory()->for($this->company)->create(['user_type' => 'owner']);
    LeadSetting::create(['setting_type' => 'status', 'name' => 'Converted', 'type' => 'system', 'is_active' => true]);
});

test('kanban conversion copies client data and preserves history and assignee', function () {
    $lead = Lead::factory()->for($this->company)->create([
        'created_by' => $this->owner->id, 'assigned_to' => $this->owner->id,
        'alternate_mobile' => '9876543210', 'job_title' => 'Manager', 'address' => '12 Main Road',
    ]);
    $this->actingAs($this->owner)->patchJson(route('sales-leads.status', $lead), ['status' => 'Converted'])->assertSuccessful();
    $lead->refresh();
    expect($lead->client)->not->toBeNull()
        ->and($lead->client->company_id)->toBe($this->company->id)
        ->and($lead->client->lead_id)->toBe($lead->id)
        ->and($lead->client->phone)->toBe($lead->mobile)
        ->and($lead->client->alternate_phone)->toBe('9876543210')
        ->and($lead->client->designation)->toBe('Manager')
        ->and($lead->client->address)->toBe('12 Main Road')
        ->and($lead->converted_at)->not->toBeNull()
        ->and($lead->assigned_to)->toBe($this->owner->id)
        ->and($lead->activities()->where('subject', 'Status changed')->count())->toBe(1);
    $convertedAt = $lead->converted_at->toDateTimeString();
    $lead->client->update(['name' => 'Client maintained separately']);
    $this->actingAs($this->owner)->patchJson(route('sales-leads.status', $lead), ['status' => 'Converted'])->assertSuccessful();
    expect(Client::count())->toBe(1)
        ->and($lead->fresh()->client->name)->toBe('Client maintained separately')
        ->and($lead->fresh()->converted_at->toDateTimeString())->toBe($convertedAt);
});

test('converted leads are linked on form creation and edit', function (bool $editing) {
    foreach (['stage' => 'New', 'source' => 'Direct'] as $type => $name) {
        LeadSetting::create(['setting_type' => $type, 'name' => $name, 'type' => 'system', 'is_active' => true]);
    }
    $data = ['name' => 'Converted Buyer', 'email' => 'buyer@example.com', 'mobile' => '1234567890', 'status' => 'Converted', 'stage' => 'New', 'source' => 'Direct'];
    if ($editing) {
        $lead = Lead::factory()->for($this->company)->create();
        $this->actingAs($this->owner)->put(route('sales-leads.update', $lead), $data)->assertSessionHasNoErrors()->assertRedirect();
    } else {
        $this->actingAs($this->owner)->post(route('sales-leads.store'), $data)->assertSessionHasNoErrors()->assertRedirect();
    }
    expect(Lead::where('name', 'Converted Buyer')->firstOrFail()->client->name)->toBe('Converted Buyer');
})->with([false, true]);

test('conversion reuses the client across stale requests and status reversals', function () {
    $lead = Lead::factory()->for($this->company)->create();
    $staleLead = $lead->fresh();
    $save = app(SaveLead::class);
    $converted = $save->handle(['status' => 'Converted'], $lead);
    $save->handle(['status' => 'Converted'], $staleLead);
    $save->handle(['status' => 'Open'], $converted);
    $again = $save->handle(['status' => 'Converted'], $staleLead);
    expect(Client::count())->toBe(1)->and($again->client_id)->toBe($converted->client_id);
});

test('conversion rolls back when a client cannot be saved', function () {
    $lead = Lead::factory()->for($this->company)->create();
    $dispatcher = Client::getEventDispatcher();
    $isolated = clone $dispatcher;
    Client::setEventDispatcher($isolated);
    $isolated->listen('eloquent.creating: '.Client::class, function () {
        throw new RuntimeException('Storage unavailable');
    });
    try {
        expect(fn () => app(SaveLead::class)->handle(['status' => 'Converted'], $lead))->toThrow(RuntimeException::class);
    } finally {
        Client::setEventDispatcher($dispatcher);
    }
    expect($lead->fresh()->status)->toBe('Open')->and(Client::count())->toBe(0);
});

test('conversion rejects foreign-company links and unauthorized lead access', function () {
    $foreignClient = Client::factory()->create();
    $lead = Lead::factory()->for($this->company)->create(['client_id' => $foreignClient->id]);
    expect(fn () => app(SaveLead::class)->handle(['status' => 'Converted'], $lead))->toThrow(ValidationException::class);
    expect($lead->fresh()->status)->toBe('Open');
    $foreignLead = Lead::factory()->create();
    $this->actingAs($this->owner)->patchJson(route('sales-leads.status', $foreignLead), ['status' => 'Converted'])->assertNotFound();
    $staff = User::factory()->for($this->company)->create(['user_type' => 'staff']);
    $this->actingAs($staff)->patchJson(route('sales-leads.status', $lead), ['status' => 'Converted'])->assertForbidden();
});

test('converted leads received through imports and webhooks create clients', function () {
    foreach (['stage' => 'New', 'source' => 'Direct'] as $type => $name) {
        LeadSetting::create(['setting_type' => $type, 'name' => $name, 'type' => 'system', 'is_active' => true]);
    }
    $csv = "name,email,mobile,status,stage,source\nImported Buyer,imported@example.com,1234567890,Converted,New,Direct\n";
    $this->actingAs($this->owner)->post(route('sales-leads.import'), ['file' => UploadedFile::fake()->createWithContent('leads.csv', $csv)])
        ->assertSuccessful()->assertViewHas('importedCount', 1);
    expect(Lead::where('email', 'imported@example.com')->firstOrFail()->client)->not->toBeNull();

    $token = 'crm_conversion_test_token';
    Integration::create(['company_id' => $this->company->id, 'name' => 'Webhook', 'type' => 'webhook', 'api_key' => hash('sha256', $token), 'status' => true]);
    $this->postJson(route('webhook.v1.lead.create'), ['name' => 'Webhook Buyer', 'email' => 'webhook@example.com', 'status' => 'Converted'], ['Authorization' => 'Bearer '.$token])
        ->assertCreated();
    expect(Lead::where('email', 'webhook@example.com')->firstOrFail()->client->name)->toBe('Webhook Buyer');
});
