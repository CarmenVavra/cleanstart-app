<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

/**
 * Legt die acht Meilenstein-Abzeichen an (idempotent – kann beliebig oft ausgeführt werden).
 */
class BadgeSeeder extends Seeder
{
    /**
     * Meilensteine laut Design-Handoff: [Tage, Kurzlabel, Titel, Farbton].
     *
     * @var list<array{int, string, string, int}>
     */
    public const MILESTONES = [
        [1, '24h', '24 Stunden', 165],
        [3, '3', '3 Tage', 190],
        [7, '7', '1 Woche', 215],
        [14, '14', '2 Wochen', 240],
        [30, '30', '30 Tage', 275],
        [90, '90', '90 Tage', 310],
        [180, '½J', '6 Monate', 30],
        [365, '1J', '1 Jahr', 70],
    ];

    public function run(): void
    {
        foreach (self::MILESTONES as [$days, $short, $title, $hue]) {
            Badge::updateOrCreate(
                ['icon_slug' => "badge-{$days}d"],
                ['title' => $title, 'short_label' => $short, 'hue' => $hue, 'days_required' => $days],
            );
        }
    }
}
