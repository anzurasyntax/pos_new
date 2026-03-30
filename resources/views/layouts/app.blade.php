<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700|figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Tailwind + Alpine (no Vite manifest required) -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['"DM Sans"', 'Figtree', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        },
                        boxShadow: {
                            'soft': '0 2px 15px -3px rgba(15, 23, 42, 0.08), 0 10px 20px -5px rgba(15, 23, 42, 0.06)',
                        },
                    },
                },
            };
        </script>
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="font-sans antialiased text-slate-800 bg-slate-100/90">
        <div class="min-h-screen" x-data="{ sidebarOpen: false }">
            @php
                $role = Auth::user()?->role ?? 'sales_user';
            @endphp
            <div class="flex min-h-screen">
                <!-- Sidebar (Desktop) -->
                <aside class="hidden md:flex md:flex-col md:w-64 md:min-h-screen md:bg-gradient-to-b md:from-slate-900 md:to-slate-950 md:border-r md:border-slate-800/80 md:shadow-soft">
                    <div class="p-5 border-b border-slate-700/60">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/20 ring-1 ring-emerald-400/30">
                                <x-application-logo class="block h-6 w-auto fill-current text-emerald-400" />
                            </span>
                            <div>
                                <span class="font-semibold text-white tracking-tight block leading-tight">Shop System</span>
                                <span class="text-[11px] uppercase tracking-wider text-slate-500 font-medium">POS</span>
                            </div>
                        </a>
                        <div class="mt-4 rounded-lg bg-slate-800/50 px-3 py-2.5 ring-1 ring-slate-700/50">
                            <div class="text-xs text-slate-400">Signed in</div>
                            <div class="text-sm font-medium text-slate-100 truncate">{{ Auth::user()->name }}</div>
                            <div class="mt-1 inline-flex items-center rounded-full bg-slate-700/80 px-2 py-0.5 text-[10px] font-medium uppercase tracking-wide text-emerald-300/90">
                                {{ str_replace('_', ' ', Auth::user()?->role ?? 'sales_user') }}
                            </div>
                        </div>
                    </div>

                    <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto">
                        <a href="{{ route('dashboard') }}"
                           class="{{ request()->routeIs('dashboard') ? 'bg-emerald-500/15 text-white shadow-sm ring-1 ring-emerald-500/25' : 'text-slate-300 hover:bg-white/5 hover:text-white' }} flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M10.707 1.293a1 1 0 00-1.414 0l-8 8A1 1 0 001.999 10h1v8a1 1 0 001 1h5v-6h2v6h5a1 1 0 001-1v-8h1a1 1 0 00.707-1.707l-8-8z"/>
                            </svg>
                            Dashboard
                        </a>

                        @if (in_array($role, ['manager', 'super_admin'], true))
                            <a href="{{ route('products.index') }}"
                               class="{{ request()->routeIs('products.*') ? 'bg-emerald-500/15 text-white shadow-sm ring-1 ring-emerald-500/25' : 'text-slate-300 hover:bg-white/5 hover:text-white' }} flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 2a2 2 0 00-2 2v1H5a2 2 0 00-2 2v9a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-3V4a2 2 0 00-2-2zm0 3V4a0 0 0 010-0v1zm-1 5a1 1 0 112 0v1a1 1 0 11-2 0V10z" clip-rule="evenodd"/>
                                </svg>
                                Products
                            </a>
                        @endif

                        <a href="{{ route('sales.index') }}"
                           class="{{ request()->routeIs('sales.*') ? 'bg-emerald-500/15 text-white shadow-sm ring-1 ring-emerald-500/25' : 'text-slate-300 hover:bg-white/5 hover:text-white' }} flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M3 3a1 1 0 011-1h2a1 1 0 011 1v1H4v12h12v-2h1a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V3z"/>
                                <path d="M6 8a1 1 0 011-1h8a1 1 0 011 1v7a1 1 0 01-1 1H7a1 1 0 01-1-1V8z"/>
                            </svg>
                            Sales
                        </a>

                        @if (in_array($role, ['manager', 'super_admin'], true))
                            <a href="{{ route('purchase.index') }}"
                               class="{{ request()->routeIs('purchase.*') ? 'bg-emerald-500/15 text-white shadow-sm ring-1 ring-emerald-500/25' : 'text-slate-300 hover:bg-white/5 hover:text-white' }} flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M4 3a1 1 0 011-1h10a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V3z"/>
                                    <path d="M6 8l2-2 2 2 2-2 2 2v7H6V8z" fill="#ffffff" opacity=".7"/>
                                </svg>
                                Purchases
                            </a>
                        @endif

                        @if (in_array($role, ['manager', 'super_admin'], true))
                            <a href="{{ route('payments.index') }}"
                               class="{{ request()->routeIs('payments.index') ? 'bg-emerald-500/15 text-white shadow-sm ring-1 ring-emerald-500/25' : 'text-slate-300 hover:bg-white/5 hover:text-white' }} flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M3 3a2 2 0 012-2h10a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V3zM6 7a1 1 0 100 2h8a1 1 0 100-2H6zm0 4a1 1 0 100 2h5a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                </svg>
                                Payments
                            </a>
                        @endif

                        @if (in_array($role, ['manager', 'super_admin'], true))
                            <a href="{{ route('expenses.index') }}"
                               class="{{ request()->routeIs('expenses.index') ? 'bg-emerald-500/15 text-white shadow-sm ring-1 ring-emerald-500/25' : 'text-slate-300 hover:bg-white/5 hover:text-white' }} flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M2 10a8 8 0 1116 0 8 8 0 01-16 0zM4 10a6 6 0 1012 0 6 6 0 00-12 0z"/>
                                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3.382l1.447 1.447a1 1 0 01-1.414 1.414l-1.75-1.75A1 1 0 019 9.828V6a1 1 0 011-1z" clip-rule="evenodd"/>
                                </svg>
                                Expenses
                            </a>
                        @endif

                        <a href="{{ route('customers.index') }}"
                           class="{{ request()->routeIs('customers.*') ? 'bg-emerald-500/15 text-white shadow-sm ring-1 ring-emerald-500/25' : 'text-slate-300 hover:bg-white/5 hover:text-white' }} flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 10a4 4 0 100-8 4 4 0 000 8zm-7 8a7 7 0 0114 0H3z" clip-rule="evenodd"/>
                            </svg>
                            Customers
                        </a>

                        <a href="{{ route('reports.index') }}"
                           class="{{ request()->routeIs('reports.*') ? 'bg-emerald-500/15 text-white shadow-sm ring-1 ring-emerald-500/25' : 'text-slate-300 hover:bg-white/5 hover:text-white' }} flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M3 17a1 1 0 01-1-1V4a1 1 0 011-1h14a1 1 0 011 1v12a1 1 0 01-1 1H3z"/>
                                <path d="M6 14l2-2 2 2 4-5 2 3v2H6z" fill="#ffffff" opacity=".7"/>
                            </svg>
                            Reports
                        </a>
                    </nav>

                    <div class="p-4 border-t border-slate-700/60">
                        <div class="flex items-center justify-between gap-3">
                            <a href="{{ route('profile.edit') }}" class="text-sm text-slate-400 hover:text-white transition-colors">
                                Profile
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-sm font-medium text-rose-400 hover:text-rose-300 transition-colors">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </aside>

                <!-- Main Area -->
                <div class="flex-1 flex flex-col min-w-0 bg-gradient-to-br from-slate-50 via-white to-emerald-50/30">
                    <!-- Top navbar (Mobile) -->
                    <header class="md:hidden sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-200/80 shadow-sm">
                        <div class="flex items-center justify-between px-4 py-3">
                            <button
                                type="button"
                                @click="sidebarOpen = !sidebarOpen"
                                class="inline-flex items-center justify-center rounded-lg p-2.5 text-slate-600 hover:bg-slate-100 active:scale-95 transition"
                                aria-label="Toggle menu"
                            >
                                <svg class="w-6 h-6" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </button>

                            <div class="text-sm text-right min-w-0 flex-1 mx-2">
                                <div class="font-semibold text-slate-800 truncate">{{ Auth::user()->name }}</div>
                                <div class="text-slate-500 text-xs truncate">{{ str_replace('_', ' ', Auth::user()?->role ?? 'sales_user') }}</div>
                            </div>

                            <a href="{{ route('profile.edit') }}" class="text-sm text-emerald-700 font-semibold hover:text-emerald-800 shrink-0">
                                Profile
                            </a>
                        </div>
                    </header>

                    <!-- Sidebar (Mobile Dropdown) -->
                    <div class="md:hidden z-30" x-show="sidebarOpen" x-transition.opacity.duration.200ms @click.away="sidebarOpen = false">
                        <div class="bg-white border-b border-slate-200 p-3 space-y-1 shadow-soft mx-2 mb-2 rounded-b-xl">
                            <a href="{{ route('dashboard') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-emerald-50 text-emerald-900 ring-1 ring-emerald-200/80' : 'text-slate-700 hover:bg-slate-50' }}">
                                Dashboard
                            </a>
                            @if (in_array($role, ['manager', 'super_admin'], true))
                                <a href="{{ route('products.index') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('products.*') ? 'bg-emerald-50 text-emerald-900 ring-1 ring-emerald-200/80' : 'text-slate-700 hover:bg-slate-50' }}">
                                    Products
                                </a>
                            @endif
                            <a href="{{ route('sales.index') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('sales.*') ? 'bg-emerald-50 text-emerald-900 ring-1 ring-emerald-200/80' : 'text-slate-700 hover:bg-slate-50' }}">
                                Sales
                            </a>
                            @if (in_array($role, ['manager', 'super_admin'], true))
                                <a href="{{ route('purchase.index') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('purchase.*') ? 'bg-emerald-50 text-emerald-900 ring-1 ring-emerald-200/80' : 'text-slate-700 hover:bg-slate-50' }}">
                                    Purchases
                                </a>
                            @endif

                            @if (in_array($role, ['manager', 'super_admin'], true))
                                <a href="{{ route('payments.index') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('payments.index') ? 'bg-emerald-50 text-emerald-900 ring-1 ring-emerald-200/80' : 'text-slate-700 hover:bg-slate-50' }}">
                                    Payments
                                </a>
                            @endif

                            @if (in_array($role, ['manager', 'super_admin'], true))
                                <a href="{{ route('expenses.index') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('expenses.index') ? 'bg-emerald-50 text-emerald-900 ring-1 ring-emerald-200/80' : 'text-slate-700 hover:bg-slate-50' }}">
                                    Expenses
                                </a>
                            @endif
                            <a href="{{ route('customers.index') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('customers.*') ? 'bg-emerald-50 text-emerald-900 ring-1 ring-emerald-200/80' : 'text-slate-700 hover:bg-slate-50' }}">
                                Customers
                            </a>
                            <a href="{{ route('reports.index') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('reports.*') ? 'bg-emerald-50 text-emerald-900 ring-1 ring-emerald-200/80' : 'text-slate-700 hover:bg-slate-50' }}">
                                Reports
                            </a>

                            <div class="pt-2 border-t border-slate-100">
                                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                                    @csrf
                                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-3 rounded-lg bg-rose-600 text-white text-sm font-semibold hover:bg-rose-700 shadow-sm active:scale-[0.99] transition">
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Top navbar (Desktop) -->
                    <header class="hidden md:flex sticky top-0 z-30 bg-white/85 backdrop-blur-md border-b border-slate-200/80">
                        <div class="w-full px-6 py-3.5 flex items-center justify-between gap-4">
                            <div class="text-sm text-slate-500">
                                <span class="font-medium text-slate-700">{{ config('app.name', 'Shop') }}</span>
                                <span class="text-slate-400 mx-2" aria-hidden="true">·</span>
                                <span>Workspace</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="text-right hidden sm:block">
                                    <div class="text-sm font-semibold text-slate-800">{{ Auth::user()->name }}</div>
                                    <div class="text-xs text-slate-500">{{ str_replace('_', ' ', Auth::user()?->role ?? 'sales_user') }}</div>
                                </div>
                                <a href="{{ route('profile.edit') }}" class="hidden lg:inline-flex items-center justify-center px-4 py-2 rounded-lg border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition shadow-sm">
                                    Profile
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="hidden lg:inline-flex items-center justify-center px-4 py-2 rounded-lg bg-rose-600 text-white text-sm font-semibold hover:bg-rose-700 shadow-sm transition">
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </header>

                    <!-- Page Heading -->
                    @isset($header)
                        <header class="bg-white/60 border-b border-slate-200/60 backdrop-blur-sm">
                            <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </header>
                    @endisset

                    <!-- Page Content -->
                    <main class="flex-1 pb-8">
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('submit', function (e) {
                const form = e.target;
                if (!(form instanceof HTMLFormElement)) return;

                const submitButtons = form.querySelectorAll('button[type="submit"][data-loading-text]');
                submitButtons.forEach((btn) => {
                    const loadingText = btn.getAttribute('data-loading-text');
                    if (!loadingText) return;

                    if (!btn.dataset.originalHtml) {
                        btn.dataset.originalHtml = btn.innerHTML;
                    }

                    btn.disabled = true;
                    btn.innerHTML = `
                        <span class="inline-flex items-center gap-2">
                            <span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white/60 border-t-white"></span>
                            ${loadingText}
                        </span>
                    `;
                });
            });
        </script>
    </body>
</html>
