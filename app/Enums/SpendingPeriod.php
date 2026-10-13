<?php

namespace App\Enums;

/**
 * Bezugszeitraum für die früheren Ausgaben im Ersparnis-Rechner.
 */
enum SpendingPeriod: string
{
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';

    /** Durchschnittliche Monatslänge in Tagen (365,2425 / 12). */
    public const DAYS_PER_MONTH = 30.436875;

    /**
     * Auswahlliste für Formulare.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    public function label(): string
    {
        return match ($this) {
            self::Day => 'pro Tag',
            self::Week => 'pro Woche',
            self::Month => 'pro Monat',
        };
    }

    public function days(): float
    {
        return match ($this) {
            self::Day => 1,
            self::Week => 7,
            self::Month => self::DAYS_PER_MONTH,
        };
    }
}
