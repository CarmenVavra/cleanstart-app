<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Services\SobrietyProgress;
use Database\Seeders\BadgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SobrietyProgressTest extends TestCase
{
    use RefreshDatabase;

    private function progress(string $start, string $now): SobrietyProgress
    {
        $this->seed(BadgeSeeder::class);

        return new SobrietyProgress(
            Carbon::parse($start),
            Badge::query()->orderBy('days_required')->get(),
            Carbon::parse($now),
        );
    }

    public function test_it_splits_elapsed_time_into_days_hours_and_minutes(): void
    {
        $progress = $this->progress('2026-08-29 08:00', '2026-10-13 20:31:59');

        $this->assertSame(45, $progress->days());
        $this->assertSame(12, $progress->hours());
        $this->assertSame(31, $progress->minutes());
        $this->assertSame(5, $progress->unlockedCount());
    }

    public function test_it_computes_progress_towards_the_next_milestone(): void
    {
        // 45 Tage: zwischen 30 (vorheriger) und 90 (nächster) Meilenstein → 25 %
        $progress = $this->progress('2026-08-29 00:00', '2026-10-13 00:00');

        $this->assertSame('90 Tage', $progress->nextBadge()?->title);
        $this->assertSame(25, $progress->progressPercent());
        $this->assertSame('Noch 45 Tage bis „90 Tage“', $progress->nextText());
    }

    public function test_it_uses_singular_for_one_remaining_day(): void
    {
        $progress = $this->progress('2026-10-07 00:00', '2026-10-13 12:00');

        $this->assertSame('Noch 1 Tag bis „1 Woche“', $progress->nextText());
    }

    public function test_start_in_the_future_counts_as_zero(): void
    {
        $progress = $this->progress('2026-10-14 00:00', '2026-10-13 00:00');

        $this->assertSame(0, $progress->days());
        $this->assertSame(0, $progress->unlockedCount());
        $this->assertSame('Noch 24 Std. bis „24 Stunden“', $progress->nextText());
    }

    public function test_all_milestones_reached(): void
    {
        $progress = $this->progress('2025-01-01 00:00', '2026-10-13 00:00');

        $this->assertNull($progress->nextBadge());
        $this->assertSame(8, $progress->unlockedCount());
        $this->assertSame(100, $progress->progressPercent());
        $this->assertSame('', $progress->nextText());
    }
}
