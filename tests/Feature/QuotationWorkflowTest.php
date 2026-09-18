<?php

use App\Actions\SaveQuotation;
use App\Models\Item;
use App\Models\Client;
use App\Models\Company;
use App\Models\Permission;
use App\Models\TxnQuotation;
use App\Models\TxnHistoryItem;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
    $this->company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $this->owner = User::factory()->for($this->company)->create(['user_type' => 'owner']);
    $this->client = Client::factory()->for($this->company)->create();
    $this->service = Item::factory()->create(['company_id' => $this->company->id]);
    $this->inventory = Item::factory()->create(['company_id' => $this->company->id, 'type' => 'inventory', 'rate' => '118.00']);
    $this->data = [
        'client_id' => $this->client->id, 'quotation_date' => '2026-09-18', 'valid_until' => '2026-10-18',
        'notes' => 'Thank you', 'terms' => 'Valid for 30 days.',
        'items' => [['catalog_item_id' => $this->service->id, 'quantity' => '2'], ['catalog_item_id' => $this->inventory->id, 'quantity' => '1']],
    ];
    $this->actingAs($this->owner);
});

test('catalogue supports CRUD and private image replacement', function () {
    Storage::fake('local');
    $data = ['type' => 'service', 'name' => 'Consulting', 'hsn' => '998313', 'gst_rate' => '18', 'rate' => '100', 'image' => UploadedFile::fake()->image('item.png')];
    $this->get(route('catalog-items.create'))->assertSuccessful();
    $this->post(route('catalog-items.store'), $data)->assertSessionHasNoErrors()->assertRedirect();
    $item = Item::where('name', 'Consulting')->firstOrFail();
    expect($item->company_id)->toBe($this->company->id);
    Storage::disk('local')->assertExists($item->image_path);
    $this->get(route('catalog-items.image', $item))->assertSuccessful()->assertHeader('x-content-type-options', 'nosniff');
    $this->get(route('catalog-items.index'))->assertSuccessful()->assertSee('Consulting');
    $this->get(route('catalog-items.show', $item))->assertSuccessful()->assertSee('GST exclusive');
    $this->get(route('catalog-items.edit', $item))->assertSuccessful();
    $oldPath = $item->image_path;
    $data['image'] = UploadedFile::fake()->image('new.png');
    $data['type'] = 'inventory';
    $this->put(route('catalog-items.update', $item), $data)->assertSessionHasNoErrors()->assertRedirect();
    Storage::disk('local')->assertMissing($oldPath);
    Storage::disk('local')->assertExists($item->fresh()->image_path);
    $newPath = $item->fresh()->image_path;
    unset($data['image']);
    $this->put(route('catalog-items.update', $item), $data + ['remove_image' => 1])->assertSessionHasNoErrors();
    Storage::disk('local')->assertMissing($newPath);
    expect($item->fresh()->image_path)->toBeNull();
    $this->delete(route('catalog-items.destroy', $item))->assertRedirect();
    $this->assertSoftDeleted($item);
});

test('catalogue validates images and monetary inputs', function () {
    $this->postJson(route('catalog-items.store'), [
        'type' => 'unknown', 'name' => '', 'hsn' => '', 'gst_rate' => '101', 'rate' => '-1',
        'image' => UploadedFile::fake()->create('script.php', 10, 'application/x-php'),
    ])->assertUnprocessable()->assertJsonValidationErrors(['type', 'name', 'hsn', 'gst_rate', 'rate', 'image']);
});

