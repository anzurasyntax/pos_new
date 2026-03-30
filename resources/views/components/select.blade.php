@props([
    'label' => null,
    'name' => null,
    'placeholder' => null,
])

@php
    $selectName = (string) $name;
    $computedId = preg_replace('/[^A-Za-z0-9_-]/', '_', $selectName);
    $id = $attributes->get('id') ?? $computedId;

    $errorKey = $selectName;
    $errorKey = str_replace('][', '.', $errorKey);
    $errorKey = str_replace('[', '.', $errorKey);
    $errorKey = str_replace(']', '', $errorKey);

    $hasError = $errors->has($errorKey);
    $baseSelectClass = 'block w-full rounded-lg border-slate-200 bg-white shadow-sm px-3 py-2.5 text-slate-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-shadow';
    $extraClass = $attributes->get('class');
    $mergedSelectClass = $baseSelectClass . ($extraClass ? (' ' . $extraClass) : '');
@endphp

<div class="w-full">
    @if (!empty($label))
        <label for="{{ $id }}" class="block text-sm font-semibold text-slate-700">
            {{ $label }}
        </label>
    @endif

    <div @if (!empty($label)) class="mt-2" @endif>
        <select
            id="{{ $id }}"
            name="{{ $selectName }}"
            {{ $attributes->except(['id', 'class']) }}
            class="{{ $mergedSelectClass }}"
        >
            @if (!empty($placeholder))
                <option value="">{{ $placeholder }}</option>
            @endif

            {{ $slot }}
        </select>
    </div>

    @if ($hasError)
        <p id="error-{{ $id }}" class="mt-2 text-sm text-rose-600">
            {{ $errors->first($errorKey) }}
        </p>
    @else
        <p id="error-{{ $id }}" class="mt-2 text-sm text-rose-600 hidden"></p>
    @endif
</div>

