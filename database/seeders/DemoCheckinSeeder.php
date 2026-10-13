<?php

namespace Database\Seeders;

use App\Enums\Mood;
use App\Enums\Trigger;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Vier Wochen Beispiel-Check-ins für das Demo-Konto „Alex“ (nur lokal), damit der
 * Stimmungsverlauf Muster zeigt: Freitage mit Stress sind schwerer, zuletzt geht es bergauf.
 * Tage, an denen bereits ein Check-in existiert, werden nicht verändert.
 */
class DemoCheckinSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('username', 'Alex')->first();

        if ($user === null || app()->isProduction()) {
            return;
        }

        $existing = $user->dailyCheckins()->pluck('checkin_date')->map->toDateString()->all();

        for ($daysAgo = 28; $daysAgo >= 1; $daysAgo--) {
            $date = today()->subDays($daysAgo);

            // Ein paar Lücken, wie im echten Leben
            if ($daysAgo % 5 === 0 || in_array($date->toDateString(), $existing, true)) {
                continue;
            }

            $mood = $daysAgo < 14 ? 4 : 3;
            $triggers = [];

            if ($date->isFriday()) {
                $mood -= 2;
                $triggers[] = Trigger::Stress;
            }

            if ($date->isSunday()) {
                $mood -= 1;
                $triggers[] = Trigger::Loneliness;
            }

            if ($daysAgo % 3 === 0) {
                $triggers[] = Trigger::Fatigue;
            }

            $user->dailyCheckins()->create([
                'checkin_date' => $date,
                'mood_rating' => Mood::from(max(1, min(5, $mood))),
                'triggers' => array_map(fn (Trigger $trigger) => $trigger->value, $triggers),
            ]);
        }
    }
}
