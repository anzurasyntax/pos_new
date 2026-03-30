@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 rounded-lg shadow-sm w-full px-3 py-2.5 text-slate-900 placeholder:text-slate-400 transition-shadow']) }}>
