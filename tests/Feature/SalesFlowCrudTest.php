<?php

use App\Models\Client;
use App\Models\Company;
use App\Models\Item;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tax;
use App\Models\TxnInvoice;
use App\Models\TxnJob;
use App\Models\TxnLedger;
use App\Models\TxnPayment;
use App\Models\TxnQuotation;
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
    $this->actingAs($this->owner);
});

test('client and tax masters support validated CRUD', function (string $resource, string $model, array $data) {
    $this->get(route($resource.'.create'))->assertSuccessful();
    $this->post(route($resource.'.store'), $data + ['company_id' => 999])->assertSessionHasNoErrors()->assertRedirect();
    $record = $model::where('name', $data['name'])->firstOrFail();
    expect($record->company_id)->toBe($this->company->id);
    $this->get(route($resource.'.index'))->assertSuccessful()->assertSee($data['name']);
    $this->get(route($resource.'.show', $record))->assertSuccessful();
    $this->get(route($resource.'.edit', $record))->assertSuccessful();
    $this->put(route($resource.'.update', $record), array_replace($data, ['name' => 'Updated']))->assertSessionHasNoErrors();
    expect($record->fresh()->name)->toBe('Updated');
    $this->postJson(route($resource.'.store'), array_replace($data, ['name' => '']))->assertUnprocessable();
    $this->delete(route($resource.'.destroy', $record))->assertRedirect();
    $this->assertModelMissing($record);
})->with([
    ['clients', Client::class, ['name' => 'New client', 'type' => 'business', 'email' => 'client@example.com', 'is_active' => 1]],
    ['taxes', Tax::class, ['name' => 'New GST', 'rate' => '18.1250', 'is_active' => 1]],
]);

test('documents support CRUD snapshots print and private attachments', function (string $type, string $model) {
    Storage::fake('local');
    $item = Item::factory()->create(['company_id' => $this->company->id, 'rate' => '100.00']);
    $data = ['client_id' => $this->client->id, $type.'_date' => '2026-09-19', 'status' => 'draft',
        'items' => [['catalog_item_id' => $item->id, 'quantity' => '2']],
        'attachments' => [UploadedFile::fake()->create('agreement.pdf', 10, 'application/pdf')],
    ];
    $resource = $type.'s';
    $this->get(route($resource.'.create'))->assertSuccessful();
    $this->post(route($resource.'.store'), $data + ['total_amount' => '1', 'company_id' => 999])->assertSessionHasNoErrors()->assertRedirect();
    $record = $model::firstOrFail();
    expect($record->company_id)->toBe($this->company->id)->and($record->total_amount)->toBe('236.00');
    $attachment = $record->attachments->sole();
    Storage::disk('local')->assertExists($attachment->file_path);
    $this->get(route($resource.'.attachment', [$record->id, $attachment->id]))->assertSuccessful()->assertDownload('agreement.pdf');
    foreach (['index', 'show', 'edit', 'print'] as $page) {
        $this->get(route($resource.'.'.$page, $page === 'index' ? [] : $record->id))->assertSuccessful();
    }
    $item->update(['rate' => '900.00']);
    unset($data['attachments']);
    $data['items'][0]['quotation_item_id'] = $record->items->sole()->id;
    $data['items'][0]['quantity'] = '3';
    $this->put(route($resource.'.update', $record->id), $data)->assertSessionHasNoErrors();
    expect($record->fresh()->total_amount)->toBe('354.00')->and($record->fresh()->items->sole()->rate)->toBe('100.00');
    $this->delete(route($resource.'.destroy', $record->id))->assertRedirect();
    $this->assertModelMissing($record);
    Storage::disk('local')->assertMissing($attachment->file_path);
    expect($record->items()->count())->toBe(0);
})->with([['quotation', TxnQuotation::class], ['job', TxnJob::class], ['invoice', TxnInvoice::class]]);

