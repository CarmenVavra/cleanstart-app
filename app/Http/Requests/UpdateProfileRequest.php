<?php

namespace App\Http\Requests;

use App\Enums\Country;
use App\Enums\SpendingPeriod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Erlaubt Beträge in deutscher Schreibweise („1.234,50 €“).
     */
    protected function prepareForValidation(): void
    {
        $amount = $this->input('spending_amount');

        if (! is_string($amount)) {
            return;
        }

        $amount = str_replace(['€', ' ', "\u{00A0}"], '', trim($amount));

        if (str_contains($amount, ',')) {
            $amount = str_replace(['.', ','], ['', '.'], $amount);
        }

        $this->merge(['spending_amount' => $amount === '' ? null : $amount]);
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
            'country' => ['required', Rule::enum(Country::class)],
            'trusted_contact_name' => ['nullable', 'required_with:trusted_contact_phone', 'string', 'max:60'],
            'trusted_contact_phone' => [
                'nullable', 'required_with:trusted_contact_name', 'string', 'max:30',
                'regex:/^\+?[0-9][0-9 \/()-]{2,29}$/',
            ],
            'spending_amount' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:100000'],
            'spending_period' => ['nullable', 'required_with:spending_amount', Rule::enum(SpendingPeriod::class)],
        ];
    }

    /**
     * Validierte Profildaten; der Betrag wird als Cent-Wert gespeichert.
     *
     * @return array<string, mixed>
     */
    public function profileData(): array
    {
        $data = $this->safe()->except(['spending_amount', 'spending_period']);

        $amount = $this->validated('spending_amount');
        $cents = $amount === null ? 0 : (int) round((float) $amount * 100);

        // Ohne Betrag (oder 0 €) ist der Ersparnis-Rechner ausgeschaltet.
        $data['spending_cents'] = $cents > 0 ? (string) $cents : null;
        $data['spending_period'] = $cents > 0 ? $this->validated('spending_period') : null;

        return $data;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'username' => 'Benutzername',
            'sobriety_start_at' => 'Clean seit',
            'country' => 'Land',
            'trusted_contact_name' => 'Name der Vertrauensperson',
            'trusted_contact_phone' => 'Telefonnummer',
            'spending_amount' => 'Betrag',
            'spending_period' => 'Zeitraum',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'trusted_contact_phone.regex' => 'Bitte gib eine gültige Telefonnummer ein (Ziffern, optional mit + am Anfang).',
            'spending_amount.numeric' => 'Bitte gib einen Betrag in Euro ein, z. B. 12,50.',
            'spending_amount.decimal' => 'Bitte gib den Betrag mit höchstens zwei Nachkommastellen an.',
        ];
    }
}
