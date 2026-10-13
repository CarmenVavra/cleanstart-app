<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\CravingEpisode;
use App\Models\User;
use Database\Seeders\BadgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class CravingTimerTest extends TestCase
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

    public function test_emergency_sheet_offers_the_craving_timer(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertStatus(200)
            ->assertSee('Suchtdruck-Timer starten')
            ->assertSee('15 Minuten – die Welle geht vorbei')
            ->assertSee('aria-controls="craving-area"', false)
            // Zähler ist ohne Einträge ausgeblendet (wird nach dem Speichern per Alpine eingeblendet).
            ->assertSee('x-show="cravingsOvercome > 0"', false)
            ->assertSee('0-mal');
    }

    public function test_overcome_craving_is_recorded_and_counted(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->call('recordCraving')
            ->assertReturned(1);

        $this->assertDatabaseHas('craving_episodes', [
            'user_id' => $user->id,
            'overcome_at' => '2026-10-13 20:30:00',
        ]);
    }

    public function test_repeated_calls_within_one_timer_period_are_ignored(): void
    {
        $user = User::factory()->create();
        $component = Livewire::actingAs($user)->test(Dashboard::class);

        $component->call('recordCraving')->assertReturned(1);
        $component->call('recordCraving')->assertReturned(1);

        Carbon::setTestNow(now()->addMinutes(CravingEpisode::TIMER_MINUTES)->addSecond());

        $component->call('recordCraving')->assertReturned(2);
        $this->assertSame(2, $user->cravingEpisodes()->count());
    }

    public function test_dashboard_shows_how_often_craving_was_overcome(): void
    {
        $user = User::factory()->create();
        CravingEpisode::factory()->count(3)->for($user)->create();
        CravingEpisode::factory()->count(5)->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertSee('Suchtdruck überstanden:')
            ->assertSee('3-mal');
    }

    public function test_craving_episodes_are_exported_and_deleted_with_the_account(): void
    {
        $user = User::factory()->create();
        CravingEpisode::factory()->for($user)->create(['overcome_at' => '2026-10-01 18:00:00']);

        $content = (string) $this->actingAs($user)->get(route('profile.export'))->streamedContent();
        $this->assertSame(['2026-10-01T18:00:00+02:00'], json_decode($content, true)['overcome_cravings']);

        $this->actingAs($user)
            ->delete(route('profile.destroy'), ['password' => 'password'])
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('craving_episodes', 0);
    }

    public function test_guests_cannot_record_cravings(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }
}
