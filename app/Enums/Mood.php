<?php

namespace App\Enums;

/**
 * Stimmungsskala des Daily Check-ins (1 = sehr schlecht … 5 = sehr gut).
 */
enum Mood: int
{
    case VeryBad = 1;
    case Bad = 2;
    case Okay = 3;
    case Good = 4;
    case VeryGood = 5;

    public function label(): string
    {
        return match ($this) {
            self::VeryBad => 'Sehr schlecht',
            self::Bad => 'Schlecht',
            self::Okay => 'Okay',
            self::Good => 'Gut',
            self::VeryGood => 'Sehr gut',
        };
    }

    public function emoji(): string
    {
        return match ($this) {
            self::VeryBad => '😣',
            self::Bad => '😕',
            self::Okay => '😐',
            self::Good => '🙂',
            self::VeryGood => '😄',
        };
    }
}
