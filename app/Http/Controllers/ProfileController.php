<?php

namespace App\Http\Controllers;

use App\Actions\SyncUnlockedBadges;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('profile.edit', compact('user'));
    }

    public function update(UpdateProfileRequest $request, SyncUnlockedBadges $syncUnlockedBadges): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->update($request->validated());

        // Ein geändertes Startdatum (z. B. nach einem Rückfall) wirkt sich direkt auf die Abzeichen aus.
        $syncUnlockedBadges->handle($user);

        return redirect()->route('profile.edit')->with('status', 'Dein Profil wurde gespeichert.');
    }
}
