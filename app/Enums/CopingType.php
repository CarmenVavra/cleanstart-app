<?php

namespace App\Enums;

/**
 * Kategorien des persönlichen Notfallplans.
 */
enum CopingType: string
{
    case Reason = 'reason';
    case Strategy = 'strategy';

    /** Maximale Anzahl Einträge pro Kategorie – der Notfallplan soll schnell erfassbar bleiben. */
    public const MAX_ITEMS = 10;

    public function label(): string
    {
        return match ($this) {
            self::Reason => 'Mein Warum',
            self::Strategy => 'Was mir hilft',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Reason => 'Wofür lohnt es sich, clean zu bleiben? Menschen, Ziele, Träume.',
            self::Strategy => 'Was hilft dir, wenn der Suchtdruck kommt? Kleine, konkrete Schritte.',
        };
    }

    public function placeholder(): string
    {
        return match ($this) {
            self::Reason => 'z. B. Meine Tochter soll stolz auf mich sein',
            self::Strategy => 'z. B. 10 Minuten spazieren gehen',
        };
    }
}
