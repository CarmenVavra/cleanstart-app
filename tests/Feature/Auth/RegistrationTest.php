<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'username' => 'Leuchtturm',
            'email' => 'leuchtturm@example.test',
            'password' => 'geheim1234',
            'password_confirmation' => 'geheim1234',
            'sobriety_start_at' => now()->subDays(3)->format('Y-m-d\TH:i'),
        ], $overrides);
    }

    public function test_registration_page_renders_with_anonymity_hint(): void
    {
        $this->get(route('register'))
            ->assertStatus(200)
            ->assertSee('Bitte wähle einen anonymen Namen');
    }

    public function test_new_users_can_register(): void
    {
        $this->post(route('register.store'), $this->validPayload())
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();

        $user = User::firstWhere('username', 'Leuchtturm');
        $this->assertNotNull($user);
        $this->assertSame(3, (int) round($user->cleanDays()));
    }

    public function test_sobriety_start_must_not_be_in_the_future(): void
    {
        $this->post(route('register.store'), $this->validPayload([
            'sobriety_start_at' => now()->addDay()->format('Y-m-d\TH:i'),
        ]))->assertSessionHasErrors('sobriety_start_at');

        $this->assertGuest();
    }

    public function test_username_must_be_unique_and_safe(): void
    {
        User::factory()->create(['username' => 'Leuchtturm']);

        $this->post(route('register.store'), $this->validPayload())
            ->assertSessionHasErrors('username');

        $this->post(route('register.store'), $this->validPayload(['username' => "x' OR 1=1 --"]))
            ->assertSessionHasErrors('username');

        $this->assertSame(1, User::count());
    }

    public function test_password_must_be_confirmed(): void
    {
        $this->post(route('register.store'), $this->validPayload(['password_confirmation' => 'anders1234']))
            ->assertSessionHasErrors('password');
    }
}
