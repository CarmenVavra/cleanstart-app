<x-layouts.guest title="Neues Passwort">
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl/[1.2] font-bold">Neues Passwort festlegen</h1>
        <p class="text-[17px]/[1.45] text-ink-muted">Wähle ein neues Passwort für dein Konto.</p>
    </div>

    <x-card aria-labelledby="reset-h">
        <h2 id="reset-h" class="sr-only">Neues Passwort</h2>

        <form method="POST" action="{{ route('password.store') }}" class="flex flex-col gap-5" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <x-form.input name="email" label="E-Mail-Adresse" type="email" :value="$email" autocomplete="email" required />
            <x-form.input name="password" label="Neues Passwort" type="password" hint="Mindestens 8 Zeichen." autocomplete="new-password" required autofocus />
            <x-form.input name="password_confirmation" label="Neues Passwort wiederholen" type="password" autocomplete="new-password" required />

            <x-button>Passwort speichern</x-button>
        </form>
    </x-card>
</x-layouts.guest>
