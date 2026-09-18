<?php

namespace Database\Factories;

use App\Models\TxnHistoryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TxnHistoryItem>
 */
class TxnHistoryItemFactory extends Factory
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
            'item_id' => fn (array $attributes) => \App\Models\Item::factory()->create(['company_id' => $attributes['company_id']])->id,
            'item_type' => 'service',
            'item_name' => fake()->words(3, true),
            'qty' => '1.000',
            'rate' => '100.00',
            'taxable_amount' => '100.00',
            'tax_amount' => '0.00',
            'total_amount' => '100.00',
        ];
    }
}
