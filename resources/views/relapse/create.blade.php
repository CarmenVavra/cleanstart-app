<x-layouts.page title="Neu starten" heading="Neu starten" :back="route('profile.edit')" back-label="Zurück zum Profil">
    <section aria-labelledby="restart-intro-h" class="flex flex-col gap-[18px] rounded-3xl bg-sage-100 px-5 py-[22px]">
        <h2 id="restart-intro-h" class="text-[19px] font-bold">Du bist hier – und das zählt.</h2>
        <p class="text-[17px]/[1.45] text-ink-soft">
            Ein Rückfall löscht nicht, was du geschafft hast. Deine Serie von
            <strong>{{ $currentDays }} {{ $currentDays === 1 ? 'Tag' : 'Tagen' }}</strong> bleibt gespeichert.
            Viele Menschen brauchen mehrere Anläufe – jeder davon macht dich stärker.
        </p>
        <p class="text-[15px]/[1.45] text-ink-soft">
            Geht es dir gerade schlecht? Die Telefonseelsorge ist rund um die Uhr da:
            <a href="tel:08001110111" class="font-bold">0800 111 0 111</a>
        </p>
    </section>

    <form method="POST" action="{{ route('relapse.store') }}" class="flex flex-col gap-5" novalidate>
        @csrf

        <x-card aria-labelledby="restart-form-h">
            <h2 id="restart-form-h" class="sr-only">Neustart erfassen</h2>

            <x-form.input
                name="relapsed_at"
                label="Ab wann zählst du neu?"
                type="datetime-local"
                hint="Meist ist das jetzt. Du kannst auch einen früheren Zeitpunkt wählen."
                :value="$now"
                :min="$currentStart"
                :max="$now"
                required
            />

            <div class="flex flex-col gap-1.5">
                <label for="reflection" class="text-base font-bold">
                    Was ist passiert? <span class="font-normal text-ink-muted">(optional)</span>
                </label>
                <p id="reflection-hint" class="text-sm text-ink-muted">
                    Nur für dich – wird verschlüsselt gespeichert. Hinschauen hilft, Muster zu erkennen.
                </p>
                <textarea
                    id="reflection"
                    name="reflection"
                    rows="4"
                    maxlength="2000"
                    aria-describedby="reflection-hint @error('reflection') reflection-error @enderror"
                    @error('reflection') aria-invalid="true" @enderror
                    class="w-full rounded-2xl border-2 border-line-soft bg-surface p-3.5 text-base text-ink focus:border-sage-600"
                >{{ old('reflection') }}</textarea>
                @error('reflection')
                    <p id="reflection-error" class="text-sm font-bold text-sos">{{ $message }}</p>
                @enderror
            </div>

            <fieldset class="flex flex-col gap-3">
                <legend class="mb-3 text-base font-bold">
                    Was hat eine Rolle gespielt? <span class="font-normal text-ink-muted">(optional)</span>
                </legend>
                <div class="flex flex-wrap gap-2">
                    @foreach ($triggers as $trigger)
                        <label class="group flex min-h-12 cursor-pointer items-center gap-1.5 rounded-full border-2 border-line-soft bg-surface px-[18px] text-base text-ink has-checked:border-ocean-700 has-checked:bg-ocean-700 has-checked:font-bold has-checked:text-white has-focus-visible:outline-3 has-focus-visible:outline-offset-2 has-focus-visible:outline-ocean-700">
                            <input
                                type="checkbox"
                                name="triggers[]"
                                value="{{ $trigger->value }}"
                                class="sr-only"
                                @checked(in_array($trigger->value, old('triggers', []), true))
                            >
                            <span aria-hidden="true" class="hidden group-has-checked:inline">✓</span>{{ $trigger->label() }}
                        </label>
                    @endforeach
                </div>
                @error('triggers.*')
                    <p class="text-sm font-bold text-sos">{{ $message }}</p>
                @enderror
            </fieldset>
        </x-card>

        <x-button>Neu starten</x-button>
        <a href="{{ route('profile.edit') }}" class="flex min-h-12 items-center justify-center text-base font-bold">Abbrechen</a>
    </form>
</x-layouts.page>
