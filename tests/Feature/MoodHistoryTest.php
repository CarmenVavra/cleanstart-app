<?php

namespace Tests\Feature;

use App\Enums\Mood;
use App\Enums\Trigger;
use App\Models\DailyCheckin;
use App\Models\SobrietyPeriod;
use App\Models\User;
use App\Services\MoodHistory;
use Database\Seeders\BadgeSeeder;
use Database\Seeders\DemoCheckinSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MoodHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BadgeSeeder::class);
        // Dienstag
        Carbon::setTestNow('2026-10-13 20:30:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * @param  list<Trigger>  $triggers
     */
    private function checkin(User $user, int $daysAgo, Mood $mood, array $triggers = [], ?string $notes = null): void
    {
        DailyCheckin::factory()->for($user)->create([
            'checkin_date' => today()->subDays($daysAgo),
            'mood_rating' => $mood,
            'triggers' => array_map(fn (Trigger $trigger) => $trigger->value, $triggers),
            'notes_encrypted' => $notes,
        ]);
    }

    public function test_dashboard_links_to_the_mood_history(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertSee('Mein Stimmungsverlauf')
            ->assertSee(route('mood.index'), false);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('mood.index'))->assertRedirect(route('login'));
    }

    public function test_empty_history_invites_to_check_in(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('mood.index'))
            ->assertStatus(200)
            ->assertSee('Dein Stimmungsverlauf')
            ->assertSee('Noch keine Check-ins in diesem Zeitraum')
            ->assertSee('aria-current="page"', false);
    }

    public function test_history_shows_summary_chart_and_table(): void
    {
        $user = User::factory()->create();
        $this->checkin($user, 0, Mood::Good, [Trigger::Stress]);
        $this->checkin($user, 1, Mood::Okay);
        $this->checkin($user, 3, Mood::VeryGood, [], 'geheime Notiz');

        $this->actingAs($user)
            ->get(route('mood.index'))
            ->assertStatus(200)
            ->assertSee('Check-ins')
            ->assertSee('von 30 Tagen')
            ->assertSee('4,0')
            ->assertSee('Stimmung pro Tag')
            ->assertSee('<rect', false)
            ->assertSee('Werte als Tabelle anzeigen')
            ->assertSee('Di., 13. Okt')
            ->assertSee('Ab 5 Check-ins im gewählten Zeitraum')
            ->assertDontSee('geheime Notiz');
    }

    public function test_range_can_be_switched_and_excludes_older_checkins(): void
    {
        $user = User::factory()->create();
        $this->checkin($user, 2, Mood::Good);
        $this->checkin($user, 20, Mood::VeryBad);

        $this->actingAs($user)
            ->get(route('mood.index', ['tage' => 7]))
            ->assertSee('von 7 Tagen')
            ->assertSeeInOrder(['Check-ins', '1', 'von 7 Tagen']);

        $history = new MoodHistory($user, 7);
        $this->assertSame(1, $history->checkinCount());
        $this->assertCount(7, $history->timeline());

        $this->assertSame(2, (new MoodHistory($user, 30))->checkinCount());
    }

    public function test_invalid_range_redirects_to_the_default_view(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('mood.index', ['tage' => 365]))
            ->assertRedirect(route('mood.index'));
    }

    public function test_only_own_checkins_are_shown(): void
    {
        $user = User::factory()->create();
        $this->checkin(User::factory()->create(), 0, Mood::VeryBad);

        $this->actingAs($user)
            ->get(route('mood.index'))
            ->assertSee('Noch keine Check-ins in diesem Zeitraum');
    }

    public function test_patterns_point_out_heavy_triggers_weekdays_and_trend(): void
    {
        $user = User::factory()->create();

        // Ältere Hälfte: gute Tage
        foreach ([25, 24, 23, 22, 21] as $daysAgo) {
            $this->checkin($user, $daysAgo, Mood::VeryGood, [Trigger::Fatigue]);
        }

        // Jüngere Hälfte: Freitage (2. und 9. Oktober) mit Stress deutlich schwerer; der 18. September (vor 25 Tagen) ist ebenfalls ein Freitag
        $this->checkin($user, 11, Mood::VeryBad, [Trigger::Stress]);
        $this->checkin($user, 4, Mood::Bad, [Trigger::Stress]);
        $this->checkin($user, 2, Mood::Okay);
        $this->checkin($user, 1, Mood::Okay);

        $history = new MoodHistory($user, 30);
        $this->assertSame('worse', $history->trend());
        $this->assertSame(Trigger::Fatigue, $history->triggerStats()[0]['trigger']);

        $this->actingAs($user)
            ->get(route('mood.index'))
            ->assertSee('Zuletzt war deine Stimmung gedrückter als davor.')
            ->assertSee('An Tagen mit „Stress“ ging es dir im Schnitt schlechter (1,5 statt 3,8).')
            ->assertSee('Freitage fallen dir oft schwerer (Ø 2,7).')
            ->assertSee('Zu meinem Notfallplan')
            ->assertSee('Häufigste Trigger')
            ->assertSeeInOrder(['Müdigkeit', 'an 5 Tagen', 'Stress', 'an 2 Tagen'])
            ->assertSee('Stimmung nach Wochentag');
    }

    public function test_demo_seeder_fills_history_without_touching_existing_checkins(): void
    {
        $user = User::factory()->create(['username' => 'Alex']);
        $this->checkin($user, 2, Mood::VeryGood, [], 'eigener Eintrag');

        $this->seed(DemoCheckinSeeder::class);

        $this->assertGreaterThan(15, $user->dailyCheckins()->count());
        $this->assertSame(
            'eigener Eintrag',
            $user->dailyCheckins()->whereDate('checkin_date', today()->subDays(2))->firstOrFail()->notes_encrypted,
        );
        $this->assertTrue((new MoodHistory($user, 30))->showsPatterns());
    }

    public function test_restarts_are_marked_in_the_chart(): void
    {
        $user = User::factory()->create(['sobriety_start_at' => now()->subDays(5)]);
        SobrietyPeriod::factory()->for($user)->create([
            'started_at' => now()->subDays(40),
            'ended_at' => now()->subDays(5),
        ]);
        $this->checkin($user, 5, Mood::Bad);

        $this->actingAs($user)
            ->get(route('mood.index'))
            ->assertSee('stroke-dasharray="3 3"', false)
            ->assertSee('Neustart');
    }
}
