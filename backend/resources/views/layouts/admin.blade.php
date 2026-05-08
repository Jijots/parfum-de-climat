<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin') — Parfum de Climat</title>

    {{--
        ── FOUC Prevention ────────────────────────────────────────────────────
        Inline script — must execute synchronously before any stylesheet loads.
        Sets .dark on <html> from localStorage before first paint.
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
            } catch (e) {}
        })();
    </script>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

    @stack('head')
</head>

{{--
    Root wrapper uses Alpine x-data to manage the mobile sidebar open/close
    state. The entire layout is a child of this Alpine scope.
--}}
<body
    class="min-h-screen bg-[var(--bg)] text-[var(--ink)] font-sans antialiased transition-colors duration-150"
    x-data="{ sidebarOpen: false }"
    @keydown.escape="sidebarOpen = false"
>

    {{-- ════════════════════════════════════════════════════════════════════
         MOBILE OVERLAY — dims the page when off-canvas sidebar is open
         ════════════════════════════════════════════════════════════════════ --}}
    <div
        class="fixed inset-0 z-20 bg-black/40 backdrop-blur-sm lg:hidden"
        x-show="sidebarOpen"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false"
        aria-hidden="true"
    ></div>

    {{-- ════════════════════════════════════════════════════════════════════
         SIDEBAR
         Desktop: fixed left column, always visible.
         Mobile:  off-canvas (translate-x transforms), toggled by sidebarOpen.
         ════════════════════════════════════════════════════════════════════ --}}
    <aside
        id="admin-sidebar"
        class="fixed inset-y-0 left-0 z-30 flex w-64 flex-col glass border-r border-[var(--hairline)]
               -translate-x-full lg:translate-x-0 transition-transform duration-150 ease-out"
        :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen && window.innerWidth < 1024 }"
        aria-label="Admin navigation"
    >
        {{-- ── Logo area ──────────────────────────────────────────────── --}}
        <div class="flex h-14 shrink-0 items-center border-b border-[var(--hairline)] px-5">
            <a href="{{ route('admin.dashboard') }}" class="font-display text-lg font-light tracking-wide text-[var(--ink)] hover:text-[var(--color-accent)] transition-colors duration-150">
                Parfum <span class="text-[var(--color-accent)]">de</span> Climat
            </a>
        </div>

        {{-- ── Nav links ──────────────────────────────────────────────── --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">

            {{-- Section: Catalogue --}}
            <p class="section-label px-2 mb-2">Catalogue</p>

            <a href="{{ route('admin.dashboard') }}"
               class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                {{-- Dashboard icon --}}
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('admin.fragrances.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.fragrances.*') ? 'active' : '' }}">
                {{-- Beaker icon (fragrances) --}}
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15M14.25 3.104c.251.023.501.05.75.082M19.8 15a2.25 2.25 0 01.45 1.328v.05A2.25 2.25 0 0118 18.75h-12a2.25 2.25 0 01-2.25-2.25v-.05a2.25 2.25 0 01.45-1.328L5 14.5m14.8.5l.007-.007M5 14.5l-.007-.007"/>
                </svg>
                <span>Fragrances</span>
            </a>

            <a href="{{ route('admin.note-profiles.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.note-profiles.*') ? 'active' : '' }}">
                {{-- Tag icon (note profiles) --}}
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.169.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/>
                </svg>
                <span>Note Profiles</span>
            </a>

            {{-- Section: Analytics --}}
            <p class="section-label px-2 mt-5 mb-2">Analytics</p>

            <a href="{{ route('admin.logs.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.logs.*') ? 'active' : '' }}">
                {{-- Chart bar icon --}}
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                </svg>
                <span>Weather Logs</span>
            </a>
        </nav>

        {{-- ── User + Logout ───────────────────────────────────────────── --}}
        <div class="shrink-0 border-t border-[var(--hairline)] p-3">
            <div class="flex items-center gap-3 px-2 py-2 mb-1">
                {{-- Avatar initials --}}
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[var(--color-accent)] text-xs font-semibold text-white">
                    {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                </span>
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-[var(--ink)]">{{ Auth::user()->name ?? 'Admin' }}</p>
                    <p class="truncate text-xs text-[var(--muted)]">{{ Auth::user()->email ?? '' }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="sidebar-link w-full text-[var(--error)]">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                    </svg>
                    <span>Sign Out</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- ════════════════════════════════════════════════════════════════════
         MAIN COLUMN — offset by sidebar width on large screens
         ════════════════════════════════════════════════════════════════════ --}}
    <div class="lg:pl-64 flex flex-col min-h-screen">

        {{-- ── Sticky Topbar ───────────────────────────────────────────── --}}
        <header class="sticky top-0 z-10 flex h-14 shrink-0 items-center gap-4 glass border-b border-[var(--hairline)] px-4 lg:px-6">

            {{-- Mobile: hamburger button --}}
            <button
                type="button"
                class="btn-icon lg:hidden"
                @click="sidebarOpen = !sidebarOpen"
                :aria-expanded="sidebarOpen.toString()"
                aria-controls="admin-sidebar"
                aria-label="Toggle navigation"
            >
                <svg x-show="!sidebarOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                </svg>
                <svg x-show="sidebarOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            {{-- Page title (injected from child views via @section) --}}
            <div class="flex-1 min-w-0">
                <h1 class="font-display text-xl font-light text-[var(--ink)] truncate">
                    @yield('page_title', 'Admin')
                </h1>
            </div>

            {{-- Topbar actions slot (e.g. "New Fragrance" button) --}}
            @yield('topbar_actions')

            {{-- Theme toggle --}}
            <button
                type="button"
                class="btn-icon shrink-0"
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
        </header>

        {{-- ── Flash Toast ──────────────────────────────────────────────── --}}
        {{--
            Auto-dismisses after 4 seconds. Uses Alpine x-init to schedule the
            removal. Appears below the sticky topbar so it does not cover content.
        --}}
        @if (session()->has('success') || session()->has('error'))
            <div
                class="mx-4 lg:mx-6 mt-4"
                x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 4000)"
                x-show="show"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-1"
                role="alert"
                aria-live="polite"
            >
                @if (session('success'))
                    <div class="flex items-start gap-3 rounded-lg border border-green-500/30 bg-green-500/10 px-4 py-3 text-sm text-green-700 dark:text-green-400">
                        {{-- Checkmark icon --}}
                        <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                        </svg>
                        <p>{{ session('success') }}</p>
                        <button type="button" class="ml-auto shrink-0 opacity-60 hover:opacity-100 transition-opacity" @click="show = false" aria-label="Dismiss">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="flex items-start gap-3 rounded-lg border border-[var(--error)]/30 bg-[var(--error)]/10 px-4 py-3 text-sm text-[var(--error)]">
                        <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                        <p>{{ session('error') }}</p>
                        <button type="button" class="ml-auto shrink-0 opacity-60 hover:opacity-100 transition-opacity" @click="show = false" aria-label="Dismiss">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                @endif
            </div>
        @endif

        {{-- ── Page Content ────────────────────────────────────────────── --}}
        <main class="flex-1 px-4 lg:px-6 py-6">
            @yield('content')
        </main>

    </div>{{-- /main column --}}

    @stack('scripts')
</body>
</html>
