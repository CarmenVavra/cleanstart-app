<?php

namespace Tests\Feature;

use App\Models\CopingItem;
use App\Models\DailyCheckin;
use App\Models\SobrietyPeriod;
use App\Models\User;
use Database\Seeders\BadgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AccountDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_offers_export_and_deletion(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertSee('Meine Daten herunterladen')
            ->assertSee('Konto endgültig löschen');
    }

    public function test_users_can_download_all_their_data_decrypted(): void
    {
        $this->seed(BadgeSeeder::class);
        $user = User::factory()->withTrustedContact('Sabine', '+49 170 1234567')->create(['username' => 'Alex']);
        DailyCheckin::factory()->for($user)->create(['notes_encrypted' => 'Geheime Notiz', 'triggers' => ['stress']]);
        SobrietyPeriod::factory()->for($user)->create(['reflection_encrypted' => 'Meine Reflexion']);
        CopingItem::factory()->for($user)->reason()->create(['content' => 'Für meine Familie']);
        $user->badges()->attach(1, ['unlocked_at' => now()]);

        $response = $this->actingAs($user)->get(route('profile.export'));

        $response->assertOk()->assertDownload('cleanstart-meine-daten-'.now()->format('Y-m-d').'.json');

        $data = json_decode((string) $response->streamedContent(), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('Alex', $data['account']['username']);
        $this->assertSame('Sabine', $data['trusted_contact']['name']);
        $this->assertSame('Geheime Notiz', $data['daily_checkins'][0]['notes']);
        $this->assertSame(['Stress'], $data['daily_checkins'][0]['triggers']);
        $this->assertSame('Meine Reflexion', $data['previous_streaks'][0]['reflection']);
        $this->assertSame('Für meine Familie', $data['emergency_plan'][0]['content']);
        $this->assertSame('24 Stunden', $data['unlocked_badges'][0]['title']);
        $this->assertArrayNotHasKey('password', $data['account']);
    }

    public function test_export_contains_only_own_data(): void
    {
        $user = User::factory()->create();
        DailyCheckin::factory()->for(User::factory())->create(['notes_encrypted' => 'Fremde Notiz']);

        $content = (string) $this->actingAs($user)->get(route('profile.export'))->streamedContent();

        $this->assertStringNotContainsString('Fremde Notiz', $content);
    }

    public function test_account_deletion_requires_the_correct_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->delete(route('profile.destroy'), ['password' => 'falsch'])
            ->assertSessionHasErrorsIn('accountDeletion', 'password')
            ->assertRedirect(route('profile.edit'));

        $this->assertNotNull($user->fresh());
        $this->assertAuthenticated();
    }

    public function test_users_can_delete_their_account_with_all_data(): void
    {
        $this->seed(BadgeSeeder::class);
        $user = User::factory()->create();
        $other = User::factory()->create();
        DailyCheckin::factory()->for($user)->create();
        DailyCheckin::factory()->for($other)->create();
        SobrietyPeriod::factory()->for($user)->create();
        CopingItem::factory()->for($user)->create();
        $user->badges()->attach(1, ['unlocked_at' => now()]);
        DB::table('password_reset_tokens')->insert(['email' => $user->email, 'token' => 'x', 'created_at' => now()]);

        $this->actingAs($user)
            ->delete(route('profile.destroy'), ['password' => 'password'])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->assertGuest();
        $this->assertNull($user->fresh());
        $this->assertSame(0, DailyCheckin::where('user_id', $user->id)->count());
        $this->assertSame(0, SobrietyPeriod::where('user_id', $user->id)->count());
        $this->assertSame(0, CopingItem::where('user_id', $user->id)->count());
        $this->assertSame(0, DB::table('badge_user')->where('user_id', $user->id)->count());
        $this->assertSame(0, DB::table('password_reset_tokens')->where('email', $user->email)->count());

        // Daten anderer Nutzer bleiben unberührt.
        $this->assertSame(1, DailyCheckin::where('user_id', $other->id)->count());
    }

    public function test_guests_cannot_export_or_delete(): void
    {
        $this->get(route('profile.export'))->assertRedirect(route('login'));
        $this->delete(route('profile.destroy'))->assertRedirect(route('login'));
    }
}
