<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-rose-600 border border-transparent rounded-lg font-semibold text-sm text-white shadow-sm hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 active:scale-[0.98] transition']) }}>
    {{ $slot }}
</button>
