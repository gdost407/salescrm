<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\TxnInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TxnInvoice>
 */
class TxnInvoiceFactory extends Factory
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
            'invoice_no' => fake()->unique()->bothify('TXN-########'),
            'invoice_date' => today(),
            'subtotal' => '0.00',
            'discount_amount' => '0.00',
            'tax_amount' => '0.00',
            'total_amount' => '0.00',
            'status' => 'draft',
        ];
    }
}
