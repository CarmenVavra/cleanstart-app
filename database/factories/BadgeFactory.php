<?php

namespace Database\Factories;

use App\Models\Badge;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Badge>
 */
class BadgeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $days = fake()->unique()->numberBetween(1, 999);

        return [
            'title' => "{$days} Tage",
            'icon_slug' => "badge-{$days}d",
            'short_label' => (string) $days,
            'hue' => fake()->numberBetween(0, 359),
            'days_required' => $days,
        ];
    }
}
