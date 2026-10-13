@props(['variant' => 'primary'])

<button {{ $attributes->merge(['type' => 'submit'])->class([
    'min-h-14 w-full rounded-2xl px-5 text-lg font-bold transition-colors duration-150',
    'bg-sage-900 text-white hover:bg-sage-600' => $variant === 'primary',
    'border-2 border-line bg-white text-ink hover:bg-surface' => $variant === 'secondary',
]) }}>
    {{ $slot }}
</button>
