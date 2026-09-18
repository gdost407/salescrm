<?php

namespace Database\Factories;

use App\Models\TxnLedger;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TxnLedger>
 */
class TxnLedgerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'document_type' => 'quotation',
            'document_id' => \App\Models\TxnQuotation::factory(),
            'company_id' => fn (array $attributes) => \App\Models\TxnQuotation::findOrFail($attributes['document_id'])->company_id,
            'client_id' => fn (array $attributes) => \App\Models\TxnQuotation::findOrFail($attributes['document_id'])->client_id,
            'ledger_type' => 'client',
            'transaction_date' => today(),
            'entry_type' => 'debit',
            'transaction_type' => 'adjustment',
            'amount' => '100.00',
        ];
    }
}
