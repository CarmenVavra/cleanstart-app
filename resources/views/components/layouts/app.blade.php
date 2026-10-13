@props(['title' => null])

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#23463D">
    {{-- Sensible Gesundheitsdaten: nicht indexieren --}}
    <meta name="robots" content="noindex, nofollow">

    <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-page font-sans text-ink">
    <a href="#main"
       class="sr-only rounded-xl bg-white px-4 py-3 font-bold focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[60]">
        Zum Inhalt springen
    </a>

    {{ $slot }}

    @livewireScriptConfig
</body>
</html>
