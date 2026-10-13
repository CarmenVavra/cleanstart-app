<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function manifest(): array
    {
        return json_decode((string) file_get_contents(public_path('manifest.json')), true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * Pfade aus der PRECACHE-Liste des Service Workers.
     *
     * @return list<string>
     */
    private function precachedPaths(): array
    {
        $sw = (string) file_get_contents(public_path('sw.js'));
        preg_match('/const PRECACHE = \[(.*?)\];/s', $sw, $match);
        preg_match_all("/'([^']+)'/", $match[1] ?? '', $paths);

        return $paths[1];
    }

    public function test_manifest_describes_an_installable_app(): void
    {
        $manifest = $this->manifest();

        $this->assertSame('CleanStart', $manifest['short_name']);
        $this->assertSame('/', $manifest['start_url']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('de', $manifest['lang']);

        $purposes = [];
        foreach ($manifest['icons'] as $icon) {
            $path = public_path(ltrim($icon['src'], '/'));
            $this->assertFileExists($path);

            [$width, $height] = (array) getimagesize($path);
            $this->assertSame($icon['sizes'], "{$width}x{$height}");
            $purposes[] = $icon['purpose'];
        }

        $this->assertContains('maskable', $purposes);
        $this->assertContains('any', $purposes);
    }

    public function test_pages_link_manifest_and_icons(): void
    {
        $this->get(route('login'))
            ->assertSee('<link rel="manifest" href="/manifest.json">', false)
            ->assertSee('<link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">', false);

        $this->assertFileExists(public_path('icons/apple-touch-icon.png'));
        $this->assertSame("\x00\x00\x01\x00", substr((string) file_get_contents(public_path('favicon.ico')), 0, 4));
    }

    public function test_service_worker_only_precaches_public_files(): void
    {
        $paths = $this->precachedPaths();

        $this->assertContains('/offline.html', $paths);

        foreach ($paths as $path) {
            $this->assertFileExists(public_path(ltrim($path, '/')), "{$path} fehlt in public/");
            $this->assertDoesNotMatchRegularExpression('#^/(profil|verlauf|notfallplan|neustart)?$#', $path);
        }

        $sw = (string) file_get_contents(public_path('sw.js'));
        $this->assertStringContainsString("request.mode === 'navigate'", $sw);
        $this->assertStringContainsString("caches.match('/offline.html')", $sw);
    }

    public function test_offline_page_offers_emergency_numbers_without_external_resources(): void
    {
        $html = (string) file_get_contents(public_path('offline.html'));

        $this->assertStringContainsString('href="tel:142"', $html);
        $this->assertStringContainsString('href="tel:08001110111"', $html);
        $this->assertStringContainsString('href="tel:112"', $html);
        $this->assertStringContainsString('Atemübung 4-7-8', $html);
        $this->assertDoesNotMatchRegularExpression('#(src|href)="https?://#', $html);
    }

    public function test_profile_offers_app_installation(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('profile.edit'))
            ->assertSee('Als App installieren')
            ->assertSee('x-data="installApp"', false);
    }

    public function test_privacy_policy_explains_offline_storage(): void
    {
        $this->get(route('legal.privacy'))
            ->assertSee('Installierbare App &amp; Offline-Modus', false)
            ->assertSee('keine persönlichen Daten');
    }
}
