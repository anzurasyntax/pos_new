<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Tailwind + Alpine (no Vite manifest required) -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100" x-data="{ sidebarOpen: false }">
            @php
                $role = Auth::user()?->role ?? 'sales_user';
            @endphp
            <div class="flex">
                <!-- Sidebar (Desktop) -->
                <aside class="hidden md:flex md:flex-col md:w-64 md:bg-white md:border-r md:min-h-screen">
                    <div class="p-4 border-b">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                            <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                            <span class="font-semibold text-gray-800">Shop System</span>
                        </a>
                        <div class="mt-2 text-sm text-gray-600">
                            Signed in as <span class="font-medium">{{ Auth::user()->name }}</span>
                        </div>
                        <div class="text-xs text-gray-500">Role: {{ Auth::user()?->role ?? 'sales_user' }}</div>
                    </div>

                    <nav class="flex-1 p-2 space-y-1">
                        <a href="{{ route('dashboard') }}"
                           class="{{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700 border-indigo-500' : 'text-gray-700 hover:bg-gray-50 border-transparent' }} flex items-center gap-3 w-full px-3 py-2 rounded-md border-l-4">
                            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M10.707 1.293a1 1 0 00-1.414 0l-8 8A1 1 0 001.999 10h1v8a1 1 0 001 1h5v-6h2v6h5a1 1 0 001-1v-8h1a1 1 0 00.707-1.707l-8-8z"/>
                            </svg>
                            Dashboard
                        </a>

                        @if (in_array($role, ['manager', 'super_admin'], true))
                            <a href="{{ route('products.index') }}"
                               class="{{ request()->routeIs('products.*') ? 'bg-indigo-50 text-indigo-700 border-indigo-500' : 'text-gray-700 hover:bg-gray-50 border-transparent' }} flex items-center gap-3 w-full px-3 py-2 rounded-md border-l-4">
                                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 2a2 2 0 00-2 2v1H5a2 2 0 00-2 2v9a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-3V4a2 2 0 00-2-2zm0 3V4a0 0 0 010-0v1zm-1 5a1 1 0 112 0v1a1 1 0 11-2 0V10z" clip-rule="evenodd"/>
                                </svg>
                                Products
                            </a>
                        @endif

                        <a href="{{ route('sales.index') }}"
                           class="{{ request()->routeIs('sales.index') ? 'bg-indigo-50 text-indigo-700 border-indigo-500' : 'text-gray-700 hover:bg-gray-50 border-transparent' }} flex items-center gap-3 w-full px-3 py-2 rounded-md border-l-4">
                            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M3 3a1 1 0 011-1h2a1 1 0 011 1v1H4v12h12v-2h1a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V3z"/>
                                <path d="M6 8a1 1 0 011-1h8a1 1 0 011 1v7a1 1 0 01-1 1H7a1 1 0 01-1-1V8z"/>
                            </svg>
                            Sales
                        </a>

                        @if (in_array($role, ['manager', 'super_admin'], true))
                            <a href="{{ route('purchase.index') }}"
                               class="{{ request()->routeIs('purchase.index') ? 'bg-indigo-50 text-indigo-700 border-indigo-500' : 'text-gray-700 hover:bg-gray-50 border-transparent' }} flex items-center gap-3 w-full px-3 py-2 rounded-md border-l-4">
                                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M4 3a1 1 0 011-1h10a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V3z"/>
                                    <path d="M6 8l2-2 2 2 2-2 2 2v7H6V8z" fill="#ffffff" opacity=".7"/>
                                </svg>
                                Purchases
                            </a>
                        @endif

                        @if (in_array($role, ['manager', 'super_admin'], true))
                            <a href="{{ route('payments.index') }}"
                               class="{{ request()->routeIs('payments.index') ? 'bg-indigo-50 text-indigo-700 border-indigo-500' : 'text-gray-700 hover:bg-gray-50 border-transparent' }} flex items-center gap-3 w-full px-3 py-2 rounded-md border-l-4">
                                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M3 3a2 2 0 012-2h10a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V3zM6 7a1 1 0 100 2h8a1 1 0 100-2H6zm0 4a1 1 0 100 2h5a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                </svg>
                                Payments
                            </a>
                        @endif

                        @if (in_array($role, ['manager', 'super_admin'], true))
                            <a href="{{ route('expenses.index') }}"
                               class="{{ request()->routeIs('expenses.index') ? 'bg-indigo-50 text-indigo-700 border-indigo-500' : 'text-gray-700 hover:bg-gray-50 border-transparent' }} flex items-center gap-3 w-full px-3 py-2 rounded-md border-l-4">
                                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M2 10a8 8 0 1116 0 8 8 0 01-16 0zM4 10a6 6 0 1012 0 6 6 0 00-12 0z"/>
                                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3.382l1.447 1.447a1 1 0 01-1.414 1.414l-1.75-1.75A1 1 0 019 9.828V6a1 1 0 011-1z" clip-rule="evenodd"/>
                                </svg>
                                Expenses
                            </a>
                        @endif

                        <a href="{{ route('customers.index') }}"
                           class="{{ request()->routeIs('customers.*') ? 'bg-indigo-50 text-indigo-700 border-indigo-500' : 'text-gray-700 hover:bg-gray-50 border-transparent' }} flex items-center gap-3 w-full px-3 py-2 rounded-md border-l-4">
                            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 10a4 4 0 100-8 4 4 0 000 8zm-7 8a7 7 0 0114 0H3z" clip-rule="evenodd"/>
                            </svg>
                            Customers
                        </a>

                        <a href="{{ route('reports.index') }}"
                           class="{{ request()->routeIs('reports.*') ? 'bg-indigo-50 text-indigo-700 border-indigo-500' : 'text-gray-700 hover:bg-gray-50 border-transparent' }} flex items-center gap-3 w-full px-3 py-2 rounded-md border-l-4">
                            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M3 17a1 1 0 01-1-1V4a1 1 0 011-1h14a1 1 0 011 1v12a1 1 0 01-1 1H3z"/>
                                <path d="M6 14l2-2 2 2 4-5 2 3v2H6z" fill="#ffffff" opacity=".7"/>
                            </svg>
                            Reports
                        </a>
                    </nav>

                    <div class="p-4 border-t">
                        <div class="flex items-center justify-between gap-3">
                            <a href="{{ route('profile.edit') }}" class="text-sm text-gray-700 hover:text-gray-900">
                                Profile
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </aside>

                <!-- Main Area -->
                <div class="flex-1 flex flex-col min-w-0">
                    <!-- Top navbar (Mobile) -->
                    <header class="md:hidden bg-white border-b">
                        <div class="flex items-center justify-between px-4 py-3">
                            <button
                                type="button"
                                @click="sidebarOpen = !sidebarOpen"
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-600 hover:bg-gray-100"
                                aria-label="Toggle menu"
                            >
                                <svg class="w-6 h-6" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </button>

                            <div class="text-sm">
                                <div class="font-medium text-gray-800">{{ Auth::user()->name }}</div>
                                <div class="text-gray-500 text-xs">Role: {{ Auth::user()?->role ?? 'sales_user' }}</div>
                            </div>

                            <a href="{{ route('profile.edit') }}" class="text-sm text-indigo-700 font-medium">
                                Profile
                            </a>
                        </div>
                    </header>

                    <!-- Sidebar (Mobile Dropdown) -->
                    <div class="md:hidden" x-show="sidebarOpen" @click.away="sidebarOpen = false">
                        <div class="bg-white border-b p-3 space-y-2">
                            <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-md bg-gray-50 text-gray-800">
                                Dashboard
                            </a>
                            @if (in_array($role, ['manager', 'super_admin'], true))
                                <a href="{{ route('products.index') }}" class="block px-3 py-2 rounded-md hover:bg-gray-50 text-gray-800">
                                    Products
                                </a>
                            @endif
                            <a href="{{ route('sales.index') }}" class="block px-3 py-2 rounded-md hover:bg-gray-50 text-gray-800">
                                Sales
                            </a>
                            @if (in_array($role, ['manager', 'super_admin'], true))
                                <a href="{{ route('purchase.index') }}" class="block px-3 py-2 rounded-md hover:bg-gray-50 text-gray-800">
                                    Purchases
                                </a>
                            @endif

                            @if (in_array($role, ['manager', 'super_admin'], true))
                                <a href="{{ route('payments.index') }}" class="block px-3 py-2 rounded-md hover:bg-gray-50 text-gray-800">
                                    Payments
                                </a>
                            @endif

                            @if (in_array($role, ['manager', 'super_admin'], true))
                                <a href="{{ route('expenses.index') }}" class="block px-3 py-2 rounded-md hover:bg-gray-50 text-gray-800">
                                    Expenses
                                </a>
                            @endif
                            <a href="{{ route('customers.index') }}" class="block px-3 py-2 rounded-md hover:bg-gray-50 text-gray-800">
                                Customers
                            </a>
                            <a href="{{ route('reports.index') }}" class="block px-3 py-2 rounded-md hover:bg-gray-50 text-gray-800">
                                Reports
                            </a>

                            <div class="pt-2 border-t">
                                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                                    @csrf
                                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-3 rounded-md bg-red-600 text-white font-medium hover:bg-red-700">
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Top navbar (Desktop) -->
                    <header class="hidden md:flex bg-white border-b">
                        <div class="w-full px-4 py-3 flex items-center justify-between">
                            <div class="text-sm text-gray-600">Quick menu</div>
                            <div class="flex items-center gap-3">
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-800">{{ Auth::user()->name }}</div>
                                    <div class="text-xs text-gray-500">Role: {{ Auth::user()?->role ?? 'sales_user' }}</div>
                                </div>
                                <a href="{{ route('profile.edit') }}" class="hidden lg:inline-flex items-center justify-center px-3 py-2 rounded-md border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Profile
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="hidden lg:inline-flex items-center justify-center px-3 py-2 rounded-md bg-red-600 text-white text-sm font-medium hover:bg-red-700">
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </header>

                    <!-- Page Heading -->
                    @isset($header)
                        <header class="bg-white shadow-sm">
                            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </header>
                    @endisset

                    <!-- Page Content -->
                    <main class="flex-1">
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
