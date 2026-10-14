<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_send_security_headers(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'same-origin')
            ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin')
            ->assertHeader('Content-Security-Policy', "frame-ancestors 'none'; base-uri 'self'; form-action 'self'; object-src 'none'")
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), browsing-topics=()');
    }

    public function test_hsts_is_only_sent_over_https(): void
    {
        $this->get(route('login'))->assertHeaderMissing('Strict-Transport-Security');

        $this->get('https://localhost/anmelden')
            ->assertOk()
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000');
    }

    public function test_unknown_pages_also_send_security_headers(): void
    {
        $this->get('/gibt-es-nicht')
            ->assertNotFound()
            ->assertHeader('X-Frame-Options', 'DENY');
    }

    public function test_pages_of_signed_in_users_are_not_cached_by_the_browser(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_guest_pages_keep_default_caching(): void
    {
        $response = $this->get(route('legal.imprint'))->assertOk();

        $this->assertStringNotContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }
}
