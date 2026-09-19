<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\TxnQuotation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TxnQuotation>
 */
class TxnQuotationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'company_id' => fn (array $attributes) => Client::findOrFail($attributes['client_id'])->company_id,
            'quotation_no' => fake()->unique()->bothify('TXN-########'),
            'quotation_date' => today(),
            'subtotal' => '0.00',
            'discount_amount' => '0.00',
            'tax_amount' => '0.00',
            'total_amount' => '0.00',
            'status' => 'draft',
        ];
    }
}
