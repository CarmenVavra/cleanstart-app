<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\SobrietyPeriod;
use App\Models\User;
use Database\Seeders\BadgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class RelapseTest extends TestCase
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

    public function test_restart_page_renders_with_current_streak(): void
    {
        $user = User::factory()->create(['sobriety_start_at' => now()->subDays(12)]);

        $this->actingAs($user)
            ->get(route('relapse.create'))
            ->assertOk()
            ->assertSee('Du bist hier – und das zählt.')
            ->assertSee('12 Tagen')
            ->assertSee('name="_token"', false);
    }

    public function test_restart_archives_the_previous_streak_and_resets_the_counter(): void
    {
        $user = User::factory()->create(['sobriety_start_at' => '2026-09-01 08:00:00']);

        $this->actingAs($user)
            ->post(route('relapse.store'), [
                'relapsed_at' => '2026-10-13T18:00',
                'reflection' => 'Streit mit meinem Bruder.',
                'triggers' => ['anger', 'stress'],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('status', fn (string $status) => str_contains($status, '42 Tage bleibt gespeichert'));

        $user->refresh();
        $this->assertSame('2026-10-13 18:00', $user->sobriety_start_at->format('Y-m-d H:i'));

        $period = $user->sobrietyPeriods()->sole();
        $this->assertSame('2026-09-01 08:00', $period->started_at->format('Y-m-d H:i'));
        $this->assertSame(42, $period->days());
        $this->assertSame(['anger', 'stress'], $period->triggers);
        $this->assertSame('Streit mit meinem Bruder.', $period->reflection_encrypted);

        // Reflexion ist in der Datenbank nicht im Klartext lesbar.
        $this->assertStringNotContainsString('Bruder', (string) DB::table('sobriety_periods')->value('reflection_encrypted'));

        // Abzeichen der neuen Serie: noch keines erreicht.
        $this->assertSame(0, $user->badges()->count());
    }

    public function test_restart_time_must_lie_within_the_current_streak(): void
    {
        $user = User::factory()->create(['sobriety_start_at' => '2026-09-01 08:00:00']);

        $this->actingAs($user)
            ->post(route('relapse.store'), ['relapsed_at' => '2026-08-01T08:00'])
            ->assertSessionHasErrors(['relapsed_at' => 'Der neue Start darf nicht vor dem Beginn deiner aktuellen Serie liegen.']);

        $this->actingAs($user)
            ->post(route('relapse.store'), ['relapsed_at' => '2026-10-14T08:00'])
            ->assertSessionHasErrors('relapsed_at');

        $this->assertSame(0, SobrietyPeriod::count());
    }

    public function test_unknown_triggers_are_rejected(): void
    {
        $user = User::factory()->create(['sobriety_start_at' => now()->subDays(5)]);

        $this->actingAs($user)
            ->post(route('relapse.store'), [
                'relapsed_at' => now()->format('Y-m-d\TH:i'),
                'triggers' => ['<script>'],
            ])
            ->assertSessionHasErrors('triggers.0');

        $this->assertSame(0, SobrietyPeriod::count());
    }

    public function test_dashboard_shows_the_restart_message_and_longest_streak(): void
    {
        $user = User::factory()->create(['sobriety_start_at' => '2026-09-01 08:00:00']);

        $this->actingAs($user)->post(route('relapse.store'), ['relapsed_at' => '2026-10-13T18:00']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertSee('Dein neuer Anfang zählt.')
            ->assertSee('Bisher längste Serie: 42 Tage');
    }

    public function test_flash_message_survives_livewire_updates(): void
    {
        $user = User::factory()->create();

        $this->withSession(['status' => 'Dein neuer Anfang zählt.']);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertSee('Dein neuer Anfang zählt.')
            ->set('form.mood', 3)
            ->call('save')
            ->assertSee('Dein neuer Anfang zählt.');
    }

    public function test_profile_lists_previous_streaks(): void
    {
        $user = User::factory()->create();
        SobrietyPeriod::factory()->for($user)->create([
            'started_at' => '2026-06-02 09:00:00',
            'ended_at' => '2026-07-14 22:00:00',
        ]);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertSee('Deine bisherigen Serien')
            ->assertSee('42 Tage');
    }
}
