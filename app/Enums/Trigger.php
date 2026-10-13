<?php

namespace App\Enums;

/**
 * Vordefinierte Risikofaktoren (Trigger-Tags) für den Daily Check-in.
 */
enum Trigger: string
{
    case Stress = 'stress';
    case Loneliness = 'loneliness';
    case Fatigue = 'fatigue';
    case Boredom = 'boredom';
    case Anger = 'anger';
    case Hunger = 'hunger';
    case Craving = 'craving';
    case SocialSituation = 'social_situation';

    public function label(): string
    {
        return match ($this) {
            self::Stress => 'Stress',
            self::Loneliness => 'Einsamkeit',
            self::Fatigue => 'Müdigkeit',
            self::Boredom => 'Langeweile',
            self::Anger => 'Wut',
            self::Hunger => 'Hunger',
            self::Craving => 'Suchtdruck',
            self::SocialSituation => 'Soziale Situation',
        };
    }

    /**
     * Wandelt eine Liste von Slugs in ihre deutschen Labels um (unbekannte Werte werden ignoriert).
     *
     * @param  array<int, string>  $values
     * @return list<string>
     */
    public static function labelsFor(array $values): array
    {
        $labels = [];

        foreach (self::cases() as $case) {
            if (in_array($case->value, $values, true)) {
                $labels[] = $case->label();
            }
        }

        return $labels;
    }
}
