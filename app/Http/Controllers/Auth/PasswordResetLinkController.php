<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(ForgotPasswordRequest $request): RedirectResponse
    {
        Password::sendResetLink($request->only('email'));

        // Bewusst immer dieselbe Antwort: verrät nicht, ob eine E-Mail-Adresse registriert ist.
        return back()->with('status', 'Falls ein Konto mit dieser E-Mail-Adresse existiert, haben wir dir einen Link zum Zurücksetzen geschickt.');
    }
}
