<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Lead;
use App\Models\LeadActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadActivity>
 */
class LeadActivityFactory extends Factory
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
            'lead_id' => Lead::factory(),
            'user_id' => null,
            'activity_type' => fake()->randomElement(['followup', 'visit', 'gmeet']),
            'followup_type' => null,
            'subject' => fake()->sentence(4),
            'summary' => null,
            'scheduled_at' => fake()->dateTimeBetween('now', '+30 days'),
            'completed_at' => null,
            'status' => 'pending',
            'metadata' => null,
        ];
    }
}
