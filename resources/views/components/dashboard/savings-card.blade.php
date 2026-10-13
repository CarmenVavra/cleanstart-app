@props(['savedLabel' => null, 'summary' => null])

{{-- Ersparnis-Rechner: serverseitig vorgerendert, danach zählt Alpine (`dashboard`) live mit --}}
@if ($savedLabel !== null)
    <x-card aria-labelledby="savings-h">
        <div class="flex flex-col gap-1">
            <h2 id="savings-h" class="text-sm font-bold tracking-[.08em] text-sage-600 uppercase">Gespart seit deinem Start</h2>
            <p class="text-[40px] leading-[1.1] font-bold text-sage-900 tabular-nums" x-text="savedLabel">{{ $savedLabel }}</p>
        </div>
        <p class="text-[15px] text-ink-muted">{{ $summary }}</p>
        <a href="{{ route('profile.edit') }}#ersparnis" class="self-start text-sm font-bold">Betrag ändern</a>
    </x-card>
@else
    <a href="{{ route('profile.edit') }}#ersparnis" class="flex min-h-11 flex-col justify-center rounded-[18px] border-2 border-dashed border-line px-[18px] py-3 text-ink no-underline hover:text-ink">
        <span class="text-base font-bold">Ersparnis berechnen</span>
        <span class="text-sm text-ink-muted">Trag ein, was du früher ausgegeben hast – und sieh, was du schon gespart hast.</span>
    </a>
@endif
