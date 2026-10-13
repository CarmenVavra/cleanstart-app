<x-layouts.guest title="Registrieren">
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl/[1.2] font-bold">Dein neuer Anfang</h1>
        <p class="text-[17px]/[1.45] text-ink-muted">
            CleanStart begleitet dich – anonym. Du brauchst keinen echten Namen.
        </p>
    </div>

    <x-card aria-labelledby="register-h">
        <h2 id="register-h" class="sr-only">Registrierungsformular</h2>

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-5" novalidate>
            @csrf

            <x-form.input
                name="username"
                label="Benutzername (Pseudonym)"
                hint="Bitte wähle einen anonymen Namen – nicht deinen echten Vor- oder Nachnamen."
                autocomplete="username"
                maxlength="40"
                required
                autofocus
            />

            <x-form.input
                name="email"
                label="E-Mail-Adresse"
                type="email"
                hint="Wird nur für die Anmeldung gespeichert. Eine anonyme Adresse ist völlig in Ordnung."
                autocomplete="email"
                required
            />

            <x-form.input name="password" label="Passwort" type="password" hint="Mindestens 8 Zeichen." autocomplete="new-password" required />
            <x-form.input name="password_confirmation" label="Passwort wiederholen" type="password" autocomplete="new-password" required />

            <x-form.input
                name="sobriety_start_at"
                label="Clean seit"
                type="datetime-local"
                hint="Wann hat dein cleaner Weg begonnen? Du kannst das später im Profil ändern."
                :value="$defaultStart"
                :max="$defaultStart"
                required
            />

            <x-form.select
                name="country"
                label="Land"
                hint="Damit wir dir im Notfall die passenden Hilfsnummern zeigen."
                :options="$countries"
                :value="$defaultCountry"
                required
            />

            <div class="flex flex-col gap-1.5">
                <label class="flex items-start gap-3 text-[15px]/[1.45]">
                    <input
                        type="checkbox"
                        name="privacy_consent"
                        value="1"
                        required
                        class="mt-0.5 size-5 flex-none accent-sage-900"
                        @checked(old('privacy_consent'))
                        @error('privacy_consent') aria-invalid="true" aria-describedby="privacy_consent-error" @enderror
                    >
                    <span>
                        Ich habe die <a href="{{ route('legal.privacy') }}" target="_blank" rel="noopener" class="font-bold">Datenschutzerklärung</a>
                        gelesen und willige ausdrücklich ein, dass meine Angaben zu Stimmung, Triggern und Abstinenz
                        (Gesundheitsdaten) zur Nutzung von CleanStart verarbeitet werden. Ich kann die Einwilligung
                        jederzeit widerrufen, indem ich mein Konto lösche.
                    </span>
                </label>
                @error('privacy_consent')
                    <p id="privacy_consent-error" class="text-sm font-bold text-sos">{{ $message }}</p>
                @enderror
            </div>

            <x-button>Konto erstellen</x-button>
        </form>
    </x-card>

    <p class="text-center text-base text-ink-muted">
        Schon registriert?
        <a href="{{ route('login') }}" class="font-bold">Zur Anmeldung</a>
    </p>
</x-layouts.guest>
