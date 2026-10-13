<?php

namespace Database\Factories;

use App\Models\SobrietyPeriod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SobrietyPeriod>
 */
class SobrietyPeriodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'started_at' => now()->subDays(60),
            'ended_at' => now()->subDays(30),
            'reflection_encrypted' => null,
            'triggers' => [],
        ];
    }
}
