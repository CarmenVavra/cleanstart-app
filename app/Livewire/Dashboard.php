<?php

namespace App\Livewire;

use App\Actions\SaveDailyCheckin;
use App\Actions\SyncUnlockedBadges;
use App\Enums\Mood;
use App\Enums\Trigger;
use App\Livewire\Forms\CheckinForm;
use App\Models\Badge;
use App\Models\User;
use App\Services\SobrietyProgress;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Haupt-Dashboard: Notfall-Button, Clean-Counter, Meilensteine und Daily Check-in.
 *
 * Counter-Tick, Notfall-Sheet und Atemübung laufen rein clientseitig (Alpine.js),
 * der Check-in wird über Livewire gespeichert.
 */
#[Title('Dashboard')]
class Dashboard extends Component
{
    public CheckinForm $form;

    public bool $saved = false;

    public function mount(SyncUnlockedBadges $syncUnlockedBadges): void
    {
        $user = $this->user();

        $syncUnlockedBadges->handle($user);

        $checkin = $user->todaysCheckin();

        if ($checkin !== null) {
            $this->form->fillFromCheckin($checkin);
            $this->saved = true;
        }
    }

    public function save(SaveDailyCheckin $saveDailyCheckin): void
    {
        $this->form->validate();

        $mood = $this->form->selectedMood();

        if ($mood === null) {
            return;
        }

        $saveDailyCheckin->handle($this->user(), $mood, $this->form->selectedTriggers(), $this->form->notes);

        $this->saved = true;
    }

    public function edit(): void
    {
        $this->saved = false;
    }

    public function render(): View
    {
        $user = $this->user();
        $badges = Badge::query()->orderBy('days_required')->get();
        $progress = new SobrietyProgress($user->sobriety_start_at, $badges);

        $moods = Mood::cases();
        $triggers = Trigger::cases();
        $greeting = $this->greeting();
        $todayLabel = now()->translatedFormat('l, j. F');
        $startLabel = 'seit '.$user->sobriety_start_at->translatedFormat('j. F Y');
        $selectedMood = $this->form->selectedMood();
        $savedSummary = $this->savedSummary();

        return view('livewire.dashboard', compact(
            'user',
            'badges',
            'progress',
            'moods',
            'triggers',
            'greeting',
            'todayLabel',
            'startLabel',
            'selectedMood',
            'savedSummary',
        ));
    }

    /**
     * Begrüßung nach Tageszeit (< 11 Uhr Morgen, < 18 Uhr Tag, sonst Abend).
     */
    private function greeting(): string
    {
        $hour = now()->hour;

        return match (true) {
            $hour < 11 => 'Guten Morgen',
            $hour < 18 => 'Guten Tag',
            default => 'Guten Abend',
        };
    }

    /**
     * Rückmeldetext im Gespeichert-Zustand.
     */
    private function savedSummary(): string
    {
        $labels = Trigger::labelsFor($this->form->triggers);

        if ($labels === []) {
            return 'Keine Trigger notiert. Schön, dass du dir Zeit genommen hast.';
        }

        return 'Notiert: '.implode(', ', $labels).'. Gut, dass du hinschaust.';
    }

    private function user(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
