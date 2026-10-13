<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Country;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function create(): View
    {
        $defaultStart = now()->format('Y-m-d\TH:i');
        $countries = Country::options();
        $defaultCountry = Country::DEFAULT->value;

        return view('auth.register', compact('defaultStart', 'countries', 'defaultCountry'));
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            ...$request->safe()->except('privacy_consent'),
            'privacy_accepted_at' => now(),
        ]);

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
