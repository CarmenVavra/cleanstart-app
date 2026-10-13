<x-layouts.page title="Notfallplan" heading="Mein Notfallplan" :back="route('dashboard')">
    <p class="text-[17px]/[1.45] text-ink-muted">
        Schreib auf, was dich trägt. In schwierigen Momenten siehst du diese Einträge direkt im Notfall-Fenster.
        Alles wird verschlüsselt gespeichert.
    </p>

    @foreach ($types as $type)
        @php($items = $itemsByType->get($type->value, collect()))

        <x-card aria-labelledby="coping-{{ $type->value }}-h">
            <div class="flex flex-col gap-1">
                <div class="flex items-baseline justify-between gap-2">
                    <h2 id="coping-{{ $type->value }}-h" class="text-[19px] font-bold">{{ $type->label() }}</h2>
                    <span class="text-[15px] text-ink-muted">{{ $items->count() }} von {{ \App\Enums\CopingType::MAX_ITEMS }}</span>
                </div>
                <p class="text-[15px]/[1.45] text-ink-muted">{{ $type->description() }}</p>
            </div>

            @if ($items->isNotEmpty())
                <ul class="flex flex-col gap-2">
                    @foreach ($items as $item)
                        <li class="flex items-center justify-between gap-3 rounded-xl bg-surface py-1.5 pr-1.5 pl-3.5">
                            <span class="text-base break-words">{{ $item->content }}</span>
                            <form method="POST" action="{{ route('coping.destroy', $item) }}" class="flex-none">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    aria-label="„{{ $item->content }}“ löschen"
                                    class="flex min-h-11 min-w-11 items-center justify-center rounded-xl text-sm font-bold text-ink-muted hover:bg-white hover:text-sos"
                                >Löschen</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($items->count() < \App\Enums\CopingType::MAX_ITEMS)
                <form method="POST" action="{{ route('coping.store') }}" class="flex flex-col gap-3" novalidate>
                    @csrf
                    <input type="hidden" name="type" value="{{ $type->value }}">

                    <x-form.input
                        name="content"
                        id="coping-{{ $type->value }}-content"
                        label="Neuer Eintrag"
                        :bag="$type->value"
                        :use-old="false"
                        :value="old('type') === $type->value ? old('content') : null"
                        :placeholder="$type->placeholder()"
                        maxlength="200"
                        required
                    />

                    <button type="submit" class="min-h-12 w-full rounded-2xl bg-sage-900 px-5 text-base font-bold text-white hover:bg-sage-600">
                        Hinzufügen
                    </button>
                </form>
            @endif
        </x-card>
    @endforeach
</x-layouts.page>
