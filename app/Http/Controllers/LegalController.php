<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Impressum und Datenschutzerklärung (öffentlich, auch ohne Anmeldung erreichbar).
 */
class LegalController extends Controller
{
    public function imprint(Request $request): View
    {
        return $this->page('legal.imprint', $request);
    }

    public function privacy(Request $request): View
    {
        return $this->page('legal.privacy', $request);
    }

    private function page(string $view, Request $request): View
    {
        $legal = config('legal');
        $loggedIn = $request->user() !== null;
        $back = $loggedIn ? route('dashboard') : route('login');
        $backLabel = $loggedIn ? 'Zurück zum Dashboard' : 'Zurück zur Anmeldung';

        return view($view, compact('legal', 'back', 'backLabel'));
    }
}
