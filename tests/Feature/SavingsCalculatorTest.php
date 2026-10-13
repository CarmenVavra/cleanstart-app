<?php

namespace Tests\Feature;

use App\Enums\Country;
use App\Enums\SpendingPeriod;
use App\Models\User;
use Database\Seeders\BadgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SavingsCalculatorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BadgeSeeder::class);
        Carbon::setTestNow('2026-10-13 20:30:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function profilePayload(User $user, array $overrides = []): array
    {
        return array_merge([
            'username' => $user->username,
            'sobriety_start_at' => $user->sobriety_start_at->format('Y-m-d\TH:i'),
            'country' => $user->country->value,
        ], $overrides);
    }

    public function test_dashboard_invites_to_set_up_the_calculator(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertSee('Ersparnis berechnen')
            ->assertDontSee('Gespart seit deinem Start');
    }

    public function test_dashboard_shows_savings_in_austrian_format(): void
    {
        $user = User::factory()
            ->withSpending(7000, SpendingPeriod::Week)
            ->create(['sobriety_start_at' => now()->subDays(14), 'country' => Country::Austria]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertSee('Gespart seit deinem Start')
            ->assertSee("€\u{00A0}140,00")
            ->assertSee("Bei €\u{00A0}70,00 pro Woche · rund €\u{00A0}304 im Monat")
            ->assertDontSee('Ersparnis berechnen');
    }

    public function test_dashboard_shows_savings_in_german_format(): void
    {
        $user = User::factory()
            ->withSpending(1250, SpendingPeriod::Day)
            ->create(['sobriety_start_at' => now()->subDays(100), 'country' => Country::Germany]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertSee("1.250,00\u{00A0}€")
            ->assertSee("Bei 12,50\u{00A0}€ pro Tag · rund 380\u{00A0}€ im Monat");
    }

    public function test_amount_can_be_saved_in_german_notation_and_is_encrypted(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('profile.update'), $this->profilePayload($user, [
                'spending_amount' => '1.234,50 €',
                'spending_period' => 'month',
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $user->refresh();
        $this->assertSame(123450, $user->spendingCents());
        $this->assertSame(SpendingPeriod::Month, $user->spending_period);

        $raw = DB::table('users')->where('id', $user->id)->value('spending_cents');
        $this->assertStringNotContainsString('123450', (string) $raw);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertSee('value="1234,50"', false)
            ->assertSee('<option value="month" selected>pro Monat</option>', false);
    }

    public function test_dot_is_accepted_as_decimal_separator(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('profile.update'), $this->profilePayload($user, [
                'spending_amount' => '12.5',
                'spending_period' => 'day',
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame(1250, $user->refresh()->spendingCents());
    }

    public function test_empty_amount_turns_the_calculator_off(): void
    {
        $user = User::factory()->withSpending()->create();

        $this->actingAs($user)
            ->put(route('profile.update'), $this->profilePayload($user, [
                'spending_amount' => '',
                'spending_period' => 'week',
            ]))
            ->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertFalse($user->hasSpending());
        $this->assertNull($user->spending_period);
    }

    public function test_invalid_amounts_are_rejected(): void
    {
        $user = User::factory()->create();

        foreach (['viel', '-5', '12,345', '200000'] as $amount) {
            $this->actingAs($user)
                ->put(route('profile.update'), $this->profilePayload($user, [
                    'spending_amount' => $amount,
                    'spending_period' => 'week',
                ]))
                ->assertSessionHasErrors('spending_amount');
        }

        $this->actingAs($user)
            ->put(route('profile.update'), $this->profilePayload($user, [
                'spending_amount' => '10',
                'spending_period' => 'year',
            ]))
            ->assertSessionHasErrors('spending_period');

        $this->assertFalse($user->refresh()->hasSpending());
    }

    public function test_savings_are_part_of_the_data_export(): void
    {
        $user = User::factory()->withSpending(7000, SpendingPeriod::Week)->create();

        $content = (string) $this->actingAs($user)->get(route('profile.export'))->streamedContent();

        $this->assertSame(
            ['previous_spending_eur' => 70, 'period' => 'pro Woche'],
            json_decode($content, true)['savings_calculator'],
        );
    }
}
