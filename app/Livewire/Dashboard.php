<?php

namespace App\Livewire;

use App\Actions\RecordOvercomeCraving;
use App\Actions\SaveDailyCheckin;
use App\Actions\SyncUnlockedBadges;
use App\Enums\CopingType;
use App\Enums\Mood;
use App\Enums\SpendingPeriod;
use App\Enums\Trigger;
use App\Livewire\Forms\CheckinForm;
use App\Models\Badge;
use App\Models\CravingEpisode;
use App\Models\User;
use App\Services\SobrietyProgress;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Renderless;
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

    /** Einmalige Rückmeldung nach einer Weiterleitung (z. B. nach einem Neustart). */
    public ?string $flash = null;

    public function mount(SyncUnlockedBadges $syncUnlockedBadges): void
    {
        $user = $this->user();

        $status = session('status');
        $this->flash = is_string($status) ? $status : null;

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

    /**
     * Speichert einen mit dem Craving-Timer überstandenen Suchtdruck.
     * Das Notfall-Sheet ist clientseitig (wire:ignore), daher kein Re-Render nötig.
     *
     * @return int Anzahl aller überstandenen Suchtdruck-Momente
     */
    #[Renderless]
    public function recordCraving(RecordOvercomeCraving $recordOvercomeCraving): int
    {
        return $recordOvercomeCraving->handle($this->user());
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
        $longestPreviousStreak = $user->longestPreviousStreakDays();

        $copingItems = $user->copingItems()->orderBy('id')->get();
        $reasons = $copingItems->where('type', CopingType::Reason)->values();
        $strategies = $copingItems->where('type', CopingType::Strategy)->values();

        $cravingsOvercome = $user->cravingEpisodes()->count();
        $savedLabel = $user->hasSpending() ? $user->country->formatEuro($user->savedEuros()) : null;
        $savingsSummary = $this->savingsSummary($user);
        $clientConfig = [
            ...$progress->clientConfig(),
            'cravingMinutes' => CravingEpisode::TIMER_MINUTES,
            'cravingsOvercome' => $cravingsOvercome,
            'cravingStorageKey' => 'cleanstart.craving.'.$user->id,
            'savingsPerDayCents' => $user->dailySpendingCents(),
            'locale' => $user->country->locale(),
        ];

        return view('livewire.dashboard', compact(
            'user',
            'clientConfig',
            'cravingsOvercome',
            'savedLabel',
            'savingsSummary',
            'badges',
            'progress',
            'moods',
            'triggers',
            'greeting',
            'todayLabel',
            'startLabel',
            'selectedMood',
            'savedSummary',
            'longestPreviousStreak',
            'reasons',
            'strategies',
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
     * Erläuterung zum Ersparnis-Rechner, z. B. „Bei € 70,00 pro Woche · rund € 304 im Monat“.
     */
    private function savingsSummary(User $user): ?string
    {
        $cents = $user->spendingCents();
        $perDay = $user->dailySpendingCents();

        if ($cents === null || $perDay === null || $user->spending_period === null) {
            return null;
        }

        $country = $user->country;
        $perMonth = $perDay * SpendingPeriod::DAYS_PER_MONTH / 100;

        return 'Bei '.$country->formatEuro($cents / 100).' '.$user->spending_period->label()
            .' · rund '.$country->formatEuro($perMonth, 0).' im Monat';
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
