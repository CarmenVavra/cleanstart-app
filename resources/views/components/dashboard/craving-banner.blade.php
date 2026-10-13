{{-- Hinweis auf einen laufenden bzw. abgelaufenen Craving-Timer, solange das Notfall-Sheet geschlossen ist --}}
<button
    type="button"
    x-show="cravingActive && ! sos"
    x-cloak
    x-on:click="openSos()"
    aria-haspopup="dialog"
    class="flex min-h-14 w-full items-center justify-between gap-3 rounded-[18px] bg-ocean-100 px-[18px] py-3 text-left text-ink"
>
    <span class="flex flex-col gap-0.5">
        <span class="text-base font-bold" x-text="cravingState === 'running' ? 'Suchtdruck-Timer läuft' : 'Geschafft – die Welle ist vorbei'">Suchtdruck-Timer läuft</span>
        <span class="text-sm text-ink-soft">Tippen zum Öffnen</span>
    </span>
    <span aria-hidden="true" class="text-[22px] font-bold tabular-nums" x-show="cravingState === 'running'" x-text="cravingClock"></span>
</button>
