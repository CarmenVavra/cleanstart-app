<?php

namespace App\Actions;

use App\Models\CravingEpisode;
use App\Models\User;

/**
 * Speichert einen mit dem Craving-Timer überstandenen Suchtdruck.
 *
 * Da ein Timer mindestens CravingEpisode::TIMER_MINUTES läuft, wird innerhalb dieser Zeit
 * kein zweiter Eintrag angelegt (Schutz vor Doppelklicks und Mehrfach-Aufrufen).
 */
class RecordOvercomeCraving
{
    /**
     * @return int Anzahl aller überstandenen Suchtdruck-Momente
     */
    public function handle(User $user): int
    {
        $recentlyRecorded = $user->cravingEpisodes()
            ->where('overcome_at', '>', now()->subMinutes(CravingEpisode::TIMER_MINUTES))
            ->exists();

        if (! $recentlyRecorded) {
            $user->cravingEpisodes()->create(['overcome_at' => now()]);
        }

        return $user->cravingEpisodes()->count();
    }
}
