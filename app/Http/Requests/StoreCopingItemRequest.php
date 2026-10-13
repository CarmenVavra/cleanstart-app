<?php

namespace App\Http\Requests;

use App\Enums\CopingType;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCopingItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Fehler je Kategorie in einem eigenen Bag, damit sie nur beim passenden Formular erscheinen.
     */
    protected function prepareForValidation(): void
    {
        $type = CopingType::tryFrom($this->string('type')->toString());

        $this->errorBag = $type === null ? 'default' : $type->value;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(CopingType::class)],
            'content' => ['required', 'string', 'max:200'],
        ];
    }

    /**
     * Begrenzt die Anzahl der Einträge pro Kategorie.
     *
     * @return array<int, \Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $type = CopingType::tryFrom($this->string('type')->toString());

                /** @var User $user */
                $user = $this->user();

                if ($type !== null && $user->copingItems()->where('type', $type)->count() >= CopingType::MAX_ITEMS) {
                    $validator->errors()->add('content', 'Du kannst höchstens '.CopingType::MAX_ITEMS.' Einträge anlegen. Lösche zuerst einen bestehenden.');
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'type' => 'Kategorie',
            'content' => 'Eintrag',
        ];
    }

    public function type(): CopingType
    {
        return CopingType::from($this->string('type')->toString());
    }
}
