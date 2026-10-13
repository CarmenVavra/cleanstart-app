<x-layouts.guest title="Anmelden">
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl/[1.2] font-bold">Willkommen zurück</h1>
        <p class="text-[17px]/[1.45] text-ink-muted">Schön, dass du da bist. Melde dich mit deinem Pseudonym an.</p>
    </div>

    <x-card aria-labelledby="login-h">
        <h2 id="login-h" class="sr-only">Anmeldeformular</h2>

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5" novalidate>
            @csrf

            <x-form.input name="username" label="Benutzername" autocomplete="username" required autofocus />
            <div class="flex flex-col gap-1">
                <x-form.input name="password" label="Passwort" type="password" autocomplete="current-password" required />
                <a href="{{ route('password.request') }}" class="inline-flex min-h-11 items-center self-start text-[15px] font-bold">
                    Passwort vergessen?
                </a>
            </div>

            <label class="flex min-h-11 items-center gap-3 text-base">
                <input type="checkbox" name="remember" value="1" class="size-5 accent-sage-900" @checked(old('remember'))>
                Angemeldet bleiben
            </label>

            <x-button>Anmelden</x-button>
        </form>
    </x-card>

    <p class="text-center text-base text-ink-muted">
        Noch kein Konto?
        <a href="{{ route('register') }}" class="font-bold">Jetzt anonym registrieren</a>
    </p>
</x-layouts.guest>
