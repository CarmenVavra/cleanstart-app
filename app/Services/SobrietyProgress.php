<?php

namespace App\Services;

use App\Models\Badge;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Berechnet Clean-Zeit, Meilenstein-Status und Fortschritt zum nächsten Meilenstein.
 *
 * Dient dem serverseitigen Initial-Rendering; der Browser tickt dieselbe Logik
 * anschließend sekündlich weiter (resources/js/dashboard.js).
 */
class SobrietyProgress
{
    private readonly int $elapsedSeconds;

    /**
     * @param  Collection<int, Badge>  $badges  nach days_required aufsteigend sortiert
     */
    public function __construct(
        public readonly Carbon $start,
        public readonly Collection $badges,
        ?Carbon $now = null,
    ) {
        $this->elapsedSeconds = (int) max(0, $start->diffInSeconds($now ?? now(), false));
    }

    public function days(): int
    {
        return intdiv($this->elapsedSeconds, 86400);
    }

    public function hours(): int
    {
        return intdiv($this->elapsedSeconds, 3600) % 24;
    }

    public function minutes(): int
    {
        return intdiv($this->elapsedSeconds, 60) % 60;
    }

    /**
     * Vergangene Zeit in Tagen mit Nachkommastellen.
     */
    public function fractionalDays(): float
    {
        return $this->elapsedSeconds / 86400;
    }

    public function isUnlocked(Badge $badge): bool
    {
        return $this->fractionalDays() >= $badge->days_required;
    }

    public function unlockedCount(): int
    {
        return $this->badges->filter(fn (Badge $badge) => $this->isUnlocked($badge))->count();
    }

    /**
     * Nächster noch nicht erreichter Meilenstein (null, wenn alle erreicht sind).
     */
    public function nextBadge(): ?Badge
    {
        return $this->badges->first(fn (Badge $badge) => ! $this->isUnlocked($badge));
    }

    /**
     * Fortschritt seit dem vorherigen Meilenstein in Prozent (0–100).
     */
    public function progressPercent(): int
    {
        $next = $this->nextBadge();

        if ($next === null) {
            return 100;
        }

        $previous = $this->badges
            ->filter(fn (Badge $badge) => $badge->days_required < $next->days_required)
            ->max('days_required') ?? 0;

        $span = $next->days_required - $previous;

        return (int) round((($this->fractionalDays() - $previous) / $span) * 100);
    }

    /**
     * Hinweistext unter dem Fortschrittsbalken, z. B. „Noch 6 Tage bis „90 Tage““.
     */
    public function nextText(): string
    {
        $next = $this->nextBadge();

        if ($next === null) {
            return '';
        }

        if ($next->days_required === 1) {
            $hoursLeft = 24 - (int) floor($this->fractionalDays() * 24);

            return "Noch {$hoursLeft} Std. bis „{$next->title}“";
        }

        $daysLeft = (int) ceil($next->days_required - $this->fractionalDays());
        $word = $daysLeft === 1 ? 'Tag' : 'Tage';

        return "Noch {$daysLeft} {$word} bis „{$next->title}“";
    }

    /**
     * Daten, die das Alpine-Dashboard für den Live-Counter benötigt.
     *
     * @return array{start: string, milestones: list<array{days: int, title: string}>}
     */
    public function clientConfig(): array
    {
        return [
            'start' => $this->start->toIso8601String(),
            'milestones' => $this->badges
                ->map(fn (Badge $badge) => ['days' => $badge->days_required, 'title' => $badge->title])
                ->values()
                ->all(),
        ];
    }
}
