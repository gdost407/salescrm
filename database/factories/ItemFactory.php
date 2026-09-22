<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Item;
use App\Models\Tax;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'type' => 'service',
            'name' => fake()->words(3, true),
            'hsn_sac' => '998313',
            'rate' => '100.00',
            'tax_id' => fn (array $attributes) => Tax::factory()->create(['company_id' => $attributes['company_id']])->id,
            'tax_type' => fn (array $attributes) => $attributes['type'] === 'inventory' ? 'inclusive' : 'exclusive',
            'is_active' => true,
        ];
    }
}
