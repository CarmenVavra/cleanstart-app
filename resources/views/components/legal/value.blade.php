@props(['value' => null, 'env'])

{{-- Zeigt eine Pflichtangabe aus config/legal.php oder einen deutlich sichtbaren Platzhalter --}}
@if (filled($value))
    {{ $value }}
@else
    <mark class="rounded bg-[#FDECEA] px-1 font-bold text-sos">[{{ $env }} in .env ergänzen]</mark>
@endif
