@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-lg bg-emerald-50 border border-emerald-200/80 px-3 py-2 font-medium text-sm text-emerald-800']) }}>
        {{ $status }}
    </div>
@endif
