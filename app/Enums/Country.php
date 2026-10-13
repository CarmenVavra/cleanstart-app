<?php

namespace App\Enums;

/**
 * Land der Nutzerin bzw. des Nutzers – bestimmt die angezeigten Hilfsnummern.
 */
enum Country: string
{
    case Austria = 'AT';
    case Germany = 'DE';

    /** Voreinstellung für neue Konten. */
    public const DEFAULT = self::Austria;

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
            self::Austria => 'Österreich',
            self::Germany => 'Deutschland',
        };
    }

    /**
     * Krisen-Hotline, die im Notfall-Fenster an erster Stelle steht.
     */
    public function hotlineName(): string
    {
        return 'Telefonseelsorge';
    }

    /**
     * Hotline-Nummer in lesbarer Schreibweise.
     */
    public function hotlineNumber(): string
    {
        return match ($this) {
            self::Austria => '142',
            self::Germany => '0800 111 0 111',
        };
    }

    public function hotlineNote(): string
    {
        return 'kostenlos, rund um die Uhr';
    }

    public function hotlineTelHref(): string
    {
        return self::telHref($this->hotlineNumber());
    }

    /**
     * Europaweiter Notruf – in beiden Ländern erreichbar.
     */
    public function emergencyNumber(): string
    {
        return '112';
    }

    public function emergencyTelHref(): string
    {
        return self::telHref($this->emergencyNumber());
    }

    /**
     * Beispiel für das Format einer Handynummer im Profil.
     */
    public function phoneExample(): string
    {
        return match ($this) {
            self::Austria => '+43 664 1234567',
            self::Germany => '+49 170 1234567',
        };
    }

    private static function telHref(string $number): string
    {
        return 'tel:'.preg_replace('/\D/', '', $number);
    }
}
