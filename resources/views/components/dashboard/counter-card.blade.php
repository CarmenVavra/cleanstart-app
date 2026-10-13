@props(['progress', 'startLabel', 'longestPreviousStreak' => null])

{{-- Clean-Counter: serverseitig vorgerendert, danach tickt Alpine (`dashboard`) sekündlich weiter --}}
<section aria-labelledby="counter-h" class="flex flex-col gap-[18px] rounded-[28px] bg-sage-900 px-[22px] pt-6 pb-[22px] text-sage-50">
    <div class="flex items-baseline justify-between gap-2">
        <h2 id="counter-h" class="text-[15px] font-bold tracking-[.08em] text-sage-200 uppercase">Clean seit</h2>
        <span class="text-sm text-sage-200">{{ $startLabel }}</span>
    </div>

    <p class="sr-only" x-text="counterAria">
        {{ $progress->days() }} {{ $progress->days() === 1 ? 'Tag' : 'Tage' }}, {{ $progress->hours() }} Stunden und {{ $progress->minutes() }} Minuten clean
    </p>

    <div aria-hidden="true" class="flex items-baseline gap-2.5">
        <span class="text-[84px] leading-[.9] font-bold tracking-[-.02em] tabular-nums" x-text="days">{{ $progress->days() }}</span>
        <span class="text-2xl font-bold text-sage-150" x-text="daysWord">{{ $progress->days() === 1 ? 'Tag' : 'Tage' }}</span>
    </div>

    <div aria-hidden="true" class="flex gap-2.5">
        <div class="flex flex-1 flex-col gap-0.5 rounded-2xl bg-white/8 px-3.5 py-3">
            <span class="text-[30px] leading-[1.1] font-bold tabular-nums" x-text="hoursPadded">{{ sprintf('%02d', $progress->hours()) }}</span>
            <span class="text-sm text-sage-200">Stunden</span>
        </div>
        <div class="flex flex-1 flex-col gap-0.5 rounded-2xl bg-white/8 px-3.5 py-3">
            <span class="text-[30px] leading-[1.1] font-bold tabular-nums" x-text="minutesPadded">{{ sprintf('%02d', $progress->minutes()) }}</span>
            <span class="text-sm text-sage-200">Minuten</span>
        </div>
    </div>

    <div x-show="hasNext" class="flex flex-col gap-2" @if ($progress->nextBadge() === null) style="display: none" @endif>
        <div
            role="progressbar"
            aria-valuemin="0"
            aria-valuemax="100"
            aria-valuenow="{{ $progress->progressPercent() }}"
            x-bind:aria-valuenow="progress"
            aria-label="Fortschritt bis zum nächsten Meilenstein"
            class="h-2.5 overflow-hidden rounded-full bg-white/14"
        >
            <div
                class="h-full rounded-full bg-sage-300"
                style="width: {{ max(3, $progress->progressPercent()) }}%"
                x-bind:style="{ width: progressWidth }"
            ></div>
        </div>
        <p class="text-[15px] text-sage-150" x-text="nextText">{{ $progress->nextText() }}</p>
    </div>

    @if ($longestPreviousStreak !== null)
        <p class="border-t border-white/14 pt-3 text-sm text-sage-200">
            Bisher längste Serie: {{ $longestPreviousStreak }} {{ $longestPreviousStreak === 1 ? 'Tag' : 'Tage' }}
        </p>
    @endif
</section>
