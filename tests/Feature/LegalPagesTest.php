<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_imprint_and_privacy_are_public(): void
    {
        $this->get(route('legal.imprint'))->assertOk()->assertSee('Impressum')->assertSee('Zurück zur Anmeldung');
        $this->get(route('legal.privacy'))->assertOk()->assertSee('Datenschutzerklärung')->assertSee('Art. 9 Abs. 2 lit. a DSGVO');
    }

    public function test_missing_operator_details_are_shown_as_placeholders(): void
    {
        config(['legal.operator' => null]);

        $this->get(route('legal.imprint'))->assertSee('[LEGAL_OPERATOR in .env ergänzen]');
    }

    public function test_configured_operator_details_are_shown_escaped(): void
    {
        config([
            'legal.operator' => 'Verein <b>Neustart</b>',
            'legal.email' => 'kontakt@example.test',
        ]);

        $this->get(route('legal.imprint'))
            ->assertSee('Verein &lt;b&gt;Neustart&lt;/b&gt;', false)
            ->assertSee('mailto:kontakt@example.test', false)
            ->assertDontSee('[LEGAL_OPERATOR in .env ergänzen]');
    }

    public function test_legal_links_appear_on_login_and_dashboard(): void
    {
        $this->get(route('login'))->assertSee(route('legal.imprint'), false)->assertSee(route('legal.privacy'), false);

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertSee(route('legal.privacy'), false);
    }

    public function test_logged_in_users_get_a_back_link_to_the_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('legal.privacy'))
            ->assertSee('Zurück zum Dashboard');
    }
}
