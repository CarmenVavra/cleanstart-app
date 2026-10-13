<?php

namespace App\Actions;

use App\Models\Badge;
use App\Models\CopingItem;
use App\Models\DailyCheckin;
use App\Models\SobrietyPeriod;
use App\Models\User;

/**
 * Stellt alle personenbezogenen Daten eines Nutzers in maschinenlesbarer Form zusammen
 * (Recht auf Datenübertragbarkeit, Art. 20 DSGVO). Verschlüsselte Felder werden entschlüsselt.
 */
class ExportUserData
{
    /**
     * @return array<string, mixed>
     */
    public function handle(User $user): array
    {
        $user->load([
            'dailyCheckins' => fn ($query) => $query->orderBy('checkin_date'),
            'sobrietyPeriods' => fn ($query) => $query->orderBy('started_at'),
            'copingItems' => fn ($query) => $query->orderBy('id'),
            'badges' => fn ($query) => $query->orderBy('days_required'),
        ]);

        return [
            'exported_at' => now()->toIso8601String(),
            'account' => [
                'username' => $user->username,
                'email' => $user->email,
                'sobriety_start_at' => $user->sobriety_start_at->toIso8601String(),
                'privacy_accepted_at' => $user->privacy_accepted_at?->toIso8601String(),
                'created_at' => $user->created_at?->toIso8601String(),
            ],
            'trusted_contact' => [
                'name' => $user->trusted_contact_name,
                'phone' => $user->trusted_contact_phone,
            ],
            'daily_checkins' => $user->dailyCheckins->map(fn (DailyCheckin $checkin) => [
                'date' => $checkin->checkin_date->toDateString(),
                'mood_rating' => $checkin->mood_rating->value,
                'mood_label' => $checkin->mood_rating->label(),
                'triggers' => $checkin->triggerLabels(),
                'notes' => $checkin->notes_encrypted,
            ])->all(),
            'previous_streaks' => $user->sobrietyPeriods->map(fn (SobrietyPeriod $period) => [
                'started_at' => $period->started_at->toIso8601String(),
                'ended_at' => $period->ended_at->toIso8601String(),
                'days' => $period->days(),
                'reflection' => $period->reflection_encrypted,
                'triggers' => $period->triggerLabels(),
            ])->all(),
            'emergency_plan' => $user->copingItems->map(fn (CopingItem $item) => [
                'type' => $item->type->label(),
                'content' => $item->content,
            ])->all(),
            'unlocked_badges' => $user->badges->map(fn (Badge $badge) => [
                'title' => $badge->title,
                'unlocked_at' => $badge->getRelationValue('pivot')?->getAttribute('unlocked_at'),
            ])->all(),
        ];
    }
}
