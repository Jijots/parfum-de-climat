<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Parfum de Climat') — Wear the Weather.</title>
    <meta name="description" content="@yield('meta_description', 'Real-time fragrance recommendations powered by your local weather.')">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    {{--
        ── FOUC Prevention ────────────────────────────────────────────────────
        This script MUST run before any stylesheet is applied. It reads the
        user's saved theme preference from localStorage and immediately sets
        the `.dark` class on <html> so Tailwind's dark-mode variants take
        effect on first paint. Without this, there is a visible flash from
        light to dark on page load for users who prefer dark mode.
    --}}
    <script>
        (function () {
            try {
                var saved = localStorage.getItem('pdc_theme');
                var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (saved === 'dark' || (!saved && prefersDark)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (e) { /* localStorage blocked (private browsing) — safe to ignore */ }
        })();
    </script>

    {{-- Google Fonts: Cormorant Garamond (display) + Inter (UI) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    {{-- Compiled CSS + JS (Vite) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine.js — CDN, deferred. Must load AFTER app.js registers the store. --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

    @stack('head')
</head>

<body
    class="min-h-screen bg-[var(--bg)] text-[var(--ink)] font-sans antialiased transition-colors duration-150"
    x-data
>
    {{-- Navigation --}}
    <header class="fixed inset-x-0 top-0 z-50 border-b border-[var(--hairline)]" style="background-color:var(--surface-bg);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);">
        <div class="mx-auto flex h-16 max-w-6xl items-center px-6">

            {{-- Left: logo — flex:1 so it balances the right side and keeps the center nav truly centered --}}
            <div style="flex: 1;">
                <a href="{{ route('landing') }}" class="font-display text-2xl font-light tracking-wide text-[var(--ink)] hover:text-[var(--color-accent)] transition-colors duration-150">
                    Parfum <span class="text-[var(--color-accent)]">de</span> Climat
                </a>
            </div>

            {{-- Center: nav links (desktop only) --}}
            <div class="hidden md:flex items-center">
                <nav class="flex items-center gap-6 text-sm">
                    {{-- Public routes: always visible --}}
                    <a href="{{ route('app') }}" class="text-[var(--muted)] hover:text-[var(--ink)] transition-colors {{ request()->routeIs('app') ? 'text-[var(--ink)]' : '' }}">Recommend</a>
                    <a href="{{ route('browse') }}" class="text-[var(--muted)] hover:text-[var(--ink)] transition-colors {{ request()->routeIs('browse*') ? 'text-[var(--ink)]' : '' }}">Browse</a>
                    <a href="{{ route('wardrobe') }}" class="text-[var(--muted)] hover:text-[var(--ink)] transition-colors {{ request()->routeIs('wardrobe') ? 'text-[var(--ink)]' : '' }}">Wardrobe</a>
                    @auth
                    @if (auth()->user()->hasVerifiedEmail())
                    <a href="{{ route('history') }}" class="text-[var(--muted)] hover:text-[var(--ink)] transition-colors {{ request()->routeIs('history') ? 'text-[var(--ink)]' : '' }}">History</a>
                    @endif
                    @endauth
                </nav>
            </div>

            {{-- Right: actions — flex:1 + justify-end balances the logo on the left --}}
            <div style="flex: 1; display: flex; justify-content: flex-end; align-items: center; gap: 0.75rem;">

                {{-- Theme toggle --}}
                <button
                    type="button"
                    class="btn-icon"
                    title="Toggle dark mode"
                    @click="Alpine.store('theme').toggle()"
                    :aria-label="$store.theme.dark ? 'Switch to light mode' : 'Switch to dark mode'"
                >
                    <svg x-show="$store.theme.dark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25M18.364 5.636l-1.591 1.591M21 12h-2.25M18.364 18.364l-1.591-1.591M12 21v-2.25M5.636 18.364l1.591-1.591M3 12h2.25M5.636 5.636l1.591 1.591M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
                    </svg>
                    <svg x-show="!$store.theme.dark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/>
                    </svg>
                </button>

                @auth
                {{-- User menu --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.outside="open = false" class="btn-icon" title="Account">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                        </svg>
                    </button>
                    <div
                        x-show="open"
                        x-transition
                        class="absolute right-0 mt-2 w-48 glass rounded-xl border border-[var(--hairline)] shadow-[0_8px_32px_rgba(0,0,0,0.08)] py-1"
                    >
                        <p class="px-4 py-2 text-xs text-[var(--muted)] border-b border-[var(--hairline)]">{{ auth()->user()->name }}</p>
                        <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-[var(--ink)] hover:text-[var(--color-accent)] transition-colors">
                            Edit Profile
                        </a>
                        @if (! auth()->user()->hasVerifiedEmail())
                            <a href="{{ route('verification.notice') }}" class="block px-4 py-2 text-sm text-[var(--ink)] hover:text-[var(--color-accent)] transition-colors">
                                Verify email
                            </a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-[var(--ink)] hover:text-[var(--error)] transition-colors">
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
                @else
                <a href="{{ route('login') }}" class="text-sm text-[var(--muted)] hover:text-[var(--ink)] transition-colors">Sign in</a>
                <a href="{{ route('register') }}" class="btn-primary text-sm py-1.5 px-4">Get started</a>
                @endauth

            </div>
        </div>

        {{-- Mobile nav --}}
        <div class="md:hidden border-t border-[var(--hairline)] flex overflow-x-auto">
            <a href="{{ route('app') }}" class="flex-1 py-2 text-center text-xs {{ request()->routeIs('app') ? 'text-[var(--ink)]' : 'text-[var(--muted)]' }}">Recommend</a>
            <a href="{{ route('browse') }}" class="flex-1 py-2 text-center text-xs {{ request()->routeIs('browse*') ? 'text-[var(--ink)]' : 'text-[var(--muted)]' }}">Browse</a>
            <a href="{{ route('wardrobe') }}" class="flex-1 py-2 text-center text-xs {{ request()->routeIs('wardrobe') ? 'text-[var(--ink)]' : 'text-[var(--muted)]' }}">Wardrobe</a>
            @auth
            @if (auth()->user()->hasVerifiedEmail())
            <a href="{{ route('history') }}" class="flex-1 py-2 text-center text-xs {{ request()->routeIs('history') ? 'text-[var(--ink)]' : 'text-[var(--muted)]' }}">History</a>
            @endif
            @endauth
        </div>
    </header>

    @auth
        @if (! auth()->user()->hasVerifiedEmail())
            <div class="border-b border-[var(--hairline)] bg-[var(--color-accent)]/8 px-6 py-2 text-center text-xs text-[var(--ink)]">
                Verify your email to unlock saved history and account-level tracking.
                <a href="{{ route('verification.notice') }}" class="ml-1 text-[var(--color-accent)] hover:underline">Review verification</a>
            </div>
        @endif
    @endauth

    {{-- ── Page Content ──────────────────────────────────────────────────── --}}
    {{-- pt-16 offsets the fixed header height --}}
    <main class="pt-16">
        @yield('content')
    </main>

    {{-- ── Footer ────────────────────────────────────────────────────────── --}}
    <footer class="border-t border-[var(--hairline)] mt-24 py-10">
        <div class="mx-auto max-w-6xl px-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="font-display text-base font-light text-[var(--muted)]">
                Parfum <span class="text-[var(--color-accent)]">de</span> Climat
            </p>
            <p class="text-xs text-[var(--muted)]">
                © {{ date('Y') }} — A portfolio project. Fragrance data sourced from public datasets.
            </p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