test('quotation submits mixed items and ignores browser totals', function () {
    $this->get(route('quotations.create'))->assertSuccessful()->assertSee($this->client->name);
    $this->post(route('quotations.store'), $this->data + ['total' => '0.01', 'subtotal' => '0.01', 'tax_total' => '0.00', 'company_id' => 999])
        ->assertSessionHasNoErrors()->assertRedirect();
    $quotation = TxnQuotation::firstOrFail();
    expect($quotation->company_id)->toBe($this->company->id)
        ->and($quotation->subtotal)->toBe('300.00')
        ->and($quotation->tax_total)->toBe('54.00')
        ->and($quotation->total)->toBe('354.00')
        ->and($quotation->items)->toHaveCount(2);
    $this->get(route('quotations.index'))->assertSuccessful()->assertSee($quotation->number());
    $this->get(route('quotations.edit', $quotation))->assertSuccessful()->assertSee('Edit '.$quotation->number());
    $this->get(route('quotations.show', $quotation))->assertSuccessful()->assertSee('354.00')->assertSee('Print / Save PDF')->assertSee('Authorized signatory');
});

test('quotation edits preserve price snapshots and allow deleted catalogue rows already quoted', function () {
    $this->post(route('quotations.store'), $this->data)->assertSessionHasNoErrors();
    $quotation = TxnQuotation::firstOrFail();
    $clientName = $this->client->name;
    $this->client->update(['name' => 'Changed client name']);
    $this->service->update(['rate' => '999.00', 'gst_rate' => '5.00', 'name' => 'Changed item']);
    $this->inventory->delete();
    $this->data['items'] = $quotation->items->map(fn ($item) => ['quotation_item_id' => $item->id, 'catalog_item_id' => $item->catalog_item_id, 'quantity' => '3'])->all();
    $this->put(route('quotations.update', $quotation), $this->data)->assertSessionHasNoErrors()->assertRedirect();
    $quotation->refresh();
    expect($quotation->total)->toBe('708.00')->and($quotation->items->first()->rate)->toBe('100.00')
        ->and($quotation->client_details['name'])->toBe($clientName);
    $this->get(route('quotations.edit', $quotation))->assertSuccessful();
    $this->get(route('quotations.show', $quotation))->assertSuccessful()->assertDontSee('Changed item');
    $this->data['items'] = [['catalog_item_id' => $this->service->id, 'quantity' => '1']];
    $this->put(route('quotations.update', $quotation), $this->data)->assertSessionHasNoErrors();
    expect($quotation->fresh()->total)->toBe('1048.95')->and($quotation->fresh()->items)->toHaveCount(1);
});

test('quotations reject foreign clients items and forged saved rows without changing data', function () {
    $this->post(route('quotations.store'), $this->data)->assertSessionHasNoErrors();
    $quotation = TxnQuotation::firstOrFail();
    $foreignClient = Client::factory()->create();
    $foreignItem = Item::factory()->create();
    $foreignQuotation = TxnQuotation::factory()->create();
    $foreignLine = $foreignQuotation->items()->create(['catalog_item_id' => $foreignItem->id, 'type' => 'service', 'name' => 'Private', 'hsn' => '998313', 'quantity' => 1, 'rate' => 1, 'gst_rate' => 0, 'subtotal' => 1, 'tax_total' => 0, 'total' => 1]);
    $this->data['client_id'] = $foreignClient->id;
    $this->data['items'] = [['catalog_item_id' => $foreignItem->id, 'quotation_item_id' => $foreignLine->id, 'quantity' => 1]];
    $this->putJson(route('quotations.update', $quotation), $this->data)->assertUnprocessable()
        ->assertJsonValidationErrors(['client_id', 'items.0.catalog_item_id', 'items.0.quotation_item_id']);
    expect($quotation->fresh()->total)->toBe('354.00')->and($quotation->fresh()->items)->toHaveCount(2);
    foreach (['show', 'edit'] as $action) {
        $this->get(route('quotations.'.$action, $foreignQuotation))->assertNotFound();
        $this->get(route('catalog-items.'.$action, $foreignItem))->assertNotFound();
    }
    $this->putJson(route('quotations.update', $foreignQuotation), $this->data)->assertNotFound();
    $this->putJson(route('catalog-items.update', $foreignItem), [])->assertNotFound();
    $this->deleteJson(route('catalog-items.destroy', $foreignItem))->assertNotFound();
    $this->get(route('catalog-items.image', $foreignItem))->assertNotFound();
    $this->get(route('catalog-items.index'))->assertDontSee($foreignItem->name);
    $this->get(route('quotations.index'))->assertDontSee($foreignQuotation->number());
    $this->get(route('quotations.create'))->assertDontSee($foreignClient->name)->assertDontSee($foreignItem->name);
});

