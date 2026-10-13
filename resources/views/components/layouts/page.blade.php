@props(['title' => null, 'heading', 'back' => null, 'backLabel' => 'Zurück zum Dashboard'])

{{-- Unterseite mit Zurück-Link, Wortmarke und Überschrift (Profil, Notfallplan, Neustart, Rechtliches) --}}
<x-layouts.app :title="$title">
    <main id="main" class="mx-auto flex min-h-screen max-w-[440px] flex-col gap-5 px-5 pt-5 pb-10">
        <header class="flex flex-col gap-3 pt-1">
            @if ($back)
                <a href="{{ $back }}" class="inline-flex min-h-11 items-center gap-2 self-start text-base font-bold">
                    <span aria-hidden="true">←</span> {{ $backLabel }}
                </a>
            @endif
            <div class="flex flex-col gap-0.5">
                <x-wordmark />
                <h1 class="text-2xl/[1.2] font-bold">{{ $heading }}</h1>
            </div>
        </header>

        <x-status :message="session('status')" />

        {{ $slot }}

        <x-legal-links class="mt-auto" />
    </main>
</x-layouts.app>
