@props([
    'text' => '',
    'type' => 'primary', // primary | secondary | danger
    'htmlType' => 'button',
    'loadingText' => null,
])

@php
    $variant = (string) $type;
    $baseClass = 'inline-flex items-center justify-center rounded-lg font-semibold text-sm transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2';
    $sizeClass = 'px-5 py-2.5';

    $classesByVariant = [
        'primary' => 'bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm hover:shadow active:scale-[0.98]',
        'secondary' => 'bg-white text-slate-800 hover:bg-slate-50 border border-slate-200 shadow-sm',
        'danger' => 'bg-rose-600 text-white hover:bg-rose-700 shadow-sm active:scale-[0.98]',
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

