<x-layouts.guest title="Passwort vergessen">
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl/[1.2] font-bold">Passwort vergessen?</h1>
        <p class="text-[17px]/[1.45] text-ink-muted">
            Kein Problem. Gib die E-Mail-Adresse deines Kontos ein – wir schicken dir einen Link für ein neues Passwort.
        </p>
    </div>

    <x-card aria-labelledby="forgot-h">
        <h2 id="forgot-h" class="sr-only">Link zum Zurücksetzen anfordern</h2>

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-5" novalidate>
            @csrf

            <x-form.input name="email" label="E-Mail-Adresse" type="email" autocomplete="email" required autofocus />

            <x-button>Link senden</x-button>
        </form>
    </x-card>

    <p class="text-center text-base text-ink-muted">
        <a href="{{ route('login') }}" class="font-bold">Zurück zur Anmeldung</a>
    </p>
</x-layouts.guest>
