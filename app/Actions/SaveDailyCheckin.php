<?php

namespace App\Actions;

use App\Enums\Mood;
use App\Enums\Trigger;
use App\Models\DailyCheckin;
use App\Models\User;

/**
 * Speichert (bzw. aktualisiert) den Check-in des heutigen Tages – maximal einer pro Tag.
 */
class SaveDailyCheckin
{
    /**
     * @param  list<Trigger>  $triggers
     */
    public function handle(User $user, Mood $mood, array $triggers, ?string $notes = null): DailyCheckin
    {
        $checkin = $user->todaysCheckin() ?? $user->dailyCheckins()->make(['checkin_date' => today()]);

        $checkin->fill([
            'mood_rating' => $mood,
            'triggers' => array_values(array_unique(array_map(fn (Trigger $trigger) => $trigger->value, $triggers))),
            'notes_encrypted' => filled($notes) ? trim($notes) : null,
        ])->save();

        return $checkin;
    }
}
