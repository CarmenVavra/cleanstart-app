<?php

namespace App\Http\Requests;

use App\Services\MoodHistory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MoodHistoryRequest extends FormRequest
{
    /**
     * Ungültige Zeiträume führen zur Standardansicht (statt zurück auf dieselbe URL).
     *
     * @var string
     */
    protected $redirectRoute = 'mood.index';

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tage' => ['nullable', 'integer', Rule::in(MoodHistory::RANGES)],
        ];
    }

    /**
     * Gewählter Zeitraum in Tagen.
     */
    public function days(): int
    {
        return (int) ($this->validated('tage') ?? MoodHistory::DEFAULT_RANGE);
    }
}
