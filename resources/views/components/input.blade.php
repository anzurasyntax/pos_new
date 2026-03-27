@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
])

@php
    $inputName = (string) $name;
    $computedId = preg_replace('/[^A-Za-z0-9_-]/', '_', $inputName);
    $id = $attributes->get('id') ?? $computedId;

    // Laravel validation error keys for array inputs use dot-notation (e.g. variants.0.variant_name),
    // while input HTML uses brackets (e.g. variants[0][variant_name]).
    $errorKey = $inputName;
    $errorKey = str_replace('][', '.', $errorKey);
    $errorKey = str_replace('[', '.', $errorKey);
    $errorKey = str_replace(']', '', $errorKey);

    // For array inputs (e.g. variants[0][name]), Laravel stores old() values under dot keys
    // (e.g. variants.0.name).
    $finalValue = old($errorKey, $value);
    $hasError = $errors->has($errorKey);
    $baseInputClass = 'block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2';
    $extraClass = $attributes->get('class');
    $mergedInputClass = $baseInputClass . ($extraClass ? (' ' . $extraClass) : '');
@endphp

<div class="w-full">
    @if (!empty($label))
        <label for="{{ $id }}" class="block text-sm font-medium text-gray-700">
            {{ $label }}
        </label>
    @endif

    <div @if (!empty($label)) class="mt-2" @endif>
        <input
            id="{{ $id }}"
            name="{{ $inputName }}"
            type="{{ $type }}"
            value="{{ $finalValue }}"
            placeholder="{{ $placeholder }}"
            {{ $attributes->except(['id', 'class']) }}
            class="{{ $mergedInputClass }}"
        />
    </div>

    @if ($hasError)
        <p id="error-{{ $id }}" class="mt-2 text-sm text-red-600">
            {{ $errors->first($errorKey) }}
        </p>
    @else
        <p id="error-{{ $id }}" class="mt-2 text-sm text-red-600 hidden"></p>
    @endif
</div>

