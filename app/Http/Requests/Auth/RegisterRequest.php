<?php

namespace App\Http\Requests\Auth;

use App\Enums\Country;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'min:3', 'max:40', 'alpha_dash:ascii', 'unique:users,username'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'sobriety_start_at' => ['required', 'date', 'before_or_equal:now'],
            'country' => ['required', Rule::enum(Country::class)],
            'privacy_consent' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'privacy_consent.accepted' => 'Bitte bestätige die Datenschutzerklärung und deine Einwilligung.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'username' => 'Benutzername',
            'email' => 'E-Mail-Adresse',
            'password' => 'Passwort',
            'sobriety_start_at' => 'Clean seit',
            'country' => 'Land',
        ];
    }
}
