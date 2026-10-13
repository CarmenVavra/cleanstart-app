<?php

namespace App\Http\Controllers;

use App\Actions\RecordRelapse;
use App\Enums\Trigger;
use App\Http\Requests\RelapseRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RelapseController extends Controller
{
    public function create(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $triggers = Trigger::cases();
        $now = now()->format('Y-m-d\TH:i');
        $currentStart = $user->sobriety_start_at->format('Y-m-d\TH:i');
        $currentDays = (int) floor($user->cleanDays());
        $country = $user->country;

        return view('relapse.create', compact('triggers', 'now', 'currentStart', 'currentDays', 'country'));
    }

    public function store(RelapseRequest $request, RecordRelapse $recordRelapse): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $period = $recordRelapse->handle(
            $user,
            $request->relapsedAt(),
            $request->validated('reflection'),
            $request->triggers(),
        );

        $days = $period->days();
        $word = $days === 1 ? 'Tag' : 'Tage';

        return redirect()->route('dashboard')->with(
            'status',
            "Dein neuer Anfang zählt. Deine bisherige Serie von {$days} {$word} bleibt gespeichert – sie war echt und sie zählt.",
        );
    }
}
