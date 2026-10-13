<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_links_to_password_reset(): void
    {
        $this->get(route('login'))
            ->assertSee('Passwort vergessen?')
            ->assertSee(route('password.request'), false);
    }

    public function test_forgot_password_page_renders(): void
    {
        $this->get(route('password.request'))
            ->assertStatus(200)
            ->assertSee('Link senden')
            ->assertSee('name="_token"', false);
    }

    public function test_reset_link_is_sent_to_existing_users(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'mond@example.test']);

        $this->post(route('password.email'), ['email' => 'mond@example.test'])
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_response_does_not_reveal_whether_an_email_is_registered(): void
    {
        Notification::fake();
        User::factory()->create(['email' => 'mond@example.test']);

        $this->post(route('password.email'), ['email' => 'mond@example.test'])->assertSessionHas('status');
        $knownStatus = session('status');

        $this->post(route('password.email'), ['email' => 'niemand@example.test'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', $knownStatus);
    }

    public function test_password_can_be_reset_with_a_valid_token(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'mond@example.test']);

        $this->post(route('password.email'), ['email' => 'mond@example.test']);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $this->get(route('password.reset', ['token' => $notification->token, 'email' => $user->email]))
                ->assertStatus(200)
                ->assertSee('Neues Passwort festlegen');

            $this->post(route('password.store'), [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'neuesPasswort1',
                'password_confirmation' => 'neuesPasswort1',
            ])
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            return true;
        });

        $this->assertTrue(Hash::check('neuesPasswort1', $user->refresh()->password));
    }

    public function test_invalid_token_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->post(route('password.store'), [
            'token' => 'ungueltig',
            'email' => $user->email,
            'password' => 'neuesPasswort1',
            'password_confirmation' => 'neuesPasswort1',
        ])->assertSessionHasErrors(['email' => 'Dieser Link ist ungültig oder abgelaufen. Bitte fordere einen neuen an.']);

        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }
}
