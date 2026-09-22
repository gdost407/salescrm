<?php

namespace Database\Factories;

use App\Models\TxnPayment;
use App\Models\TxnQuotation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TxnPayment>
 */
class TxnPaymentFactory extends Factory
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
            'document_id' => TxnQuotation::factory(),
            'company_id' => fn (array $attributes) => TxnQuotation::findOrFail($attributes['document_id'])->company_id,
            'client_id' => fn (array $attributes) => TxnQuotation::findOrFail($attributes['document_id'])->client_id,
            'payment_no' => fake()->unique()->bothify('PAY-########'),
            'payment_date' => today(),
            'amount' => '100.00',
            'payment_mode' => 'bank',
        ];
    }
}
