@props([
    'name',
    'label',
    'options' => [],
    'hint' => null,
    'value' => null,
    'bag' => 'default',
])

{{-- Auswahlfeld im Stil von <x-form.input>; $options als [Wert => Beschriftung] --}}
@php
    $id = $attributes->get('id', $name);
    $hintId = $hint ? $id.'-hint' : null;
    $errorId = $id.'-error';
    $fieldErrors = $errors->getBag($bag);
    $hasError = $fieldErrors->has($name);
    $describedBy = trim(($hintId ?? '').' '.($hasError ? $errorId : ''));
    $currentValue = (string) old($name, $value);
@endphp

<div class="flex flex-col gap-1.5">
    <label for="{{ $id }}" class="text-base font-bold">{{ $label }}</label>

    @if ($hint)
        <p id="{{ $hintId }}" class="text-sm text-ink-muted">{{ $hint }}</p>
    @endif

    <select
        id="{{ $id }}"
        name="{{ $name }}"
        @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
        @if ($hasError) aria-invalid="true" @endif
        {{ $attributes->except('id')->class([
            'min-h-12 w-full rounded-2xl border-2 bg-surface px-4 text-base text-ink',
            'border-sos' => $hasError,
            'border-line-soft focus:border-sage-600' => ! $hasError,
        ]) }}
    >
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($currentValue === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>

    @if ($hasError)
        <p id="{{ $errorId }}" class="text-sm font-bold text-sos">{{ $fieldErrors->first($name) }}</p>
    @endif
</div>
