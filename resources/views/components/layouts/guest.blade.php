@props(['title' => null])

<x-layouts.app :title="$title">
    <main id="main" class="mx-auto flex min-h-screen max-w-[440px] flex-col justify-center gap-5 px-5 pt-5 pb-10">
        <x-wordmark />
        {{ $slot }}
    </main>
</x-layouts.app>
