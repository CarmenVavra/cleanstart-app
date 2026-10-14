<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Setzt Sicherheits-Header für jede Antwort der App.
 *
 * Bewusst ohne Einschränkung von Skripten und Styles: Alpine.js und Livewire
 * benötigen dynamisch erzeugten Code, eine strenge script-src würde sie brechen.
 */
class SecurityHeaders
{
    /** HSTS-Dauer: ein Jahr. */
    public const HSTS_MAX_AGE = 31536000;

    /** @var array<string, string> */
    private const HEADERS = [
        // Keine fremde Seite darf CleanStart einbetten (Schutz vor Clickjacking).
        'Content-Security-Policy' => "frame-ancestors 'none'; base-uri 'self'; form-action 'self'; object-src 'none'",
        'X-Frame-Options' => 'DENY',
        // Dateien nur mit dem angegebenen Typ ausführen.
        'X-Content-Type-Options' => 'nosniff',
        // Externe Seiten erfahren nicht, dass jemand von CleanStart kommt.
        'Referrer-Policy' => 'same-origin',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), browsing-topics=()',
        'Cross-Origin-Opener-Policy' => 'same-origin',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        foreach (self::HEADERS as $name => $value) {
            $response->headers->set($name, $value, false);
        }

        // Nur über HTTPS sinnvoll: der Browser nutzt danach ausschließlich HTTPS.
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age='.self::HSTS_MAX_AGE, false);
        }

        // Seiten mit Gesundheitsdaten nicht im Browser-Cache ablegen (z. B. geteilte Geräte).
        if ($request->user() !== null) {
            $response->headers->set('Cache-Control', 'no-store, private');
        }

        return $response;
    }
}
