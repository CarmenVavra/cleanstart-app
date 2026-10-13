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

            <x-button>Konto erstellen</x-button>
        </form>
    </x-card>

    <p class="text-center text-base text-ink-muted">
        Schon registriert?
        <a href="{{ route('login') }}" class="font-bold">Zur Anmeldung</a>
    </p>
</x-layouts.guest>
