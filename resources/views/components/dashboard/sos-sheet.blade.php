@props(['user', 'reasons', 'strategies'])

{{-- Notfall-Sheet (Bottom-Sheet-Dialog) mit Hotline, Vertrauensperson, geführter Atemübung und Craving-Timer --}}
<div
    x-show="sos"
    x-cloak
    x-on:click.self="closeSos()"
    class="fixed inset-0 z-50 flex items-end justify-center bg-[rgba(20,32,30,.6)]"
>
    <div
        role="dialog"
        aria-modal="true"
        aria-labelledby="sos-h"
        x-trap.inert.noscroll="sos"
        class="flex max-h-[92vh] w-full max-w-[440px] flex-col gap-4 overflow-auto rounded-t-[28px] bg-white px-5 pt-3 pb-7"
    >
        <div aria-hidden="true" class="h-[5px] w-11 self-center rounded-full bg-line"></div>

        <div class="flex flex-col gap-1.5">
            <h2 id="sos-h" class="text-[26px] font-bold">Du bist nicht allein.</h2>
            <p class="text-[17px]/[1.45] text-ink-muted">Atme einmal tief durch. Wähle, was dir jetzt am meisten hilft.</p>
        </div>

        <a
            href="{{ $user->country->hotlineTelHref() }}"
            class="flex min-h-[72px] flex-col justify-center gap-0.5 rounded-[18px] bg-ocean-700 px-[18px] py-3.5 text-white no-underline hover:text-white focus-visible:outline-ink"
        >
            <span class="text-[19px] font-bold">{{ $user->country->hotlineName() }} anrufen</span>
            <span class="text-[15px] opacity-95">{{ $user->country->hotlineNumber() }} · {{ $user->country->hotlineNote() }}</span>
        </a>

        @if ($user->hasTrustedContact())
            <a
                href="{{ $user->trustedContactTelHref() }}"
                class="flex min-h-[72px] flex-col justify-center gap-0.5 rounded-[18px] bg-ocean-100 px-[18px] py-3.5 text-ink no-underline hover:text-ink"
            >
                <span class="text-[19px] font-bold">{{ $user->trusted_contact_name }} anrufen</span>
                <span class="text-[15px] text-ink-soft">Deine Vertrauensperson</span>
            </a>
        @else
            <a
                href="{{ route('profile.edit') }}"
                class="flex min-h-[72px] flex-col justify-center gap-0.5 rounded-[18px] bg-ocean-100 px-[18px] py-3.5 text-ink no-underline hover:text-ink"
            >
                <span class="text-[19px] font-bold">Vertrauensperson hinterlegen</span>
                <span class="text-[15px] text-ink-soft">Name und Nummer im Profil eintragen</span>
            </a>
        @endif

        <button
            type="button"
            x-on:click="toggleBreath()"
            x-bind:aria-expanded="breathing.toString()"
            aria-expanded="false"
            aria-controls="breath-area"
            class="flex min-h-[72px] flex-col items-start justify-center gap-0.5 rounded-[18px] bg-sage-100 px-[18px] py-3.5 text-left text-ink"
        >
            <span class="text-[19px] font-bold" x-text="breathing ? 'Atemübung beenden' : 'Atemübung starten'">Atemübung starten</span>
            <span class="text-[15px] text-ink-soft">Geführt: 4 s einatmen – 7 s halten – 8 s ausatmen</span>
        </button>

        <div id="breath-area">
            <template x-if="breathing">
                <div class="flex flex-col items-center gap-3.5 pt-2 pb-1">
                    <div class="relative flex size-[180px] items-center justify-center">
                        {{-- Kreisgröße & Dauer kommen aus dem 4-7-8-Timer (resources/js/dashboard.js) --}}
                        <div
                            aria-hidden="true"
                            class="absolute inset-0 rounded-full bg-[radial-gradient(circle,#B9D9CD_0%,#DCEBE5_70%)] transition-transform ease-in-out"
                            style="transform: scale(.55)"
                            x-bind:style="{ transform: `scale(${circleScale})`, transitionDuration: `${circleDuration}s` }"
                        ></div>
                        <div class="relative flex flex-col items-center gap-1 text-sage-900">
                            <span aria-live="polite" class="text-[22px] font-bold" x-text="breathPhase"></span>
                            <span aria-hidden="true" class="text-[30px] leading-none font-bold tabular-nums" x-text="remaining"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Craving-Timer: Suchtdruck kommt in Wellen und flacht nach ca. 15 Minuten ab --}}
        <button
            type="button"
            x-on:click="toggleCraving()"
            x-bind:aria-expanded="cravingActive.toString()"
            aria-expanded="false"
            aria-controls="craving-area"
            class="flex min-h-[72px] flex-col items-start justify-center gap-0.5 rounded-[18px] bg-sage-100 px-[18px] py-3.5 text-left text-ink"
        >
            <span class="text-[19px] font-bold" x-text="cravingButtonLabel">Suchtdruck-Timer starten</span>
            <span class="text-[15px] text-ink-soft" x-text="cravingButtonHint">{{ \App\Models\CravingEpisode::TIMER_MINUTES }} Minuten – die Welle geht vorbei</span>
        </button>

        <div id="craving-area">
            <template x-if="cravingState === 'running'">
                <div class="flex flex-col items-center gap-3.5 pt-2 pb-1">
                    <div class="relative flex size-[180px] items-center justify-center">
                        <svg aria-hidden="true" viewBox="0 0 180 180" class="absolute inset-0 -rotate-90">
                            <circle cx="90" cy="90" r="80" fill="none" stroke-width="12" class="stroke-sage-100" />
                            <circle
                                cx="90" cy="90" r="80" fill="none" stroke-width="12" stroke-linecap="round"
                                class="stroke-sage-600 transition-[stroke-dashoffset] duration-1000 ease-linear"
                                x-bind:stroke-dasharray="cravingRingLength"
                                x-bind:stroke-dashoffset="cravingRingOffset"
                            />
                        </svg>
                        <div class="relative flex flex-col items-center gap-1 text-sage-900">
                            <span aria-hidden="true" class="text-[34px] leading-none font-bold tabular-nums" x-text="cravingClock"></span>
                            <span aria-hidden="true" class="text-sm text-ink-soft">verbleibend</span>
                            <span role="timer" class="sr-only" x-text="cravingTimerLabel"></span>
                        </div>
                    </div>
                    <p aria-live="polite" class="text-center text-base/[1.45] text-ink-soft" x-text="cravingMessage"></p>
                </div>
            </template>

            <template x-if="cravingState === 'done'">
                <div role="status" class="flex flex-col gap-3 rounded-[18px] bg-sage-100 p-[18px]">
                    <strong class="text-lg">Geschafft – die Welle ist vorbei.</strong>
                    <p class="text-[15px]/[1.45] text-ink-soft">Hast du dem Suchtdruck standgehalten?</p>
                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            x-on:click="saveCraving()"
                            x-bind:disabled="cravingSaving"
                            class="min-h-12 flex-1 rounded-xl bg-sage-900 px-4 text-base font-bold text-white disabled:opacity-70"
                        >Ja, überstanden</button>
                        <button
                            type="button"
                            x-on:click="declineCraving()"
                            class="min-h-12 flex-1 rounded-xl border-2 border-sage-600 bg-transparent px-4 text-base font-bold text-sage-900"
                        >Diesmal nicht</button>
                    </div>
                    <p x-show="cravingError" role="alert" class="text-sm font-bold text-sos">
                        Das hat leider nicht geklappt. Bitte versuch es noch einmal.
                    </p>
                </div>
            </template>

            <template x-if="cravingState === 'saved'">
                <div role="status" class="flex flex-col gap-1.5 rounded-[18px] bg-sage-100 p-[18px]">
                    <strong class="text-lg">Stark! Du bist der Welle nicht gefolgt.</strong>
                    <p class="text-[15px]/[1.45] text-ink-soft">
                        Du hast Suchtdruck schon <strong x-text="cravingsOvercomeLabel"></strong> überstanden. Jedes Mal wird es ein Stück leichter.
                    </p>
                </div>
            </template>

            <template x-if="cravingState === 'declined'">
                <div role="status" class="flex flex-col gap-1.5 rounded-[18px] bg-surface p-[18px]">
                    <strong class="text-lg">Danke für deine Ehrlichkeit.</strong>
                    <p class="text-[15px]/[1.45] text-ink-soft">
                        Ein Rückfall löscht nicht, was du geschafft hast. Wenn du magst, starte deinen Zähler neu –
                        deine bisherige Serie bleibt gespeichert.
                    </p>
                    <a href="{{ route('relapse.create') }}" class="flex min-h-11 items-center self-start text-base font-bold">Neu starten</a>
                </div>
            </template>
        </div>

        {{-- Persönlicher Notfallplan --}}
        @if ($reasons->isNotEmpty())
            <section aria-labelledby="sos-why-h" class="flex flex-col gap-2 rounded-[18px] bg-surface px-[18px] py-3.5">
                <h3 id="sos-why-h" class="text-[17px] font-bold">Dein Warum</h3>
                <ul class="flex flex-col gap-1.5 text-base">
                    @foreach ($reasons as $reason)
                        <li class="flex gap-2"><span aria-hidden="true" class="text-sos">♥</span><span>{{ $reason->content }}</span></li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if ($strategies->isNotEmpty())
            <section aria-labelledby="sos-help-h" class="flex flex-col gap-2 rounded-[18px] bg-surface px-[18px] py-3.5">
                <h3 id="sos-help-h" class="text-[17px] font-bold">Was dir jetzt helfen kann</h3>
                <ul class="flex flex-col gap-1.5 text-base">
                    @foreach ($strategies as $strategy)
                        <li class="flex gap-2"><span aria-hidden="true" class="font-bold text-sage-600">✓</span><span>{{ $strategy->content }}</span></li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if ($reasons->isEmpty() && $strategies->isEmpty())
            <a href="{{ route('coping.index') }}" class="flex min-h-11 flex-col justify-center rounded-[18px] border-2 border-dashed border-line px-[18px] py-3 text-ink no-underline hover:text-ink">
                <span class="text-base font-bold">Notfallplan anlegen</span>
                <span class="text-sm text-ink-muted">Schreib auf, wofür du kämpfst und was dir hilft.</span>
            </a>
        @endif

        <p class="text-[15px]/[1.45] text-ink-muted">
            Bei akuter Gefahr für dich oder andere:
            <a href="{{ $user->country->emergencyTelHref() }}" class="font-bold text-sos hover:text-sos-hover">Notruf {{ $user->country->emergencyNumber() }}</a>
        </p>

        <button
            type="button"
            x-on:click="closeSos()"
            class="min-h-[52px] rounded-[14px] border-2 border-line bg-white text-[17px] font-bold text-ink hover:bg-surface"
        >
            Schließen
        </button>
    </div>
</div>
