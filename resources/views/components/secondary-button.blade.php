<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-white border border-slate-200 rounded-lg font-semibold text-sm text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:ring-offset-2 disabled:opacity-25 transition']) }}>
    {{ $slot }}
</button>
