<?php

use App\Models\Client;
use App\Models\Company;
use App\Models\Item;
use App\Models\Tax;
use App\Models\TxnAttachment;
use App\Models\TxnHistoryItem;
use App\Models\TxnInvoice;
use App\Models\TxnJob;
use App\Models\TxnLedger;
use App\Models\TxnPayment;
use App\Models\TxnQuotation;
use Illuminate\Support\Facades\Schema;

test('transaction models persist against the current migration schema', function (string $modelClass, string $table) {
    $model = $modelClass::factory()->create();
    $this->assertModelExists($model);
    expect($model->getTable())->toBe($table)
        ->and(array_diff($model->getFillable(), Schema::getColumnListing($table)))->toBe([]);
    $foreignCompany = Company::factory()->create();
    expect($modelClass::forCompany($model->company_id)->whereKey($model->id)->exists())->toBeTrue()
        ->and($modelClass::forCompany($foreignCompany->id)->whereKey($model->id)->exists())->toBeFalse();
})->with([
    [Tax::class, 'taxes'], [Item::class, 'items'], [TxnQuotation::class, 'txn_quotations'],
    [TxnJob::class, 'txn_jobs'], [TxnInvoice::class, 'txn_invoices'],
    [TxnHistoryItem::class, 'txn_history_items'], [TxnAttachment::class, 'txn_attachments'],
    [TxnPayment::class, 'txn_payments'], [TxnLedger::class, 'txn_ledger'],
]);

test('client fields use the new schema without soft deletes', function () {
    $client = Client::factory()->create(['mobile' => '1234567890', 'billing_address' => 'Office', 'zip_code' => '123456', 'gst_no' => 'GST-EXAMPLE']);
    expect($client->fresh()->mobile)->toBe('1234567890')
        ->and($client->fresh()->is_active)->toBeTrue()
        ->and(array_diff($client->getFillable(), Schema::getColumnListing('clients')))->toBe([]);
    $client->delete();
    $this->assertModelMissing($client);
});

test('document relationships distinguish types even when document ids overlap', function () {
    $client = Client::factory()->create();
    $quotation = TxnQuotation::factory()->for($client)->create(['id' => 50]);
    $job = TxnJob::factory()->for($client)->create(['id' => 50, 'quotation_id' => $quotation->id]);
    $invoice = TxnInvoice::factory()->for($client)->create(['id' => 50, 'quotation_id' => $quotation->id, 'job_id' => $job->id]);
    $item = Item::factory()->create(['company_id' => $client->company_id]);

    foreach ([$quotation, $job, $invoice] as $document) {
        $line = $document->items()->create([
            'company_id' => $client->company_id, 'item_id' => $item->id, 'item_type' => 'service',
            'item_name' => 'Recorded item', 'rate' => '100.10', 'qty' => '1.125', 'tax_id' => $item->tax_id,
            'tax_rate' => '18.1234', 'sort_order' => 2,
        ]);
        $document->attachments()->create(['company_id' => $client->company_id, 'file_name' => 'sample.pdf', 'file_path' => 'private/sample.pdf', 'file_size' => '123']);
        $document->payments()->create(['company_id' => $client->company_id, 'client_id' => $client->id, 'payment_no' => 'PAY-'.$document->getMorphClass(), 'payment_date' => '2026-09-19', 'amount' => '10.10', 'payment_mode' => 'bank']);
        $document->ledgerEntries()->create(['company_id' => $client->company_id, 'client_id' => $client->id, 'ledger_type' => 'client', 'transaction_date' => '2026-09-19', 'entry_type' => 'credit', 'transaction_type' => 'payment', 'amount' => '10.10']);

        $document->load(['items.document', 'attachments.document', 'payments.document', 'ledgerEntries.document']);
        foreach (['items', 'attachments', 'payments', 'ledgerEntries'] as $relation) {
            expect($document->$relation)->toHaveCount(1)
                ->and($document->$relation->first()->document->is($document))->toBeTrue();
        }
        expect($line->fresh()->qty)->toBe('1.125')->and($line->fresh()->tax_rate)->toBe('18.1234')
            ->and($line->item->is($item))->toBeTrue()
            ->and($document->payments->first()->payment_date->format('Y-m-d'))->toBe('2026-09-19')
            ->and($document->attachments->first()->file_size)->toBe(123);
    }
    expect($job->quotation->is($quotation))->toBeTrue()
        ->and($invoice->job->is($job))->toBeTrue()
        ->and($quotation->jobs->modelKeys())->toBe([$job->id])
        ->and($quotation->invoices->modelKeys())->toBe([$invoice->id])
        ->and($client->payments)->toHaveCount(3)
        ->and($client->ledgerEntries)->toHaveCount(3);
    $item->delete();
    expect(TxnHistoryItem::whereNotNull('item_id')->count())->toBe(0)
        ->and(TxnHistoryItem::where('item_name', 'Recorded item')->count())->toBe(3);
});
