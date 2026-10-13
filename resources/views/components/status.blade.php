@props(['message' => null])

@if (filled($message))
    <p role="status" {{ $attributes->class('rounded-[18px] bg-sage-100 p-4 text-base font-bold text-ink-soft') }}>
        {{ $message }}
    </p>
@endif
