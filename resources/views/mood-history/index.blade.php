<x-layouts.page title="Verlauf" heading="Dein Stimmungsverlauf" :back="route('dashboard')">
    {{-- Zeitraum --}}
    <nav aria-label="Zeitraum" class="flex gap-2">
        @foreach ($ranges as $range)
            <a
                href="{{ route('mood.index', ['tage' => $range]) }}"
                @if ($range === $days) aria-current="page" @endif
                @class([
                    'flex min-h-11 flex-1 items-center justify-center rounded-full border-2 px-3 text-base no-underline',
                    'border-sage-900 bg-sage-900 font-bold text-white hover:text-white' => $range === $days,
                    'border-line-soft bg-white text-ink hover:text-ink' => $range !== $days,
                ])
            >{{ $range }} Tage</a>
        @endforeach
    </nav>

    @if ($chart === null)
        <x-card aria-labelledby="empty-h">
            <h2 id="empty-h" class="text-[19px] font-bold">Noch keine Check-ins in diesem Zeitraum</h2>
            <p class="text-[15px]/[1.45] text-ink-muted">
                Ein kurzer Check-in am Tag reicht. Mit der Zeit erkennst du hier, wie es dir geht und was dich beeinflusst.
            </p>
            <a href="{{ route('dashboard') }}" class="flex min-h-12 items-center justify-center rounded-2xl bg-sage-900 px-5 text-base font-bold text-white no-underline hover:text-white">
                Zum Check-in
            </a>
        </x-card>
    @else
        {{-- Überblick --}}
        <section aria-labelledby="summary-h" class="grid grid-cols-2 gap-3">
            <h2 id="summary-h" class="sr-only">Überblick</h2>
            <div class="flex flex-col gap-1 rounded-3xl bg-white px-4 py-4 shadow-card">
                <span class="text-sm text-ink-muted">Check-ins</span>
                <span class="text-[28px] leading-none font-bold tabular-nums">{{ $checkinCount }}</span>
                <span class="text-sm text-ink-muted">von {{ $days }} Tagen</span>
            </div>
            <div class="flex flex-col gap-1 rounded-3xl bg-white px-4 py-4 shadow-card">
                <span class="text-sm text-ink-muted">Ø Stimmung</span>
                <span class="flex items-center gap-2 text-[28px] leading-none font-bold tabular-nums">
                    <span aria-hidden="true">{{ $averageMood?->emoji() }}</span>{{ $averageLabel }}
                </span>
                <span class="text-sm text-ink-muted">{{ $averageMood?->label() }}</span>
            </div>
        </section>

        {{-- Verlauf --}}
        <x-card aria-labelledby="chart-h">
            <div class="flex flex-col gap-1">
                <h2 id="chart-h" class="text-[19px] font-bold">Stimmung pro Tag</h2>
                <p class="text-sm text-ink-muted">Je höher der Balken, desto besser ging es dir.</p>
            </div>

            <svg viewBox="0 0 {{ $chart['width'] }} {{ $chart['height'] }}" class="h-auto w-full" aria-hidden="true" focusable="false">
                @foreach ($chart['gridlines'] as $line)
                    <line x1="{{ $line['x1'] }}" x2="{{ $line['x2'] }}" y1="{{ $line['y'] }}" y2="{{ $line['y'] }}" class="stroke-line-soft" stroke-width="1" />
                    <text x="{{ $line['labelX'] }}" y="{{ $line['y'] }}" font-size="12" text-anchor="middle" dominant-baseline="central">{{ $line['emoji'] }}</text>
                @endforeach

                @foreach ($chart['markers'] as $marker)
                    <line x1="{{ $marker['x'] }}" x2="{{ $marker['x'] }}" y1="{{ $marker['top'] }}" y2="{{ $marker['bottom'] }}" class="stroke-ocean-700" stroke-width="1.5" stroke-dasharray="3 3" />
                @endforeach

                @foreach ($chart['gaps'] as $gap)
                    <circle cx="{{ $gap['x'] }}" cy="{{ $gap['y'] }}" r="1.3" class="fill-line-dashed" />
                @endforeach

                @foreach ($chart['bars'] as $bar)
                    <rect x="{{ $bar['x'] }}" y="{{ $bar['y'] }}" width="{{ $bar['width'] }}" height="{{ $bar['height'] }}" rx="{{ min(3, $bar['width'] / 2) }}" class="fill-sage-600" />
                @endforeach

                @foreach ($chart['labels'] as $label)
                    <text x="{{ $label['x'] }}" y="{{ $label['y'] }}" font-size="12" text-anchor="{{ $label['anchor'] }}" class="fill-ink-muted">{{ $label['text'] }}</text>
                @endforeach
            </svg>

            <ul class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-ink-muted">
                <li class="flex items-center gap-1.5"><span aria-hidden="true" class="inline-block h-3 w-2.5 rounded-sm bg-sage-600"></span> Check-in</li>
                <li class="flex items-center gap-1.5"><span aria-hidden="true" class="inline-block size-1.5 rounded-full bg-line-dashed"></span> kein Check-in</li>
                @if ($hasRestarts)
                    <li class="flex items-center gap-1.5"><span aria-hidden="true" class="inline-block h-3 border-l-2 border-dashed border-ocean-700"></span> Neustart</li>
                @endif
            </ul>

            {{-- Textalternative zum Diagramm --}}
            <details class="rounded-2xl bg-surface px-4 py-2">
                <summary class="flex min-h-11 cursor-pointer items-center text-base font-bold">Werte als Tabelle anzeigen</summary>
                <div class="overflow-x-auto pb-2">
                    <table class="w-full text-left text-sm">
                        <caption class="sr-only">Stimmung und Trigger pro Tag</caption>
                        <thead>
                            <tr class="border-b border-line">
                                <th scope="col" class="py-2 pr-3">Datum</th>
                                <th scope="col" class="py-2 pr-3">Stimmung</th>
                                <th scope="col" class="py-2">Trigger</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (array_reverse($timeline) as $day)
                                @continue($day['mood'] === null && ! $day['restart'])
                                <tr class="border-b border-line-soft align-top">
                                    <th scope="row" class="py-2 pr-3 font-normal whitespace-nowrap">{{ $day['date']->translatedFormat('D, j. M') }}</th>
                                    <td class="py-2 pr-3">
                                        @if ($day['mood'])
                                            <span aria-hidden="true">{{ $day['mood']->emoji() }}</span> {{ $day['mood']->label() }}
                                        @else
                                            –
                                        @endif
                                        @if ($day['restart'])
                                            <span class="block font-bold text-ocean-700">Neustart</span>
                                        @endif
                                    </td>
                                    <td class="py-2">{{ $day['triggers'] === [] ? '–' : implode(', ', $day['triggers']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        </x-card>

        {{-- Muster --}}
        <x-card aria-labelledby="insights-h">
            <h2 id="insights-h" class="text-[19px] font-bold">Was dir auffallen könnte</h2>

            @if ($showsPatterns && $insights !== [])
                <ul class="flex flex-col gap-2.5">
                    @foreach ($insights as $insight)
                        <li class="flex gap-2.5 rounded-2xl bg-sage-50 px-4 py-3 text-base/[1.45]">
                            <span aria-hidden="true" class="font-bold text-sage-600">→</span><span>{{ $insight }}</span>
                        </li>
                    @endforeach
                </ul>
                @if ($trend === 'worse')
                    <a href="{{ route('coping.index') }}" class="self-start text-base font-bold">Zu meinem Notfallplan</a>
                @endif
            @else
                <p class="text-[15px]/[1.45] text-ink-muted">
                    Ab {{ $minForPatterns }} Check-ins im gewählten Zeitraum zeigen wir dir hier Muster –
                    zum Beispiel, welche Trigger dich besonders belasten.
                </p>
            @endif
        </x-card>

        {{-- Trigger --}}
        @if ($triggerStats !== [])
            <x-card aria-labelledby="triggers-h">
                <h2 id="triggers-h" class="text-[19px] font-bold">Häufigste Trigger</h2>
                <ul class="flex flex-col gap-3.5">
                    @foreach ($triggerStats as $stat)
                        <li class="flex flex-col gap-1.5">
                            <div class="flex items-baseline justify-between gap-3 text-base">
                                <span class="font-bold">{{ $stat['trigger']->label() }}</span>
                                <span class="text-sm whitespace-nowrap text-ink-muted">
                                    an {{ $stat['count'] }} {{ $stat['count'] === 1 ? 'Tag' : 'Tagen' }} · Ø <span aria-hidden="true">{{ $stat['mood']->emoji() }}</span> {{ $stat['averageLabel'] }}
                                </span>
                            </div>
                            <div aria-hidden="true" class="h-2 overflow-hidden rounded-full bg-sage-100">
                                <div class="h-full rounded-full bg-ocean-700" style="width: {{ max(4, $stat['share']) }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </x-card>
        @endif

        {{-- Wochentage --}}
        @if ($showsPatterns)
            <x-card aria-labelledby="weekdays-h">
                <div class="flex flex-col gap-1">
                    <h2 id="weekdays-h" class="text-[19px] font-bold">Stimmung nach Wochentag</h2>
                    <p class="text-sm text-ink-muted">Durchschnitt deiner Check-ins (1 = sehr schlecht, 5 = sehr gut).</p>
                </div>
                <ul class="flex flex-col gap-2">
                    @foreach ($weekdayStats as $stat)
                        <li class="grid grid-cols-[2.25rem_1fr_3.5rem] items-center gap-3 text-base">
                            <abbr title="{{ $stat['name'] }}" class="font-bold no-underline">{{ $stat['short'] }}</abbr>
                            <div aria-hidden="true" class="h-2 overflow-hidden rounded-full bg-sage-100">
                                <div class="h-full rounded-full bg-sage-600" style="width: {{ $stat['share'] }}%"></div>
                            </div>
                            <span class="text-right text-sm tabular-nums">
                                @if ($stat['averageLabel'] !== null)
                                    <span aria-hidden="true">{{ $stat['mood']?->emoji() }}</span> {{ $stat['averageLabel'] }}
                                @else
                                    <span aria-hidden="true">–</span><span class="sr-only">keine Daten</span>
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            </x-card>
        @endif
    @endif

    <p class="text-sm text-ink-muted">
        <span aria-hidden="true">🔒</span> Deine persönlichen Notizen bleiben verschlüsselt und erscheinen hier nicht.
    </p>
</x-layouts.page>
