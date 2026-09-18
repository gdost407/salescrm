<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Quotation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quotation>
 */
class QuotationFactory extends Factory
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
            'quotation_date' => today(),
            'seller_details' => ['name' => 'Example Seller'],
            'client_details' => ['name' => 'Example Client'],
            'subtotal' => '0.00',
            'tax_total' => '0.00',
            'total' => '0.00',
        ];
    }
}
