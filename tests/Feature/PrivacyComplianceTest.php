<?php

namespace Tests\Feature;

use App\Models\User;
use App\Session\PrivacyDatabaseSessionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Prüft, dass der Code hält, was die Datenschutzerklärung verspricht.
 */
class PrivacyComplianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_sessions_do_not_store_ip_address_or_user_agent(): void
    {
        config(['session.driver' => 'database']);

        $handler = app('session')->driver('database')->getHandler();
        $this->assertInstanceOf(PrivacyDatabaseSessionHandler::class, $handler);

        // Eine Altlast mit IP-Adresse wird beim nächsten Schreiben bereinigt.
        DB::table('sessions')->insert([
            'id' => 'test-session-id', 'user_id' => null, 'ip_address' => '203.0.113.5',
            'user_agent' => 'Testbrowser', 'payload' => 'x', 'last_activity' => now()->getTimestamp(),
        ]);
        $handler->read('test-session-id');
        $handler->write('test-session-id', 'payload');

        $row = DB::table('sessions')->where('id', 'test-session-id')->first();
        $this->assertNotNull($row);
        $this->assertNull($row->ip_address);
        $this->assertNull($row->user_agent);
    }

    public function test_remember_me_cookie_is_valid_for_thirty_days(): void
    {
        $user = User::factory()->create(['username' => 'Leuchtturm']);

        $response = $this->post(route('login.store'), [
            'username' => 'Leuchtturm',
            'password' => 'password',
            'remember' => '1',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        $recaller = collect($response->headers->getCookies())
            ->first(fn ($cookie) => $cookie->getName() === Auth::guard('web')->getRecallerName());

        $this->assertNotNull($recaller);
        $this->assertEqualsWithDelta(now()->addMinutes(30 * 24 * 60)->getTimestamp(), $recaller->getExpiresTime(), 120);
    }

    public function test_failed_logins_are_counted_under_a_hashed_key(): void
    {
        User::factory()->create(['username' => 'Leuchtturm']);

        $this->post(route('login.store'), ['username' => 'Leuchtturm', 'password' => 'falsch']);

        $hashedKey = 'login:'.hash('sha256', Str::transliterate('leuchtturm|127.0.0.1'));
        $this->assertSame(1, RateLimiter::attempts($hashedKey));
        $this->assertSame(0, RateLimiter::attempts('leuchtturm|127.0.0.1'));
    }

    public function test_prune_command_removes_expired_technical_data_only(): void
    {
        $now = now()->getTimestamp();
        $lifetime = (int) config('session.lifetime') * 60;

        DB::table('sessions')->insert([
            ['id' => 'alt', 'user_id' => null, 'ip_address' => null, 'user_agent' => null, 'payload' => 'x', 'last_activity' => $now - $lifetime - 60],
            ['id' => 'aktiv', 'user_id' => null, 'ip_address' => null, 'user_agent' => null, 'payload' => 'x', 'last_activity' => $now],
        ]);
        DB::table('cache')->insert([
            ['key' => 'abgelaufen', 'value' => '1', 'expiration' => $now - 10],
            ['key' => 'gueltig', 'value' => '1', 'expiration' => $now + 600],
        ]);
        DB::table('password_reset_tokens')->insert([
            ['email' => 'alt@example.test', 'token' => 'x', 'created_at' => now()->subHours(2)],
            ['email' => 'neu@example.test', 'token' => 'y', 'created_at' => now()],
        ]);

        $this->artisan('cleanstart:prune')->assertSuccessful();

        $this->assertSame(['aktiv'], DB::table('sessions')->pluck('id')->all());
        $this->assertSame(['gueltig'], DB::table('cache')->pluck('key')->all());
        $this->assertSame(['neu@example.test'], DB::table('password_reset_tokens')->pluck('email')->all());
    }

    public function test_prune_command_is_scheduled_hourly(): void
    {
        $this->artisan('schedule:list')
            ->expectsOutputToContain('cleanstart:prune')
            ->assertSuccessful();
    }

    public function test_sqlite_overwrites_deleted_content(): void
    {
        $this->assertSame(1, (int) DB::selectOne('PRAGMA secure_delete')->secure_delete);
    }

    public function test_privacy_policy_describes_cookies_retention_and_processors(): void
    {
        $this->get(route('legal.privacy'))
            ->assertStatus(200)
            ->assertSee('nur zum Zurücksetzen deines Passworts')
            ->assertSee('das 30 Tage gültig ist und beim Abmelden gelöscht wird')
            ->assertSee('nur als Prüfsumme (Hash)')
            ->assertSee('Darüber hinaus speichert CleanStart deine IP-Adresse nicht.')
            ->assertSee('LEGAL_BACKUP_RETENTION_DAYS')
            ->assertSee('LEGAL_MAIL_PROVIDER')
            ->assertSee('spätestens nach einer Stunde')
            ->assertSee('Suchtdruck und früheren Ausgaben');
    }

    public function test_registration_consent_covers_all_health_data(): void
    {
        $this->get(route('register'))
            ->assertSee('Stimmung, Triggern, Abstinenz, Suchtdruck und')
            ->assertSee('früheren Ausgaben für die Sucht (Gesundheitsdaten)');
    }
}
