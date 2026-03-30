<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700|figtree:400,500,600&display=swap" rel="stylesheet" />
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['"DM Sans"', 'Figtree', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        },
                    },
                },
            };
        </script>
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="font-sans text-slate-800 antialiased min-h-screen">
        <div class="min-h-screen flex flex-col sm:justify-center items-center px-4 py-10 sm:pt-0 bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-950 relative overflow-hidden">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%2310b981\' fill-opacity=\'0.06\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-80 pointer-events-none" aria-hidden="true"></div>
            <div class="absolute -top-24 -right-24 h-72 w-72 rounded-full bg-emerald-500/20 blur-3xl pointer-events-none" aria-hidden="true"></div>
            <div class="absolute -bottom-32 -left-16 h-80 w-80 rounded-full bg-teal-600/15 blur-3xl pointer-events-none" aria-hidden="true"></div>

            <div class="relative mb-8 sm:mb-10 text-center">
                <a href="/" class="inline-flex flex-col items-center gap-3 group">
                    <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-white/20 shadow-lg backdrop-blur-sm group-hover:bg-white/15 transition-colors">
                        <x-application-logo class="w-9 h-9 fill-current text-emerald-400" />
                    </span>
                    <span class="text-lg font-semibold text-white tracking-tight">{{ config('app.name', 'Shop System') }}</span>
                    <span class="text-sm text-slate-400">Sign in to continue</span>
                </a>
            </div>

            <div class="relative w-full sm:max-w-md">
                <div class="rounded-2xl bg-white/95 backdrop-blur-xl shadow-2xl shadow-slate-900/40 ring-1 ring-white/20 px-6 py-8 sm:px-8">
                    {{ $slot }}
                </div>
                <p class="mt-6 text-center text-xs text-slate-500">
                    Secure workspace · {{ config('app.name') }}
                </p>
            </div>
        </div>
    </body>
</html>
