<x-layouts.page title="Profil" heading="Dein Profil" :back="route('dashboard')">
    <form method="POST" action="{{ route('profile.update') }}" class="flex flex-col gap-5" novalidate>
        @csrf
        @method('PUT')

        <x-card aria-labelledby="account-h">
            <h2 id="account-h" class="text-[19px] font-bold">Konto</h2>

            <x-form.input
                name="username"
                label="Benutzername (Pseudonym)"
                :value="$user->username"
                autocomplete="username"
                maxlength="40"
                required
            />

            <x-form.input
                name="sobriety_start_at"
                label="Clean seit"
                type="datetime-local"
                hint="Nur zum Korrigieren eines falsch eingegebenen Datums. Nach einem Rückfall nutze bitte „Neu starten“ – so bleibt deine bisherige Serie erhalten."
                :value="$user->sobriety_start_at->format('Y-m-d\TH:i')"
                :max="now()->format('Y-m-d\TH:i')"
                required
            />

            <x-form.select
                name="country"
                label="Land"
                hint="Bestimmt die Hilfsnummern im Notfall-Fenster."
                :options="$countries"
                :value="$user->country->value"
                required
            />
        </x-card>

        <x-card aria-labelledby="contact-h">
            <div class="flex flex-col gap-1">
                <h2 id="contact-h" class="text-[19px] font-bold">Vertrauensperson</h2>
                <p class="text-[15px]/[1.45] text-ink-muted">
                    Diese Person kannst du im Notfall mit einem Tipp anrufen. Name und Nummer werden verschlüsselt gespeichert.
                </p>
            </div>

            <x-form.input
                name="trusted_contact_name"
                label="Name"
                :value="$user->trusted_contact_name"
                autocomplete="off"
                maxlength="60"
            />

            <x-form.input
                name="trusted_contact_phone"
                label="Telefonnummer"
                type="tel"
                hint="z. B. {{ $user->country->phoneExample() }}"
                :value="$user->trusted_contact_phone"
                autocomplete="off"
                inputmode="tel"
                maxlength="30"
            />
        </x-card>

        <x-card aria-labelledby="savings-h" id="ersparnis">
            <div class="flex flex-col gap-1">
                <h2 id="savings-h" class="text-[19px] font-bold">Ersparnis-Rechner</h2>
                <p class="text-[15px]/[1.45] text-ink-muted">
                    Wie viel hast du früher ungefähr für deine Sucht ausgegeben? Auf dem Dashboard siehst du dann,
                    wie viel du seit deinem Start gespart hast. Der Betrag wird verschlüsselt gespeichert –
                    leer lassen blendet den Rechner aus.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <div class="min-w-36 flex-1">
                    <x-form.input
                        name="spending_amount"
                        label="Betrag in Euro"
                        hint="z. B. 12,50"
                        :value="$spendingAmount"
                        inputmode="decimal"
                        autocomplete="off"
                        maxlength="12"
                    />
                </div>
                <div class="min-w-36 flex-1">
                    <x-form.select
                        name="spending_period"
                        label="Zeitraum"
                        hint="Wie oft?"
                        :options="$spendingPeriods"
                        :value="$spendingPeriod"
                    />
                </div>
            </div>
        </x-card>

        <x-button>Profil speichern</x-button>
    </form>

    <x-card aria-labelledby="plan-h">
        <div class="flex flex-col gap-1">
            <h2 id="plan-h" class="text-[19px] font-bold">Mein Notfallplan</h2>
            <p class="text-[15px]/[1.45] text-ink-muted">
                Dein Warum und was dir hilft – erscheint direkt im Notfall-Fenster.
            </p>
        </div>
        <a href="{{ route('coping.index') }}" class="flex min-h-12 items-center justify-center rounded-2xl bg-sage-100 px-5 text-base font-bold text-sage-900 no-underline hover:text-sage-900">
            Notfallplan bearbeiten
        </a>
    </x-card>

    <x-card aria-labelledby="restart-h">
        <div class="flex flex-col gap-1">
            <h2 id="restart-h" class="text-[19px] font-bold">Neu starten</h2>
            <p class="text-[15px]/[1.45] text-ink-muted">
                Ein Rückfall ist kein Scheitern, sondern Teil vieler Wege. Starte neu – deine bisherigen Serien bleiben gespeichert.
            </p>
        </div>

        @if ($periods->isNotEmpty())
            <div class="flex flex-col gap-2">
                <h3 class="text-base font-bold">Deine bisherigen Serien</h3>
                <ul class="flex flex-col gap-1.5">
                    @foreach ($periods as $period)
                        <li class="flex items-baseline justify-between gap-3 rounded-xl bg-surface px-3.5 py-2.5 text-[15px]">
                            <span>{{ $period->started_at->translatedFormat('j. M Y') }} – {{ $period->ended_at->translatedFormat('j. M Y') }}</span>
                            <strong class="whitespace-nowrap tabular-nums">{{ $period->days() }} {{ $period->days() === 1 ? 'Tag' : 'Tage' }}</strong>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <a href="{{ route('relapse.create') }}" class="flex min-h-12 items-center justify-center rounded-2xl border-2 border-sage-600 px-5 text-base font-bold text-sage-900 no-underline hover:bg-sage-100 hover:text-sage-900">
            Neu starten
        </a>
    </x-card>

    <x-card aria-labelledby="data-h">
        <div class="flex flex-col gap-1">
            <h2 id="data-h" class="text-[19px] font-bold">Deine Daten</h2>
            <p class="text-[15px]/[1.45] text-ink-muted">
                Lade alle deine Daten als Datei herunter (JSON) – z. B. zum Aufbewahren oder für deine Therapeutin bzw. deinen Therapeuten.
            </p>
        </div>
        <a href="{{ route('profile.export') }}" class="flex min-h-12 items-center justify-center rounded-2xl border-2 border-line bg-white px-5 text-base font-bold text-ink no-underline hover:bg-surface hover:text-ink">
            Meine Daten herunterladen
        </a>
    </x-card>

    <x-card aria-labelledby="delete-h" class="border-2 border-[#F3C9C5]">
        <div class="flex flex-col gap-1">
            <h2 id="delete-h" class="text-[19px] font-bold">Konto löschen</h2>
            <p class="text-[15px]/[1.45] text-ink-muted">
                Löscht dein Konto mit allen Check-ins, Serien, Notizen und deinem Notfallplan – sofort und endgültig.
                Das kann nicht rückgängig gemacht werden.
            </p>
        </div>

        <form method="POST" action="{{ route('profile.destroy') }}" class="flex flex-col gap-4" novalidate>
            @csrf
            @method('DELETE')

            <x-form.input
                name="password"
                id="delete-password"
                label="Zur Bestätigung: dein Passwort"
                type="password"
                bag="accountDeletion"
                autocomplete="current-password"
                required
            />

            <button type="submit" class="min-h-14 w-full rounded-2xl bg-sos px-5 text-lg font-bold text-white hover:bg-sos-hover">
                Konto endgültig löschen
            </button>
        </form>
    </x-card>

    <x-card aria-labelledby="session-h">
        <h2 id="session-h" class="text-[19px] font-bold">Sitzung</h2>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-button variant="secondary">Abmelden</x-button>
        </form>
    </x-card>
</x-layouts.page>
