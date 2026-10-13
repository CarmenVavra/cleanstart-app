<?php

namespace App\Services;

use App\Enums\Mood;
use App\Enums\Trigger;
use App\Models\DailyCheckin;
use App\Models\SobrietyPeriod;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 * Auswertung der Daily Check-ins eines Zeitraums: Verlauf, Durchschnitt, Trigger- und Wochentagsmuster.
 *
 * Notizen werden bewusst nicht geladen – der Verlauf arbeitet nur mit Stimmung und Triggern.
 */
class MoodHistory
{
    /** Wählbare Zeiträume in Tagen. */
    public const RANGES = [7, 30, 90];

    public const DEFAULT_RANGE = 30;

    /** Ab so vielen Check-ins werden Muster angezeigt. */
    public const MIN_FOR_PATTERNS = 5;

    /** Ab dieser Abweichung vom Durchschnitt gilt ein Wert als auffällig. */
    private const NOTABLE_DIFFERENCE = 0.3;

    /** Ab dieser Veränderung zwischen erster und zweiter Hälfte gilt ein Trend. */
    private const TREND_DIFFERENCE = 0.5;

    private const WEEKDAYS = [
        1 => ['Mo', 'Montag', 'Montage'],
        2 => ['Di', 'Dienstag', 'Dienstage'],
        3 => ['Mi', 'Mittwoch', 'Mittwoche'],
        4 => ['Do', 'Donnerstag', 'Donnerstage'],
        5 => ['Fr', 'Freitag', 'Freitage'],
        6 => ['Sa', 'Samstag', 'Samstage'],
        7 => ['So', 'Sonntag', 'Sonntage'],
    ];

    /** Maße des SVG-Diagramms (viewBox). */
    private const CHART = ['width' => 320, 'height' => 150, 'left' => 30, 'right' => 4, 'top' => 8, 'bottom' => 22];

    public readonly Carbon $start;

    public readonly Carbon $end;

    /** @var Collection<int, DailyCheckin> */
    private Collection $checkins;

    /** @var list<string> Daten (Y-m-d) mit Neustart nach einem Rückfall */
    private array $restartDates;

    public function __construct(User $user, public readonly int $days, ?Carbon $today = null)
    {
        $this->end = ($today ?? today())->copy()->startOfDay();
        $this->start = $this->end->copy()->subDays($days - 1);

        $this->checkins = $user->dailyCheckins()
            ->whereDate('checkin_date', '>=', $this->start)
            ->whereDate('checkin_date', '<=', $this->end)
            ->orderBy('checkin_date')
            ->get(['id', 'checkin_date', 'mood_rating', 'triggers']);

        $this->restartDates = $user->sobrietyPeriods()
            ->where('ended_at', '>=', $this->start)
            ->get(['id', 'ended_at'])
            ->map(fn (SobrietyPeriod $period) => $period->ended_at->toDateString())
            ->unique()
            ->values()
            ->all();
    }

    public function hasCheckins(): bool
    {
        return $this->checkins->isNotEmpty();
    }

    public function checkinCount(): int
    {
        return $this->checkins->count();
    }

    public function showsPatterns(): bool
    {
        return $this->checkinCount() >= self::MIN_FOR_PATTERNS;
    }

    public function averageMood(): ?float
    {
        return self::average($this->checkins);
    }

    /**
     * Nächstliegende Stufe der Skala für einen Durchschnittswert.
     */
    public static function moodFor(float $average): Mood
    {
        return Mood::from((int) max(1, min(5, round($average))));
    }

    public static function formatAverage(float $average): string
    {
        return number_format($average, 1, ',', '');
    }

    /**
     * Alle Tage des Zeitraums (auch ohne Check-in) für Diagramm und Tabelle.
     *
     * @return list<array{date: Carbon, mood: Mood|null, triggers: list<string>, restart: bool}>
     */
    public function timeline(): array
    {
        $byDate = $this->checkins->keyBy(fn (DailyCheckin $checkin) => $checkin->checkin_date->toDateString());
        $timeline = [];

        for ($date = $this->start->copy(); $date->lte($this->end); $date->addDay()) {
            $checkin = $byDate->get($date->toDateString());

            $timeline[] = [
                'date' => $date->copy(),
                'mood' => $checkin?->mood_rating,
                'triggers' => $checkin?->triggerLabels() ?? [],
                'restart' => in_array($date->toDateString(), $this->restartDates, true),
            ];
        }

        return $timeline;
    }