test('payments and ledger remain independent and validate document ownership', function () {
    $invoice = TxnInvoice::factory()->create(['company_id' => $this->company->id, 'client_id' => $this->client->id, 'total_amount' => 500, 'paid_amount' => 0, 'balance_amount' => 500]);
    $foreign = TxnInvoice::factory()->create();
    $data = ['client_id' => $this->client->id, 'document' => 'invoice:'.$invoice->id, 'payment_no' => 'PAY-1', 'payment_date' => '2026-09-19', 'amount' => '100.00', 'payment_mode' => 'bank'];
    $this->get(route('payments.create'))->assertSuccessful();
    $this->post(route('payments.store'), $data)->assertSessionHasNoErrors();
    $payment = TxnPayment::sole();
    expect($invoice->fresh()->paid_amount)->toBe('0.00')->and($invoice->fresh()->balance_amount)->toBe('500.00')->and(TxnLedger::count())->toBe(0);
    foreach (['index', 'show', 'edit', 'print'] as $page) {
        $this->get(route('payments.'.$page, $page === 'index' ? [] : $payment))->assertSuccessful();
    }
    $this->put(route('payments.update', $payment), array_replace($data, ['amount' => '150']))->assertSessionHasNoErrors();
    $this->postJson(route('payments.store'), array_replace($data, ['payment_no' => 'PAY-2', 'document' => 'invoice:'.$foreign->id]))->assertUnprocessable()->assertJsonValidationErrors('document_id');
    $otherClient = Client::factory()->for($this->company)->create();
    $this->postJson(route('payments.store'), array_replace($data, ['payment_no' => 'PAY-3', 'client_id' => $otherClient->id]))->assertUnprocessable()->assertJsonValidationErrors('document_id');
    $this->deleteJson(route('invoices.destroy', $invoice->id))->assertUnprocessable();

    $ledgerData = ['ledger_type' => 'client', 'client_id' => $this->client->id, 'transaction_date' => '2026-09-19', 'entry_type' => 'credit', 'transaction_type' => 'adjustment', 'amount' => '25.00', 'document' => 'invoice:'.$invoice->id];
    $this->get(route('ledger.create'))->assertSuccessful();
    $this->post(route('ledger.store'), $ledgerData)->assertSessionHasNoErrors();
    $ledger = TxnLedger::sole();
    foreach (['index', 'show', 'edit'] as $page) {
        $this->get(route('ledger.'.$page, $page === 'index' ? [] : $ledger))->assertSuccessful();
    }
    $this->put(route('ledger.update', $ledger), array_replace($ledgerData, ['amount' => '30']))->assertSessionHasNoErrors();
    $this->postJson(route('ledger.store'), array_replace($ledgerData, ['document' => 'invoice:'.$foreign->id]))->assertUnprocessable();
    $this->delete(route('payments.destroy', $payment))->assertRedirect();
    $this->delete(route('ledger.destroy', $ledger))->assertRedirect();
    expect(TxnPayment::count())->toBe(0)->and(TxnLedger::count())->toBe(0)->and($invoice->fresh()->paid_amount)->toBe('0.00')->and($invoice->fresh()->balance_amount)->toBe('500.00');
});

test('all new modules reject other company records and require permissions', function (string $resource, string $model) {
    $foreign = $model::factory()->create();
    $this->getJson(route($resource.'.show', $foreign->id))->assertNotFound();
    $this->getJson(route($resource.'.edit', $foreign->id))->assertNotFound();
    $this->putJson(route($resource.'.update', $foreign->id), [])->assertNotFound();
    $this->deleteJson(route($resource.'.destroy', $foreign->id))->assertNotFound();
    $staff = User::factory()->for($this->company)->create(['user_type' => 'staff', 'role_id' => null]);
    $this->actingAs($staff);
    $this->getJson(route($resource.'.index'))->assertForbidden();
    $this->getJson(route($resource.'.create'))->assertForbidden();
    $this->postJson(route($resource.'.store'), [])->assertForbidden();
    $this->putJson(route($resource.'.update', $foreign->id), [])->assertForbidden();
    $this->deleteJson(route($resource.'.destroy', $foreign->id))->assertForbidden();
})->with([
    ['clients', Client::class], ['taxes', Tax::class], ['items', Item::class],
    ['quotations', TxnQuotation::class], ['jobs', TxnJob::class], ['invoices', TxnInvoice::class],
    ['payments', TxnPayment::class], ['ledger', TxnLedger::class],
]);

