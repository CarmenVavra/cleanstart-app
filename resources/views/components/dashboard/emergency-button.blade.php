{{-- Pulsierender Notfall-Button; öffnet das Notfall-Sheet (Zustand im Alpine-`dashboard`) --}}
<div class="relative mt-1">
    <span aria-hidden="true" class="pointer-events-none absolute inset-0 z-0 animate-pulse-sos rounded-3xl bg-sos"></span>
    <span aria-hidden="true" class="pointer-events-none absolute inset-0 z-0 animate-pulse-sos rounded-3xl bg-sos [animation-delay:1.2s]"></span>

    <button
        type="button"
        x-ref="sosButton"
        x-on:click="openSos()"
        aria-haspopup="dialog"
        aria-label="Notfall – sofort Hilfe holen"
        class="relative z-[1] flex min-h-24 w-full items-center justify-center gap-4 rounded-3xl bg-sos px-5 py-4 text-white shadow-sos transition-transform duration-100 hover:bg-sos-hover focus-visible:outline-4 focus-visible:outline-offset-4 focus-visible:outline-ink active:scale-[.98]"
    >
        <span aria-hidden="true" class="flex size-[52px] flex-none items-center justify-center rounded-full border-2 border-white/55 bg-white/18 text-[28px] font-bold">!</span>
        <span class="flex flex-col items-start gap-0.5">
            <span class="text-[30px] leading-none font-bold tracking-[.08em]">NOTFALL</span>
            <span class="text-base opacity-95">Sofort Hilfe holen</span>
        </span>
    </button>
</div>
