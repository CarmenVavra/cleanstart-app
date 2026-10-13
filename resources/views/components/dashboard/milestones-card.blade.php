@props(['badges', 'progress'])

{{-- Meilenstein-Badges; Freischaltung wird live von Alpine (`dashboard`) aktualisiert --}}
<x-card aria-labelledby="ms-h">
    <div class="flex items-baseline justify-between gap-2">
        <h2 id="ms-h" class="text-[19px] font-bold">Erreichte Meilensteine</h2>
        <span class="text-[15px] text-ink-muted">
            <span x-text="unlockedCount">{{ $progress->unlockedCount() }}</span> von {{ $badges->count() }}
        </span>
    </div>

    <ul class="grid grid-cols-4 gap-x-2 gap-y-4">
        @foreach ($badges as $badge)
            @php($unlocked = $progress->isUnlocked($badge))

            <li class="flex flex-col items-center gap-2 text-center">
                {{-- Erreicht --}}
                <div
                    aria-hidden="true"
                    x-show="isUnlocked({{ $badge->days_required }})"
                    class="flex size-16 items-center justify-center rounded-full text-[17px] font-bold text-white"
                    style="background: {{ $badge->color() }}; box-shadow: 0 0 0 4px {{ $badge->haloColor() }};@unless ($unlocked) display: none;@endunless"
                >{{ $badge->short_label }}</div>

                {{-- Gesperrt --}}
                <div
                    aria-hidden="true"
                    x-show="! isUnlocked({{ $badge->days_required }})"
                    class="relative flex size-16 items-center justify-center rounded-full border-2 border-dashed border-line-dashed bg-locked text-[17px] font-bold text-ink-locked"
                    @if ($unlocked) style="display: none" @endif
                >
                    {{ $badge->short_label }}
                    <span class="absolute -right-1 -bottom-1 flex size-6 items-center justify-center rounded-full border-2 border-white bg-ink-locked">
                        <svg width="10" height="12" viewBox="0 0 10 12" focusable="false">
                            <path d="M2.5 5V3.5a2.5 2.5 0 0 1 5 0V5" fill="none" stroke="#FFFFFF" stroke-width="1.6" />
                            <rect x="1" y="5" width="8" height="6.5" rx="1.5" fill="#FFFFFF" />
                        </svg>
                    </span>
                </div>

                <span
                    @class(['text-sm leading-[1.2]', 'text-ink' => $unlocked, 'text-ink-locked' => ! $unlocked])
                    x-bind:class="{ 'text-ink': isUnlocked({{ $badge->days_required }}), 'text-ink-locked': ! isUnlocked({{ $badge->days_required }}) }"
                >
                    {{ $badge->title }}
                    <span class="sr-only" x-text="isUnlocked({{ $badge->days_required }}) ? '– erreicht' : '– noch gesperrt'">
                        – {{ $unlocked ? 'erreicht' : 'noch gesperrt' }}
                    </span>
                </span>
            </li>
        @endforeach
    </ul>
</x-card>
