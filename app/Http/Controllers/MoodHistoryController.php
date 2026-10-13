<?php

namespace App\Http\Controllers;

use App\Http\Requests\MoodHistoryRequest;
use App\Models\User;
use App\Services\MoodHistory;
use Illuminate\Contracts\View\View;

/**
 * Stimmungsverlauf mit Mustern aus den Daily Check-ins.
 */
class MoodHistoryController extends Controller
{
    public function index(MoodHistoryRequest $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $days = $request->days();
        $ranges = MoodHistory::RANGES;
        $history = new MoodHistory($user, $days);

        $checkinCount = $history->checkinCount();
        $average = $history->averageMood();
        $averageMood = $average === null ? null : MoodHistory::moodFor($average);
        $averageLabel = $average === null ? null : MoodHistory::formatAverage($average);
        $chart = $history->hasCheckins() ? $history->chart() : null;
        $timeline = $history->timeline();
        $hasRestarts = $history->hasRestarts();
        $showsPatterns = $history->showsPatterns();
        $insights = $history->insights();
        $trend = $history->trend();
        $triggerStats = $history->triggerStats();
        $weekdayStats = $history->weekdayStats();
        $minForPatterns = MoodHistory::MIN_FOR_PATTERNS;

        return view('mood-history.index', compact(
            'days',
            'ranges',
            'checkinCount',
            'averageMood',
            'averageLabel',
            'chart',
            'timeline',
            'hasRestarts',
            'showsPatterns',
            'insights',
            'trend',
            'triggerStats',
            'weekdayStats',
            'minForPatterns',
        ));
    }
}
