@extends('layouts.app')

@section('title', 'Parfum de Climat')
@section('meta_description', 'Real-time fragrance recommendations powered by your local weather. Wear the right scent for every moment.')

@section('content')

    {{-- Hero Section --}}
    <section class="relative flex min-h-[calc(100vh-6rem)] md:min-h-[calc(100vh-4rem)] items-center justify-center overflow-hidden px-6 py-24">

        {{-- Single permitted accent glow: one static radial gradient, no animation --}}
        <div class="pointer-events-none absolute inset-0 flex items-center justify-center" aria-hidden="true">
            <div class="h-[600px] w-[600px] rounded-full bg-[var(--color-accent)]/8 blur-3xl"></div>
        </div>

        <div class="relative z-10 mx-auto max-w-3xl text-center">

            <p class="section-label mb-6">Weather-Aware Fragrance Intelligence</p>

            <h1 class="font-display text-[clamp(3rem,8vw,5.5rem)] font-light leading-[1.05] tracking-tight text-[var(--ink)] mb-6">
                Wear the<br>
                <span class="text-[var(--color-accent)]">Weather.</span>
            </h1>

            <p class="mx-auto max-w-xl text-lg font-light text-[var(--muted)] leading-relaxed mb-10">
                Parfum de Climat reads your local weather in real time and recommends
                the three fragrances from your collection that are scientifically
                matched to today's temperature, humidity, and season.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}" class="btn-primary">Get Started Free</a>
                <a href="#features" class="btn-ghost">How It Works</a>
            </div>

        </div>
    </section>

    {{-- Features Section --}}
    <section id="features" class="mx-auto max-w-6xl px-6 pb-24">

        <div class="text-center mb-14">
            <p class="section-label mb-3">Core Features</p>
            <h2 class="font-display text-[clamp(1.75rem,4vw,2.75rem)] font-light text-[var(--ink)]">
                Scent meets science.
            </h2>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">

            {{-- Card 1 --}}
            <div class="glass rounded-xl p-8 flex flex-col gap-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg neu-raised">
                    <svg class="h-5 w-5 text-[var(--color-accent)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15a4.5 4.5 0 004.5 4.5H18a3.75 3.75 0 001.332-7.257 3 3 0 00-3.758-3.848 5.25 5.25 0 00-10.233 2.33A4.502 4.502 0 002.25 15z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-display text-xl font-light text-[var(--ink)] mb-2">Live Weather Data</h3>
                    <p class="text-sm font-light text-[var(--muted)] leading-relaxed">
                        GPS-based temperature, humidity, and condition readings from
                        OpenWeatherMap, refreshed every time you ask for a recommendation.
                    </p>
                </div>
            </div>

            {{-- Card 2 --}}
            <div class="glass rounded-xl p-8 flex flex-col gap-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg neu-raised">
                    <svg class="h-5 w-5 text-[var(--color-accent)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-display text-xl font-light text-[var(--ink)] mb-2">Climate Scoring Engine</h3>
                    <p class="text-sm font-light text-[var(--muted)] leading-relaxed">
                        Each fragrance note is profiled with optimal temperature range,
                        humidity preference, and seasonal affinity. A weighted Python
                        algorithm scores your entire collection against today's conditions.
                    </p>
                </div>
            </div>

            {{-- Card 3 --}}
            <div class="glass rounded-xl p-8 flex flex-col gap-4 sm:col-span-2 lg:col-span-1">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg neu-raised">
                    <svg class="h-5 w-5 text-[var(--color-accent)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-display text-xl font-light text-[var(--ink)] mb-2">Your Collection, Ranked</h3>
                    <p class="text-sm font-light text-[var(--muted)] leading-relaxed">
                        Recommendations pull only from fragrances you own. Mark favourites
                        for a subtle score boost, and log which scent you chose building
                        a personal weather-wear history over time.
                    </p>
                </div>
            </div>

        </div>
    </section>

    {{-- How It Works Section --}}
    <section class="border-t border-[var(--hairline)] mx-auto max-w-6xl px-6 py-24">

        <div class="grid gap-16 lg:grid-cols-2 lg:items-center">

            <div>
                <p class="section-label mb-4">Under the Hood</p>
                <h2 class="font-display text-[clamp(1.75rem,4vw,2.5rem)] font-light text-[var(--ink)] mb-6">
                    Intelligent notes,<br>not generic advice.
                </h2>
                <p class="text-base font-light text-[var(--muted)] leading-relaxed mb-8">
                    Most fragrance apps suggest "wear citrus in summer." Parfum de Climat
                    goes further — it profiles every individual note in your collection
                    (Bergamot, Oud, Ambergris…) against real meteorological data and
                    returns only the fragrances whose full note pyramid aligns best with
                    right now.
                </p>

                <ul class="space-y-4">
                    @foreach ([
                        ['Temperature', '50% weight: note optimal ranges vs. current °C'],
                        ['Season',      '35% weight: spring / summer / autumn / winter affinity'],
                        ['Humidity',    '15% weight: low / medium / high / any preference'],
                    ] as [$label, $desc])
                        <li class="flex items-start gap-4">
                            <span class="mt-1.5 inline-block h-1.5 w-1.5 shrink-0 rounded-full bg-[var(--color-accent)]"></span>
                            <div>
                                <span class="text-sm font-medium text-[var(--ink)]">{{ $label }}</span>
                                <span class="text-sm font-light text-[var(--muted)]">  {{ $desc }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="grid grid-cols-2 gap-4">
                @foreach ([
                    ['Laravel 11',       'API + Admin Web Panel'],
                    ['Python Engine',    'Pydantic scoring algorithm'],
                    ['Flutter',          'iOS + Android mobile app'],
                    ['OpenWeatherMap',   'Real-time GPS weather data'],
                    ['Laravel Sanctum',  'Token-based mobile auth'],
                    ['Tailwind CSS v4',  'Design system + dark mode'],
                ] as [$tech, $role])
                    <div class="glass rounded-lg px-4 py-4">
                        <p class="font-display text-base font-light text-[var(--ink)]">{{ $tech }}</p>
                        <p class="text-xs font-light text-[var(--muted)] mt-1">{{ $role }}</p>
                    </div>
                @endforeach
            </div>

        </div>
    </section>

    {{-- CTA Strip Section --}}
    <section class="px-6 pb-24">
        <div class="mx-auto max-w-6xl">
            <div class="glass rounded-2xl px-8 py-12 text-center">
                <p class="section-label mb-4">Portfolio Project</p>
                <h2 class="font-display text-[clamp(1.5rem,3.5vw,2.25rem)] font-light text-[var(--ink)] mb-4">
                    Built to production standard.
                </h2>
                <p class="mx-auto max-w-lg text-base font-light text-[var(--muted)] mb-8">
                    Every layer, schema design, API architecture, Python ML scoring,
                    mobile UI, is built with the same rigour as a production system.
                    No shortcuts.
                </p>
                <a href="{{ route('register') }}" class="btn-primary">
                    Get Started Free
                </a>
            </div>
        </div>
    </section>

@endsection
