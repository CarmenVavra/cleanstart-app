<?php

namespace App\Http\Requests;

use App\Enums\Trigger;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class RelapseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->user();

        return [
            'relapsed_at' => [
                'required', 'date', 'before_or_equal:now',
                'after_or_equal:'.$user->sobriety_start_at->format('Y-m-d H:i'),
            ],
            'reflection' => ['nullable', 'string', 'max:2000'],
            'triggers' => ['nullable', 'array', 'max:'.count(Trigger::cases())],
            'triggers.*' => ['string', 'distinct', Rule::enum(Trigger::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'relapsed_at' => 'Neuer Startzeitpunkt',
            'reflection' => 'Reflexion',
            'triggers' => 'Trigger',
            'triggers.*' => 'Trigger',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'relapsed_at.after_or_equal' => 'Der neue Start darf nicht vor dem Beginn deiner aktuellen Serie liegen.',
        ];
    }

    public function relapsedAt(): Carbon
    {
        return Carbon::parse($this->string('relapsed_at')->toString());
    }

    /**
     * @return list<Trigger>
     */
    public function triggers(): array
    {
        /** @var array<int, string> $values */
        $values = $this->validated('triggers') ?? [];

        return array_values(array_map(fn (string $value) => Trigger::from($value), $values));
    }
}