    public function hasRestarts(): bool
    {
        return $this->restartDates !== [];
    }

    /**
     * Geometrie des Balkendiagramms (Höhe = Stimmung 1–5).
     *
     * @return array{
     *     width: int, height: int,
     *     bars: list<array{x: float, y: float, width: float, height: float}>,
     *     gaps: list<array{x: float, y: int|float}>,
     *     markers: list<array{x: float, top: int|float, bottom: int|float}>,
     *     gridlines: list<array{y: float, x1: int, x2: int, labelX: int, emoji: string}>,
     *     labels: list<array{x: int|float, y: int, text: string, anchor: string}>
     * }
     */
    public function chart(): array
    {
        $c = self::CHART;
        $timeline = $this->timeline();
        $count = count($timeline);
        $plotWidth = $c['width'] - $c['left'] - $c['right'];
        $plotHeight = $c['height'] - $c['top'] - $c['bottom'];
        $baseline = $c['height'] - $c['bottom'];
        $slot = $plotWidth / $count;
        $barWidth = max(1.5, $slot * 0.68);

        $levelY = fn (int $mood): float => round($baseline - $mood / 5 * $plotHeight, 2);
        $center = fn (int $index): float => round($c['left'] + ($index + 0.5) * $slot, 2);

        $chart = [
            'width' => $c['width'],
            'height' => $c['height'],
            'bars' => [],
            'gaps' => [],
            'markers' => [],
            'gridlines' => [],
            'labels' => [],
        ];

        foreach (Mood::cases() as $mood) {
            $chart['gridlines'][] = [
                'y' => $levelY($mood->value),
                'x1' => $c['left'],
                'x2' => $c['width'] - $c['right'],
                'labelX' => 14,
                'emoji' => $mood->emoji(),
            ];
        }

        foreach ($timeline as $index => $day) {
            if ($day['mood'] !== null) {
                $top = $levelY($day['mood']->value);
                $chart['bars'][] = [
                    'x' => round($center($index) - $barWidth / 2, 2),
                    'y' => $top,
                    'width' => round($barWidth, 2),
                    'height' => round($baseline - $top, 2),
                ];
            } else {
                $chart['gaps'][] = ['x' => $center($index), 'y' => $baseline - 2];
            }

            if ($day['restart']) {
                $chart['markers'][] = ['x' => $center($index), 'top' => $c['top'], 'bottom' => $baseline];
            }
        }

        $labelY = $c['height'] - 6;
        $chart['labels'][] = ['x' => $c['left'], 'y' => $labelY, 'text' => $this->start->format('j.n.'), 'anchor' => 'start'];

        if ($count > 7) {
            $middle = intdiv($count, 2);
            $chart['labels'][] = ['x' => $center($middle), 'y' => $labelY, 'text' => $timeline[$middle]['date']->format('j.n.'), 'anchor' => 'middle'];
        }

        $chart['labels'][] = ['x' => $c['width'] - $c['right'], 'y' => $labelY, 'text' => 'Heute', 'anchor' => 'end'];

        return $chart;
    }

    /**
     * Notierte Trigger nach Häufigkeit, mit durchschnittlicher Stimmung an diesen Tagen.
     *
     * @return list<array{trigger: Trigger, count: int, average: float, averageLabel: string, mood: Mood, share: int}>
     */
    public function triggerStats(): array
    {
        $stats = [];

        foreach (Trigger::cases() as $trigger) {
            $days = $this->checkins->filter(fn (DailyCheckin $checkin) => in_array($trigger->value, $checkin->triggers, true));

            if ($days->isNotEmpty()) {
                $average = (float) self::average($days);
                $stats[] = [
                    'trigger' => $trigger,
                    'count' => $days->count(),
                    'average' => $average,
                    'averageLabel' => self::formatAverage($average),
                    'mood' => self::moodFor($average),
                    'share' => 0,
                ];
            }
        }

        usort($stats, fn (array $a, array $b) => $b['count'] <=> $a['count']);
        $max = $stats[0]['count'] ?? 1;

        foreach ($stats as $index => $stat) {
            $stats[$index]['share'] = (int) round($stat['count'] / $max * 100);
        }

        return $stats;
    }

