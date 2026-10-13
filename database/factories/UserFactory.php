<?php

namespace Database\Factories;

use App\Enums\Country;
use App\Enums\SpendingPeriod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Muss die Regel alpha_dash:ascii erfüllen (Faker-userName() enthält teils Punkte).
            'username' => 'nutzer_'.fake()->unique()->numerify('######'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'sobriety_start_at' => now()->subDays(10),
            'trusted_contact_name' => null,
            'trusted_contact_phone' => null,
            'country' => Country::DEFAULT,
            'privacy_accepted_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Nutzer mit eingerichtetem Ersparnis-Rechner.
     */
    public function withSpending(int $cents = 7000, SpendingPeriod $period = SpendingPeriod::Week): static
    {
        return $this->state(fn (array $attributes) => [
            'spending_cents' => (string) $cents,
            'spending_period' => $period,
        ]);
    }

    /**
     * Nutzer mit hinterlegter Vertrauensperson.
     */
    public function withTrustedContact(string $name = 'Sabine', string $phone = '+49 170 1234567'): static
    {
        return $this->state(fn (array $attributes) => [
            'trusted_contact_name' => $name,
            'trusted_contact_phone' => $phone,
        ]);
    }
}
