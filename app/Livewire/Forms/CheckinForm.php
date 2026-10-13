<?php

namespace App\Livewire\Forms;

use App\Enums\Mood;
use App\Enums\Trigger;
use App\Models\DailyCheckin;
use Illuminate\Validation\Rule;
use Livewire\Form;

/**
 * Formularzustand und Validierung des Daily Check-ins.
 */
class CheckinForm extends Form
{
    public ?int $mood = null;

    /** @var list<string> */
    public array $triggers = [];

    public string $notes = '';

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'mood' => ['required', 'integer', Rule::enum(Mood::class)],
            'triggers' => ['array', 'max:'.count(Trigger::cases())],
            'triggers.*' => ['string', 'distinct', Rule::enum(Trigger::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'mood.required' => 'Bitte wähle zuerst deine Stimmung.',
            'mood.*' => 'Bitte wähle eine gültige Stimmung.',
            'triggers.*' => 'Bitte wähle nur Einträge aus der Liste.',
            'triggers.*.*' => 'Bitte wähle nur Einträge aus der Liste.',
            'notes.max' => 'Deine Notiz darf höchstens 2000 Zeichen lang sein.',
        ];
    }

    /**
     * Übernimmt die Werte eines bereits gespeicherten Check-ins.
     */
    public function fillFromCheckin(DailyCheckin $checkin): void
    {
        $this->mood = $checkin->mood_rating->value;
        $this->triggers = array_values($checkin->triggers);
        $this->notes = $checkin->notes_encrypted ?? '';
    }

    public function selectedMood(): ?Mood
    {
        return $this->mood === null ? null : Mood::tryFrom($this->mood);
    }

    /**
     * Ausgewählte Trigger als Enum-Instanzen.
     *
     * @return list<Trigger>
     */
    public function selectedTriggers(): array
    {
        return array_values(array_filter(array_map(
            fn (string $value) => Trigger::tryFrom($value),
            $this->triggers,
        )));
    }
}
