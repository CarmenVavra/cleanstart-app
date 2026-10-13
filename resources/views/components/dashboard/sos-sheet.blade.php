@props(['user', 'reasons', 'strategies'])

{{-- Notfall-Sheet (Bottom-Sheet-Dialog) mit Hotline, Vertrauensperson und geführter Atemübung --}}
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
            href="tel:08001110111"
            class="flex min-h-[72px] flex-col justify-center gap-0.5 rounded-[18px] bg-ocean-700 px-[18px] py-3.5 text-white no-underline hover:text-white focus-visible:outline-ink"
        >
            <span class="text-[19px] font-bold">Telefonseelsorge anrufen</span>
            <span class="text-[15px] opacity-95">0800 111 0 111 · kostenlos, rund um die Uhr</span>
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
            <a href="tel:112" class="font-bold text-sos hover:text-sos-hover">Notruf 112</a>
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