    /**
     * Durchschnittliche Stimmung je Wochentag (Montag zuerst).
     *
     * @return list<array{short: string, name: string, plural: string, count: int, average: float|null, averageLabel: string|null, mood: Mood|null, share: int}>
     */
    public function weekdayStats(): array
    {
        $stats = [];

        foreach (self::WEEKDAYS as $iso => [$short, $name, $plural]) {
            $days = $this->checkins->filter(fn (DailyCheckin $checkin) => $checkin->checkin_date->dayOfWeekIso === $iso);

            $average = self::average($days);
            $stats[] = [
                'short' => $short,
                'name' => $name,
                'plural' => $plural,
                'count' => $days->count(),
                'average' => $average,
                'averageLabel' => $average === null ? null : self::formatAverage($average),
                'mood' => $average === null ? null : self::moodFor($average),
                'share' => $average === null ? 0 : (int) round($average / 5 * 100),
            ];
        }

        return $stats;
    }

    /**
     * Vergleich der ersten mit der zweiten Hälfte des Zeitraums.
     *
     * @return 'better'|'worse'|'stable'|null
     */
    public function trend(): ?string
    {
        $middle = $this->start->copy()->addDays(intdiv($this->days, 2));
        [$earlier, $later] = $this->checkins->partition(fn (DailyCheckin $checkin) => $checkin->checkin_date->lt($middle));

        if ($earlier->count() < 2 || $later->count() < 2) {
            return null;
        }

        $difference = (float) self::average($later) - (float) self::average($earlier);

        return match (true) {
            $difference >= self::TREND_DIFFERENCE => 'better',
            $difference <= -self::TREND_DIFFERENCE => 'worse',
            default => 'stable',
        };
    }

    /**
     * Behutsam formulierte Hinweise auf Muster.
     *
     * @return list<string>
     */
    public function insights(): array
    {
        $overall = $this->averageMood();

        if (! $this->showsPatterns() || $overall === null) {
            return [];
        }

        $insights = [];

        $trend = $this->trend();
        if ($trend !== null) {
            $insights[] = match ($trend) {
                'better' => 'Deine Stimmung ist zuletzt besser geworden – schön, dass du dranbleibst.',
                'worse' => 'Zuletzt war deine Stimmung gedrückter als davor. Sei gut zu dir – dein Notfallplan kann dich unterstützen.',
                'stable' => 'Deine Stimmung war in diesem Zeitraum recht stabil.',
            };
        }

        $triggers = $this->triggerStats();
        $heavyTrigger = collect($triggers)
            ->filter(fn (array $stat) => $stat['count'] >= 2 && $stat['average'] <= $overall - self::NOTABLE_DIFFERENCE)
            ->sortBy('average')
            ->first();

        if ($heavyTrigger !== null) {
            $insights[] = 'An Tagen mit „'.$heavyTrigger['trigger']->label().'“ ging es dir im Schnitt schlechter ('
                .self::formatAverage($heavyTrigger['average']).' statt '.self::formatAverage($overall).'). Vielleicht lohnt sich dafür ein eigener Plan.';
        } elseif ($triggers !== []) {
            $top = $triggers[0];
            $insights[] = 'Am häufigsten notiert: „'.$top['trigger']->label().'“ (an '.$top['count'].' '.($top['count'] === 1 ? 'Tag' : 'Tagen').').';
        }

        $heavyWeekday = collect($this->weekdayStats())
            ->filter(fn (array $stat) => $stat['count'] >= 2 && $stat['average'] !== null && $stat['average'] <= $overall - self::NOTABLE_DIFFERENCE)
            ->sortBy('average')
            ->first();

        if ($heavyWeekday !== null) {
            $insights[] = $heavyWeekday['plural'].' fallen dir oft schwerer (Ø '.self::formatAverage($heavyWeekday['average']).'). Plane dort vielleicht etwas Gutes für dich ein.';
        }

        return $insights;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, DailyCheckin>  $checkins
     */
    private static function average(\Illuminate\Support\Collection $checkins): ?float
    {
        if ($checkins->isEmpty()) {
            return null;
        }

        return (float) $checkins->avg(fn (DailyCheckin $checkin) => $checkin->mood_rating->value);
    }
}