test('document links enforce matching company and client and protect used masters', function () {
    $quotation = TxnQuotation::factory()->create(['company_id' => $this->company->id, 'client_id' => $this->client->id]);
    $item = Item::factory()->create(['company_id' => $this->company->id]);
    $otherClient = Client::factory()->for($this->company)->create();
    $data = ['client_id' => $otherClient->id, 'job_date' => '2026-09-19', 'quotation_id' => $quotation->id, 'items' => [['catalog_item_id' => $item->id, 'quantity' => 1]]];
    $this->postJson(route('jobs.store'), $data)->assertUnprocessable()->assertJsonValidationErrors('quotation_id');
    $data['client_id'] = $this->client->id;
    $this->post(route('jobs.store'), $data)->assertSessionHasNoErrors();
    $this->deleteJson(route('quotations.destroy', $quotation->id))->assertUnprocessable();
    $this->deleteJson(route('clients.destroy', $this->client))->assertUnprocessable();
    $this->deleteJson(route('taxes.destroy', $item->tax))->assertUnprocessable();
    $foreignTax = Tax::factory()->create();
    $this->postJson(route('items.store'), ['type' => 'service', 'name' => 'Bad tax', 'hsn' => '123', 'rate' => '10', 'tax_id' => $foreignTax->id])->assertUnprocessable()->assertJsonValidationErrors('tax_id');
});

test('attachments are bound to the company and correct document', function () {
    Storage::fake('local');
    $first = TxnQuotation::factory()->create(['company_id' => $this->company->id, 'client_id' => $this->client->id]);
    $second = TxnQuotation::factory()->create(['company_id' => $this->company->id, 'client_id' => $this->client->id]);
    Storage::disk('local')->put('document.pdf', 'private');
    $attachment = $first->attachments()->create(['company_id' => $this->company->id, 'file_name' => 'document.pdf', 'file_path' => 'document.pdf']);
    $this->get(route('quotations.attachment', [$second->id, $attachment->id]))->assertNotFound();
    $otherCompany = Company::factory()->create(['onboarding_completed_at' => now()]);
    $this->actingAs(User::factory()->for($otherCompany)->create(['user_type' => 'owner']))
        ->get(route('quotations.attachment', [$first->id, $attachment->id]))->assertNotFound();
});

test('malformed financial selections and mismatched invoice parents are rejected', function () {
    $this->postJson(route('payments.store'), ['document' => ['invoice', 1]])->assertUnprocessable()->assertJsonValidationErrors('document');
    $this->postJson(route('ledger.store'), ['document' => ['invoice', 1]])->assertUnprocessable()->assertJsonValidationErrors('document');
    $first = TxnQuotation::factory()->create(['company_id' => $this->company->id, 'client_id' => $this->client->id]);
    $second = TxnQuotation::factory()->create(['company_id' => $this->company->id, 'client_id' => $this->client->id]);
    $job = TxnJob::factory()->create(['company_id' => $this->company->id, 'client_id' => $this->client->id, 'quotation_id' => $first->id]);
    $item = Item::factory()->create(['company_id' => $this->company->id]);
    $data = ['client_id' => $this->client->id, 'invoice_date' => '2026-09-19', 'quotation_id' => $second->id, 'job_id' => $job->id, 'items' => [['catalog_item_id' => $item->id, 'quantity' => 1]]];
    $this->postJson(route('invoices.store'), $data)->assertUnprocessable()->assertJsonValidationErrors('job_id');
    $data['quotation_id'] = $first->id;
    $this->post(route('invoices.store'), $data)->assertSessionHasNoErrors();
    $data['job_date'] = '2026-09-19';
    $data['quotation_id'] = $second->id;
    $this->patchJson(route('jobs.update', $job->id), $data)->assertUnprocessable()->assertJsonValidationErrors('quotation_id');
});

test('staff can read a permitted module without gaining write permissions', function () {
    app(RolePermissionSeeder::class)->seedCompany($this->company->id);
    $role = Role::create(['company_id' => $this->company->id, 'name' => 'Client reader', 'slug' => 'client-reader', 'status' => true]);
    $role->permissions()->sync(Permission::where('company_id', $this->company->id)->where('slug', 'view_clients')->pluck('id'));
    $staff = User::factory()->for($this->company)->create(['user_type' => 'staff', 'role_id' => $role->id]);
    $foreignClient = Client::factory()->create(['name' => 'Other company private client']);
    $this->actingAs($staff);
    $this->get(route('clients.index'))->assertSuccessful()->assertSee($this->client->name)->assertDontSee($foreignClient->name);
    $this->get(route('clients.show', $this->client))->assertSuccessful();
    $this->getJson(route('clients.create'))->assertForbidden();
    $this->putJson(route('clients.update', $this->client), [])->assertForbidden();
});
