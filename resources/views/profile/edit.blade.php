<x-layouts.app title="Profil">
    <main id="main" class="mx-auto flex min-h-screen max-w-[440px] flex-col gap-5 px-5 pt-5 pb-10">
        <header class="flex flex-col gap-3 pt-1">
            <a href="{{ route('dashboard') }}" class="inline-flex min-h-11 items-center gap-2 self-start text-base font-bold">
                <span aria-hidden="true">←</span> Zurück zum Dashboard
            </a>
            <div class="flex flex-col gap-0.5">
                <x-wordmark />
                <h1 class="text-2xl/[1.2] font-bold">Dein Profil</h1>
            </div>
        </header>

        @if (session('status'))
            <p role="status" class="rounded-[18px] bg-sage-100 p-4 text-base font-bold text-ink-soft">
                {{ session('status') }}
            </p>
        @endif

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
                    hint="Nach einem Rückfall kannst du hier neu starten. Das ist kein Scheitern, sondern Teil des Weges."
                    :value="$user->sobriety_start_at->format('Y-m-d\TH:i')"
                    :max="now()->format('Y-m-d\TH:i')"
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
                    hint="z. B. +49 170 1234567"
                    :value="$user->trusted_contact_phone"
                    autocomplete="off"
                    inputmode="tel"
                    maxlength="30"
                />
            </x-card>

            <x-button>Profil speichern</x-button>
        </form>

        <x-card aria-labelledby="session-h">
            <h2 id="session-h" class="text-[19px] font-bold">Sitzung</h2>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-button variant="secondary">Abmelden</x-button>
            </form>
        </x-card>
    </main>
</x-layouts.app>
