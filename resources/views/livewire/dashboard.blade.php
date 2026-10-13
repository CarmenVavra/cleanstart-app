<div x-data="dashboard(@js($progress->clientConfig()))" x-on:keydown.escape.window="if (sos) closeSos()">
    <main id="main" class="mx-auto flex min-h-screen max-w-[440px] flex-col gap-5 px-5 pt-5 pb-10">

        {{-- Kopfzeile --}}
        <header wire:ignore class="flex items-center justify-between gap-3 pt-1">
            <div class="flex flex-col gap-0.5">
                <x-wordmark />
                <h1 class="text-2xl/[1.2] font-bold">
                    <span x-text="greeting">{{ $greeting }}</span>, {{ $user->username }}
                </h1>
                <p class="text-[15px] text-ink-muted" x-text="todayLabel">{{ $todayLabel }}</p>
            </div>
            <a
                href="{{ route('profile.edit') }}"
                aria-label="Profil öffnen"
                class="flex size-12 flex-none items-center justify-center rounded-full border-2 border-line bg-white text-lg font-bold text-sage-900 no-underline hover:text-sage-900"
            >{{ $user->initial() }}</a>
        </header>

        <div wire:ignore class="contents">
            <x-dashboard.emergency-button />
            <x-dashboard.counter-card :progress="$progress" :start-label="$startLabel" />
            <x-dashboard.milestones-card :badges="$badges" :progress="$progress" />
        </div>

        {{-- Daily Check-in --}}
        <x-card
            aria-labelledby="ci-h"
            class="gap-5"
            x-data="checkin({ mood: $wire.entangle('form.mood'), triggers: $wire.entangle('form.triggers') })"
        >
            <div class="flex flex-col gap-1">
                <span class="text-sm font-bold tracking-[.08em] text-sage-600 uppercase">Daily Check-in</span>
                <h2 id="ci-h" class="text-[22px] font-bold">Wie geht es dir heute?</h2>
            </div>

            @if ($saved)
                <div role="status" class="flex flex-col gap-3 rounded-[18px] bg-sage-100 p-[18px]">
                    <div class="flex items-center gap-3">
                        <span aria-hidden="true" class="text-4xl leading-none">{{ $selectedMood?->emoji() }}</span>
                        <div class="flex flex-col gap-0.5">
                            <strong class="text-lg">Danke, gespeichert.</strong>
                            <span class="text-[15px] text-ink-soft">Stimmung: {{ $selectedMood?->label() }}</span>
                        </div>
                    </div>
                    <p class="text-[15px]/[1.45] text-ink-soft">{{ $savedSummary }}</p>
                    @if (filled($form->notes))
                        <p class="text-sm text-ink-soft"><span aria-hidden="true">🔒</span> Deine Notiz ist verschlüsselt gespeichert.</p>
                    @endif
                    <button
                        type="button"
                        wire:click="edit"
                        class="min-h-11 self-start rounded-xl border-2 border-sage-600 bg-transparent px-[18px] text-base font-bold text-sage-900 hover:bg-white/50"
                    >Bearbeiten</button>
                </div>
            @else
                <div class="flex flex-col gap-[22px]">
                    {{-- Stimmung --}}
                    <div
                        role="radiogroup"
                        aria-labelledby="ci-h"
                        class="flex gap-1.5"
                        x-on:keydown.right.prevent="moveMood(1)"
                        x-on:keydown.down.prevent="moveMood(1)"
                        x-on:keydown.left.prevent="moveMood(-1)"
                        x-on:keydown.up.prevent="moveMood(-1)"
                    >
                        @foreach ($moods as $mood)
                            @php($isSelected = $form->mood === $mood->value)

                            <button
                                type="button"
                                role="radio"
                                data-mood="{{ $mood->value }}"
                                aria-label="{{ $mood->label() }}"
                                aria-checked="{{ $isSelected ? 'true' : 'false' }}"
                                tabindex="{{ $isSelected || ($form->mood === null && $loop->first) ? '0' : '-1' }}"
                                x-bind:aria-checked="isMood({{ $mood->value }}).toString()"
                                x-bind:tabindex="moodTabindex({{ $mood->value }})"
                                x-on:click="selectMood({{ $mood->value }})"
                                @class([
                                    'flex min-h-[84px] min-w-0 flex-1 flex-col items-center justify-center gap-1.5 rounded-[18px] border-2 px-0.5 py-2 text-ink transition-colors duration-150 focus-visible:outline-offset-2',
                                    'border-sage-600 bg-sage-100' => $isSelected,
                                    'border-transparent bg-surface' => ! $isSelected,
                                ])
                                x-bind:class="{
                                    'border-sage-600 bg-sage-100': isMood({{ $mood->value }}),
                                    'border-transparent bg-surface': ! isMood({{ $mood->value }}),
                                }"
                            >
                                <span
                                    aria-hidden="true"
                                    @class(['text-[30px] leading-none', '[filter:grayscale(.6)_opacity(.7)]' => $form->mood !== null && ! $isSelected])
                                    x-bind:class="{ '[filter:grayscale(.6)_opacity(.7)]': isDimmed({{ $mood->value }}) }"
                                >{{ $mood->emoji() }}</span>
                                <span
                                    @class(['text-[13px] leading-[1.15]', 'font-bold' => $isSelected])
                                    x-bind:class="{ 'font-bold': isMood({{ $mood->value }}) }"
                                >{{ $mood->label() }}</span>
                            </button>
                        @endforeach
                    </div>

                    {{-- Trigger --}}
                    <div class="flex flex-col gap-3">
                        <div class="flex flex-wrap items-baseline justify-between gap-2">
                            <h3 id="tr-h" class="text-[17px] font-bold">Was beeinflusst dich gerade?</h3>
                            <span class="text-sm text-ink-muted">Optional · mehrere möglich</span>
                        </div>
                        <div role="group" aria-labelledby="tr-h" class="flex flex-wrap gap-2">
                            @foreach ($triggers as $trigger)
                                @php($isActive = in_array($trigger->value, $form->triggers, true))

                                <button
                                    type="button"
                                    aria-pressed="{{ $isActive ? 'true' : 'false' }}"
                                    x-bind:aria-pressed="hasTrigger(@js($trigger->value)).toString()"
                                    x-on:click="toggleTrigger(@js($trigger->value))"
                                    @class([
                                        'flex min-h-12 items-center gap-1.5 rounded-full border-2 px-[18px] text-base focus-visible:outline-offset-2',
                                        'border-ocean-700 bg-ocean-700 font-bold text-white' => $isActive,
                                        'border-line-soft bg-surface text-ink' => ! $isActive,
                                    ])
                                    x-bind:class="{
                                        'border-ocean-700 bg-ocean-700 font-bold text-white': hasTrigger(@js($trigger->value)),
                                        'border-line-soft bg-surface text-ink': ! hasTrigger(@js($trigger->value)),
                                    }"
                                >
                                    <span aria-hidden="true" x-show="hasTrigger(@js($trigger->value))" @unless ($isActive) style="display: none" @endunless>✓</span>{{ $trigger->label() }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Optionale, verschlüsselte Notiz (Konzept: „Notizfeld“) --}}
                    <div x-data="{ open: @js(filled($form->notes)) }" class="flex flex-col gap-2">
                        <button
                            type="button"
                            x-on:click="open = ! open"
                            x-bind:aria-expanded="open.toString()"
                            aria-expanded="{{ filled($form->notes) ? 'true' : 'false' }}"
                            aria-controls="ci-notes"
                            class="flex min-h-11 items-center gap-2 self-start text-base font-bold text-sage-900"
                        >
                            <span aria-hidden="true" class="flex size-6 items-center justify-center rounded-full bg-sage-100" x-text="open ? '−' : '+'">{{ filled($form->notes) ? '−' : '+' }}</span>
                            Persönliche Notiz <span class="font-normal text-ink-muted">(optional)</span>
                        </button>
                        <div id="ci-notes" x-show="open" class="flex flex-col gap-1.5" @unless (filled($form->notes)) style="display: none" @endunless>
                            <label for="ci-notes-input" class="sr-only">Persönliche Notiz</label>
                            <textarea
                                id="ci-notes-input"
                                wire:model="form.notes"
                                rows="3"
                                maxlength="2000"
                                aria-describedby="ci-notes-hint"
                                class="w-full rounded-2xl border-2 border-line-soft bg-surface p-3.5 text-base text-ink focus:border-sage-600"
                            ></textarea>
                            <p id="ci-notes-hint" class="text-sm text-ink-muted">
                                <span aria-hidden="true">🔒</span> Nur für dich – wird verschlüsselt gespeichert.
                            </p>
                        </div>
                    </div>

                    {{-- Speichern --}}
                    <div class="flex flex-col gap-2">
                        <button
                            type="button"
                            x-on:click="submit()"
                            aria-disabled="{{ $form->mood === null ? 'true' : 'false' }}"
                            x-bind:aria-disabled="(mood === null).toString()"
                            wire:loading.attr="aria-busy"
                            wire:target="save"
                            @class([
                                'min-h-14 w-full rounded-2xl text-lg font-bold',
                                'cursor-not-allowed bg-disabled text-ink-muted' => $form->mood === null,
                                'bg-sage-900 text-white' => $form->mood !== null,
                            ])
                            x-bind:class="{
                                'cursor-not-allowed bg-disabled text-ink-muted': mood === null,
                                'bg-sage-900 text-white': mood !== null,
                            }"
                        >Check-in speichern</button>

                        <p aria-live="polite" class="min-h-[18px] text-center text-sm text-ink-muted" x-text="hint">
                            {{ $form->mood === null ? 'Dauert nur ein paar Sekunden.' : '' }}
                        </p>

                        @if ($errors->any())
                            <div role="alert" class="text-center text-sm font-bold text-sos">
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </x-card>
    </main>

    <div wire:ignore>
        <x-dashboard.sos-sheet :user="$user" />
    </div>
</div>
