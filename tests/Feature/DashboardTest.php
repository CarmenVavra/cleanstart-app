<?php

namespace Tests\Feature;

use App\Enums\Mood;
use App\Livewire\Dashboard;
use App\Models\DailyCheckin;
use App\Models\User;
use Database\Seeders\BadgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
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

    public function test_dashboard_renders_counter_milestones_and_emergency_button(): void
    {
        $user = User::factory()->create([
            'username' => 'Alex',
            'sobriety_start_at' => '2026-08-29 08:00:00',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertStatus(200)
            ->assertSee('Guten Abend')
            ->assertSee('Alex')
            ->assertSee('Dienstag, 13. Oktober')
            ->assertSee('NOTFALL')
            ->assertSee('seit 29. August 2026')
            ->assertSee('45 Tage, 12 Stunden und 30 Minuten clean')
            ->assertSee('Noch 45 Tage bis „90 Tage“')
            ->assertSee('Erreichte Meilensteine')
            ->assertSee('Telefonseelsorge anrufen')
            ->assertSee('tel:112', false);
    }

    public function test_dashboard_shows_hours_until_first_milestone(): void
    {
        $user = User::factory()->create(['sobriety_start_at' => now()->subHours(5)]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertSee('Noch 19 Std. bis „24 Stunden“');
    }

    public function test_trusted_contact_is_offered_in_the_emergency_sheet(): void
    {
        $user = User::factory()->withTrustedContact('Sabine', '+49 170 1234567')->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertSee('Sabine anrufen')
            ->assertSee('href="tel:+491701234567"', false);
    }

    public function test_missing_trusted_contact_links_to_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertSee('Vertrauensperson hinterlegen');
    }

    public function test_user_input_is_escaped(): void
    {
        $user = User::factory()->withTrustedContact('<script>alert(1)</script>', '+49 170 1')->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_mounting_the_dashboard_unlocks_reached_badges(): void
    {
        $user = User::factory()->create(['sobriety_start_at' => now()->subDays(8)]);

        Livewire::actingAs($user)->test(Dashboard::class);

        $this->assertEqualsCanonicalizing(
            [1, 3, 7],
            $user->badges()->pluck('days_required')->all(),
        );
    }

    public function test_a_check_in_can_be_saved(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertSet('saved', false)
            ->set('form.mood', 4)
            ->set('form.triggers', ['stress', 'fatigue'])
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('saved', true)
            ->assertSee('Danke, gespeichert.')
            ->assertSee('Stimmung: Gut')
            ->assertSee('Notiert: Stress, Müdigkeit. Gut, dass du hinschaust.');

        $checkin = $user->dailyCheckins()->sole();
        $this->assertSame(Mood::Good, $checkin->mood_rating);
        $this->assertSame(['stress', 'fatigue'], $checkin->triggers);
        $this->assertTrue($checkin->checkin_date->isToday());
    }

    public function test_saving_without_triggers_shows_the_encouraging_message(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('form.mood', 3)
            ->call('save')
            ->assertSee('Keine Trigger notiert. Schön, dass du dir Zeit genommen hast.');
    }

    public function test_mood_is_required(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->call('save')
            ->assertHasErrors(['form.mood' => 'required'])
            ->assertSet('saved', false);

        $this->assertSame(0, DailyCheckin::count());
    }

    public function test_invalid_mood_and_unknown_triggers_are_rejected(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('form.mood', 9)
            ->set('form.triggers', ["'; DROP TABLE users; --"])
            ->call('save')
            ->assertHasErrors(['form.mood', 'form.triggers.0']);

        $this->assertSame(0, DailyCheckin::count());
        $this->assertSame(1, User::count());
    }

    public function test_only_one_check_in_per_day_is_stored_and_edits_update_it(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('form.mood', 2)
            ->call('save')
            ->call('edit')
            ->assertSet('saved', false)
            ->assertSet('form.mood', 2)
            ->set('form.mood', 5)
            ->call('save');

        $this->assertSame(1, $user->dailyCheckins()->count());
        $this->assertSame(Mood::VeryGood, $user->dailyCheckins()->sole()->mood_rating);
    }

    public function test_existing_check_in_is_rendered_in_saved_state(): void
    {
        $user = User::factory()->create();
        DailyCheckin::factory()->for($user)->create([
            'mood_rating' => Mood::Okay,
            'triggers' => ['boredom'],
        ]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertSet('saved', true)
            ->assertSet('form.mood', 3)
            ->assertSee('Stimmung: Okay')
            ->assertSee('Notiert: Langeweile.');
    }

    public function test_yesterdays_check_in_does_not_count_for_today(): void
    {
        $user = User::factory()->create();
        DailyCheckin::factory()->for($user)->create(['checkin_date' => today()->subDay()]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertSet('saved', false)
            ->assertSet('form.mood', null);
    }

    public function test_notes_are_stored_encrypted(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('form.mood', 3)
            ->set('form.notes', 'Heute war ein harter Tag.')
            ->call('save')
            ->assertSee('Deine Notiz ist verschlüsselt gespeichert.');

        $raw = DB::table('daily_checkins')->value('notes_encrypted');

        $this->assertNotSame('Heute war ein harter Tag.', $raw);
        $this->assertStringNotContainsString('harter Tag', (string) $raw);
        $this->assertSame('Heute war ein harter Tag.', $user->dailyCheckins()->sole()->notes_encrypted);
    }
}
