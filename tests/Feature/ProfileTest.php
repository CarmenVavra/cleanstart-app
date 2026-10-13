<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\BadgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_renders(): void
    {
        $user = User::factory()->withTrustedContact('Sabine')->create(['username' => 'Alex']);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertStatus(200)
            ->assertSee('Dein Profil')
            ->assertSee('value="Alex"', false)
            ->assertSee('value="Sabine"', false)
            ->assertSee('name="_token"', false);
    }

    public function test_profile_can_be_updated_and_contact_is_encrypted(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'username' => 'NeuerWeg',
                'sobriety_start_at' => now()->subDays(2)->format('Y-m-d\TH:i'),
                'trusted_contact_name' => 'Kai',
                'trusted_contact_phone' => '+49 151 2345678',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status');

        $user->refresh();
        $this->assertSame('NeuerWeg', $user->username);
        $this->assertSame('Kai', $user->trusted_contact_name);
        $this->assertSame('tel:+491512345678', $user->trustedContactTelHref());

        $rawPhone = DB::table('users')->where('id', $user->id)->value('trusted_contact_phone');
        $this->assertStringNotContainsString('2345678', (string) $rawPhone);
    }

    public function test_resetting_the_start_date_removes_badges_that_are_no_longer_reached(): void
    {
        $this->seed(BadgeSeeder::class);
        $user = User::factory()->create(['sobriety_start_at' => now()->subDays(40)]);

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'username' => $user->username,
                'sobriety_start_at' => now()->subDays(4)->format('Y-m-d\TH:i'),
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $this->assertEqualsCanonicalizing([1, 3], $user->badges()->pluck('days_required')->all());
    }

    public function test_phone_number_must_be_valid_and_contact_complete(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'username' => $user->username,
                'sobriety_start_at' => now()->subDay()->format('Y-m-d\TH:i'),
                'trusted_contact_name' => 'Kai',
                'trusted_contact_phone' => 'javascript:alert(1)',
            ])
            ->assertSessionHasErrors('trusted_contact_phone');

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'username' => $user->username,
                'sobriety_start_at' => now()->subDay()->format('Y-m-d\TH:i'),
                'trusted_contact_name' => 'Kai',
            ])
            ->assertSessionHasErrors('trusted_contact_phone');
    }

    public function test_username_must_stay_unique(): void
    {
        User::factory()->create(['username' => 'Vergeben']);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'username' => 'Vergeben',
                'sobriety_start_at' => now()->subDay()->format('Y-m-d\TH:i'),
            ])
            ->assertSessionHasErrors('username');
    }

    public function test_guests_cannot_access_the_profile(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
        $this->put(route('profile.update'))->assertRedirect(route('login'));
    }
}
