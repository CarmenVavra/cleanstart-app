<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
        return [
            'username' => [
                'required', 'string', 'min:3', 'max:40', 'alpha_dash:ascii',
                Rule::unique('users', 'username')->ignore($this->user()?->id),
            ],
            'sobriety_start_at' => ['required', 'date', 'before_or_equal:now'],
            'trusted_contact_name' => ['nullable', 'required_with:trusted_contact_phone', 'string', 'max:60'],
            'trusted_contact_phone' => [
                'nullable', 'required_with:trusted_contact_name', 'string', 'max:30',
                'regex:/^\+?[0-9][0-9 \/()-]{2,29}$/',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'username' => 'Benutzername',
            'sobriety_start_at' => 'Clean seit',
            'trusted_contact_name' => 'Name der Vertrauensperson',
            'trusted_contact_phone' => 'Telefonnummer',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'trusted_contact_phone.regex' => 'Bitte gib eine gültige Telefonnummer ein (Ziffern, optional mit + am Anfang).',
        ];
    }
}
