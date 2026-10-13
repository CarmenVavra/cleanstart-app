<?php

namespace Tests\Feature;

use App\Enums\Country;
use App\Models\User;
use Database\Seeders\BadgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountryHotlineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BadgeSeeder::class);
    }

    public function test_new_users_default_to_austria(): void
    {
        $user = new User;

        $this->assertSame(Country::Austria, $user->country);
    }

    public function test_austrian_users_see_the_austrian_hotline(): void
    {
        $user = User::factory()->create(['country' => Country::Austria]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertStatus(200)
            ->assertSee('Telefonseelsorge anrufen')
            ->assertSee('href="tel:142"', false)
            ->assertSee('142 · kostenlos, rund um die Uhr')
            ->assertSee('href="tel:112"', false)
            ->assertDontSee('tel:08001110111', false);
    }

    public function test_german_users_see_the_german_hotline(): void
    {
        $user = User::factory()->create(['country' => Country::Germany]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertStatus(200)
            ->assertSee('href="tel:08001110111"', false)
            ->assertSee('0800 111 0 111 · kostenlos, rund um die Uhr')
            ->assertDontSee('href="tel:142"', false);
    }

    public function test_relapse_page_shows_the_hotline_of_the_users_country(): void
    {
        $user = User::factory()->create(['country' => Country::Germany]);

        $this->actingAs($user)
            ->get(route('relapse.create'))
            ->assertStatus(200)
            ->assertSee('href="tel:08001110111"', false);

        $user->update(['country' => Country::Austria]);

        $this->actingAs($user)
            ->get(route('relapse.create'))
            ->assertSee('href="tel:142"', false);
    }

    public function test_country_can_be_changed_in_the_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertSee('<select', false)
            ->assertSee('<option value="AT" selected>Österreich</option>', false)
            ->assertSee('<option value="DE" >Deutschland</option>', false);

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'username' => $user->username,
                'sobriety_start_at' => now()->subDay()->format('Y-m-d\TH:i'),
                'country' => 'DE',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $this->assertSame(Country::Germany, $user->refresh()->country);
    }

    public function test_unknown_countries_are_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'username' => $user->username,
                'sobriety_start_at' => now()->subDay()->format('Y-m-d\TH:i'),
                'country' => 'FR',
            ])
            ->assertSessionHasErrors('country');

        $this->assertSame(Country::Austria, $user->refresh()->country);
    }

    public function test_country_is_chosen_at_registration_with_austria_preselected(): void
    {
        $this->get(route('register'))
            ->assertSee('<option value="AT" selected>Österreich</option>', false);

        $this->post(route('register.store'), [
            'username' => 'Bergsee',
            'email' => 'bergsee@example.test',
            'password' => 'geheim1234',
            'password_confirmation' => 'geheim1234',
            'sobriety_start_at' => now()->subDay()->format('Y-m-d\TH:i'),
            'country' => 'DE',
            'privacy_consent' => '1',
        ])->assertRedirect(route('dashboard'));

        $this->assertSame(Country::Germany, User::where('username', 'Bergsee')->firstOrFail()->country);
    }

    public function test_country_is_part_of_the_data_export(): void
    {
        $user = User::factory()->create(['country' => Country::Germany]);

        $content = (string) $this->actingAs($user)->get(route('profile.export'))->streamedContent();

        $this->assertSame('Deutschland', json_decode($content, true)['account']['country']);
    }
}
