<footer {{ $attributes->class('flex flex-wrap items-center justify-center gap-x-2 text-sm text-ink-muted') }}>
    <a href="{{ route('legal.imprint') }}" class="inline-flex min-h-11 items-center px-1">Impressum</a>
    <span aria-hidden="true">·</span>
    <a href="{{ route('legal.privacy') }}" class="inline-flex min-h-11 items-center px-1">Datenschutz</a>
</footer>
