<?php

namespace App\Actions;

use App\Models\Badge;
use App\Models\User;

/**
 * Gleicht die freigeschalteten Abzeichen eines Nutzers mit seiner aktuellen Clean-Zeit ab.
 *
 * Neue Meilensteine werden mit ihrem tatsächlichen Erreichungszeitpunkt gespeichert;
 * wird das Startdatum zurückgesetzt (z. B. nach einem Rückfall), entfallen nicht mehr
 * erreichte Abzeichen wieder.
 */
class SyncUnlockedBadges
{
    public function handle(User $user): void
    {
        $cleanDays = $user->cleanDays();

        $unlocked = Badge::query()
            ->where('days_required', '<=', $cleanDays)
            ->get()
            ->mapWithKeys(fn (Badge $badge) => [
                $badge->id => ['unlocked_at' => $user->sobriety_start_at->copy()->addDays($badge->days_required)],
            ])
            ->all();

        $user->badges()->sync($unlocked);
    }
}
