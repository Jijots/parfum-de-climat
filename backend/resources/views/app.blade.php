<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    {{--
        ── FOUC Prevention ────────────────────────────────────────────────────
        Must run before any stylesheet is applied, so the theme is correct on
        first paint rather than flashing light then dark.

        This is duplicated from layouts/app.blade.php on purpose. Both shells
        are live during the migration and a user moving between a ported page
        and an unported one must not see their theme reset — so both read the
        same 'pdc_theme' key. Change one, change the other.
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

    {{-- Inertia injects <title> via the title callback in app.jsx --}}
    {{-- No Ziggy: URLs are passed to pages as explicit props, which keeps one
         fewer dependency and makes each page's inputs visible in the controller. --}}
    @viteReactRefresh
    @vite('resources/js/app.jsx')
    @inertiaHead
</head>
<body class="antialiased">
    @inertia
</body>
</html>
