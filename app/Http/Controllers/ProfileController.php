<?php

namespace App\Http\Controllers;

use App\Actions\DeleteAccount;
use App\Actions\ExportUserData;
use App\Actions\SyncUnlockedBadges;
use App\Enums\Country;
use App\Enums\SpendingPeriod;
use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $periods = $user->sobrietyPeriods()->latest('ended_at')->get();
        $countries = Country::options();
        $spendingPeriods = SpendingPeriod::options();
        $spendingCents = $user->spendingCents();
        $spendingAmount = $spendingCents === null ? null : number_format($spendingCents / 100, 2, ',', '');
        $spendingPeriod = ($user->spending_period ?? SpendingPeriod::Week)->value;

        return view('profile.edit', compact('user', 'periods', 'countries', 'spendingPeriods', 'spendingAmount', 'spendingPeriod'));
    }

    public function update(UpdateProfileRequest $request, SyncUnlockedBadges $syncUnlockedBadges): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->update($request->profileData());

        // Ein korrigiertes Startdatum wirkt sich direkt auf die Abzeichen aus.
        $syncUnlockedBadges->handle($user);

        return redirect()->route('profile.edit')->with('status', 'Dein Profil wurde gespeichert.');
    }

    /**
     * Download aller eigenen Daten als JSON (Art. 20 DSGVO).
     */
    public function export(Request $request, ExportUserData $exportUserData): StreamedResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $exportUserData->handle($user);
        $filename = 'cleanstart-meine-daten-'.now()->format('Y-m-d').'.json';

        return response()->streamDownload(
            function () use ($data) {
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            },
            $filename,
            ['Content-Type' => 'application/json; charset=UTF-8'],
        );
    }

    /**
     * Konto endgültig löschen (Art. 17 DSGVO).
     */
    public function destroy(DeleteAccountRequest $request, DeleteAccount $deleteAccount): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        Auth::guard('web')->logout();

        $deleteAccount->handle($user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Dein Konto und alle zugehörigen Daten wurden gelöscht. Pass gut auf dich auf.');
    }
}