test('quotation validates quantities dates and minimum items', function () {
    $data = $this->data;
    $data['valid_until'] = '2026-09-01';
    $data['items'][0]['quantity'] = '0';
    $data['items'][1]['quantity'] = '1.0001';
    $this->postJson(route('quotations.store'), $data)->assertUnprocessable()->assertJsonValidationErrors(['valid_until', 'items.0.quantity', 'items.1.quantity']);
    $data['items'] = [];
    $this->postJson(route('quotations.store'), $data)->assertUnprocessable()->assertJsonValidationErrors(['items']);
    expect(TxnQuotation::count())->toBe(0);
});

test('new quotations cannot use deleted catalogue items', function () {
    $this->inventory->delete();
    $this->postJson(route('quotations.store'), $this->data)->assertUnprocessable()->assertJsonValidationErrors(['items.1.catalog_item_id']);
    expect(TxnQuotation::count())->toBe(0);
});

test('a failed quotation edit rolls back totals and line replacement', function () {
    $this->post(route('quotations.store'), $this->data)->assertSessionHasNoErrors();
    $quotation = TxnQuotation::firstOrFail();
    $oldIds = $quotation->items->modelKeys();
    $dispatcher = TxnHistoryItem::getEventDispatcher();
    $isolated = clone $dispatcher;
    TxnHistoryItem::setEventDispatcher($isolated);
    $isolated->listen('eloquent.creating: '.TxnHistoryItem::class, function () {
        throw new RuntimeException('Unable to save quotation line');
    });
    try {
        $this->data['items'][0]['quantity'] = 3;
        expect(fn () => app(SaveQuotation::class)->handle($this->owner, $this->data, $quotation))->toThrow(RuntimeException::class);
    } finally {
        TxnHistoryItem::setEventDispatcher($dispatcher);
    }
    expect($quotation->fresh()->total)->toBe('354.00')->and($quotation->fresh()->items->modelKeys())->toBe($oldIds);
});

test('new modules enforce staff permissions on backend', function () {
    $staff = User::factory()->for($this->company)->create(['user_type' => 'staff']);
    $quotation = TxnQuotation::factory()->create(['client_id' => $this->client->id]);
    $this->actingAs($staff);
    foreach (['catalog-items.index', 'catalog-items.create', 'quotations.index', 'quotations.create'] as $route) {
        $this->getJson(route($route))->assertForbidden();
    }
    foreach (['show', 'edit'] as $action) {
        $this->getJson(route('catalog-items.'.$action, $this->service))->assertForbidden();
        $this->getJson(route('quotations.'.$action, $quotation))->assertForbidden();
    }
    $this->postJson(route('catalog-items.store'), [])->assertForbidden();
    $this->putJson(route('catalog-items.update', $this->service), [])->assertForbidden();
    $this->deleteJson(route('catalog-items.destroy', $this->service))->assertForbidden();
    $this->getJson(route('catalog-items.image', $this->service))->assertForbidden();
    $this->postJson(route('quotations.store'), $this->data)->assertForbidden();
    $this->putJson(route('quotations.update', $quotation), $this->data)->assertForbidden();

    app(RolePermissionSeeder::class)->seedCompany($this->company->id);
    $role = Role::create(['company_id' => $this->company->id, 'name' => 'TxnQuotation creator', 'slug' => 'quotation-creator', 'status' => true]);
    $role->permissions()->sync(Permission::where('company_id', $this->company->id)->where('slug', 'create_quotations')->pluck('id'));
    $staff->update(['role_id' => $role->id]);
    $this->actingAs($staff->fresh());
    $this->post(route('quotations.store'), $this->data)->assertSessionHasNoErrors()->assertRedirect(route('quotations.create'));
    $this->getJson(route('quotations.index'))->assertForbidden();
});
