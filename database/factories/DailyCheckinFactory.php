<?php

namespace Database\Factories;

use App\Enums\Mood;
use App\Enums\Trigger;
use App\Models\DailyCheckin;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyCheckin>
 */
class DailyCheckinFactory extends Factory
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
            'mood_rating' => fake()->randomElement(Mood::cases()),
            'triggers' => fake()->randomElements(array_column(Trigger::cases(), 'value'), 2),
            'notes_encrypted' => null,
            'checkin_date' => today(),
        ];
    }
}
