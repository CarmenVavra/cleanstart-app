<?php

namespace Database\Factories;

use App\Enums\CopingType;
use App\Models\CopingItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CopingItem>
 */
class CopingItemFactory extends Factory
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
            'type' => CopingType::Strategy,
            'content' => fake()->sentence(4),
        ];
    }

    public function reason(): static
    {
        return $this->state(fn (array $attributes) => ['type' => CopingType::Reason]);
    }

    public function strategy(): static
    {
        return $this->state(fn (array $attributes) => ['type' => CopingType::Strategy]);
    }
}
