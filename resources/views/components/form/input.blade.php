@props([
    'name',
    'label',
    'type' => 'text',
    'hint' => null,
    'value' => null,
])

@php
    $id = $attributes->get('id', $name);
    $hintId = $hint ? $id.'-hint' : null;
    $errorId = $id.'-error';
    $describedBy = trim(($hintId ?? '').' '.($errors->has($name) ? $errorId : ''));
    $currentValue = $type === 'password' ? null : old($name, $value);
@endphp

<div class="flex flex-col gap-1.5">
    <label for="{{ $id }}" class="text-base font-bold">{{ $label }}</label>

    @if ($hint)
        <p id="{{ $hintId }}" class="text-sm text-ink-muted">{{ $hint }}</p>
    @endif

    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if ($currentValue !== null) value="{{ $currentValue }}" @endif
        @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
        @error($name) aria-invalid="true" @enderror
        {{ $attributes->except('id')->class([
            'min-h-12 w-full rounded-2xl border-2 bg-surface px-4 text-base text-ink',
            'border-sos' => $errors->has($name),
            'border-line-soft focus:border-sage-600' => ! $errors->has($name),
        ]) }}
    >

    @error($name)
        <p id="{{ $errorId }}" class="text-sm font-bold text-sos">{{ $message }}</p>
    @enderror
</div>
