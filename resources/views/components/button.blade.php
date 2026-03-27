@props([
    'text' => '',
    'type' => 'primary', // primary | secondary | danger
    'htmlType' => 'button',
    'loadingText' => null,
])

@php
    $variant = (string) $type;
    $baseClass = 'inline-flex items-center justify-center rounded-md font-medium transition-colors';
    $sizeClass = 'px-5 py-3';

    $classesByVariant = [
        'primary' => 'bg-indigo-600 text-white hover:bg-indigo-700',
        'secondary' => 'bg-gray-100 text-gray-800 hover:bg-gray-200 border border-gray-200',
        'danger' => 'bg-red-600 text-white hover:bg-red-700',
    ];

    $classes = $classesByVariant[$variant] ?? $classesByVariant['primary'];
    $merged = $baseClass.' '.$sizeClass.' '.$classes.' disabled:opacity-60 disabled:cursor-not-allowed';
    $extraClass = $attributes->get('class');
    $merged .= $extraClass ? (' ' . $extraClass) : '';

    $loadingAttr = $loadingText ? ['data-loading-text' => $loadingText] : [];
@endphp

<button
    type="{{ $htmlType }}"
    {{ $attributes->except(['class', 'data-loading-text']) }}
    @foreach($loadingAttr as $k => $v) {{ $k }}="{{ $v }}" @endforeach
    class="{{ $merged }}"
>
    @if($slot->isNotEmpty())
        {{ $slot }}
        @if (!empty($text))
            <span class="ms-2">{{ $text }}</span>
        @endif
    @else
        {{ $text }}
    @endif
</button>

