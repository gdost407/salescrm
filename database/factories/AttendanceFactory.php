<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->for(Company::factory())->state(['user_type' => 'staff']),
            'company_id' => fn (array $attributes) => User::findOrFail($attributes['user_id'])->company_id,
            'date' => now(config('attendance.timezone'))->toDateString(),
            'punch_in' => now(),
        ];
    }
}
