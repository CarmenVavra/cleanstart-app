<?php

namespace Database\Factories;

use App\Models\CravingEpisode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CravingEpisode>
 */
class CravingEpisodeFactory extends Factory
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
            'overcome_at' => fake()->dateTimeBetween('-30 days'),
        ];
    }
}
