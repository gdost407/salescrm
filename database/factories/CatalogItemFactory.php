<?php

namespace Database\Factories;

use App\Models\CatalogItem;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CatalogItem>
 */
class CatalogItemFactory extends Factory
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
            'hsn' => '998313',
            'gst_rate' => '18.00',
            'rate' => '100.00',
        ];
    }
}
