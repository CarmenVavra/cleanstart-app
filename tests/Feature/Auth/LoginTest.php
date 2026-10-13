<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_dashboard_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_login_page_renders(): void
    {
        $this->get(route('login'))
            ->assertStatus(200)
            ->assertSee('Willkommen zurück')
            ->assertSee('name="_token"', false);
    }

    public function test_users_can_log_in_with_their_username(): void
    {
        $user = User::factory()->create(['username' => 'Morgenrot']);

        $this->post(route('login.store'), ['username' => 'Morgenrot', 'password' => 'password'])
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_users_cannot_log_in_with_a_wrong_password(): void
    {
        User::factory()->create(['username' => 'Morgenrot']);

        $this->from(route('login'))
            ->post(route('login.store'), ['username' => 'Morgenrot', 'password' => 'falsch'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['username' => 'Benutzername oder Passwort sind nicht korrekt.']);

        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_five_failed_attempts(): void
    {
        User::factory()->create(['username' => 'Morgenrot']);

        foreach (range(1, 5) as $attempt) {
            $this->post(route('login.store'), ['username' => 'Morgenrot', 'password' => 'falsch']);
        }

        $this->post(route('login.store'), ['username' => 'Morgenrot', 'password' => 'password'])
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_users_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
