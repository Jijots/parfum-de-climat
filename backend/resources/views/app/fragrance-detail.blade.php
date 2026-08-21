@extends('layouts.app')

@section('title', $item->name . ' by ' . $item->brand)

@section('content')
<div class="mx-auto max-w-2xl px-6 py-12">

    {{-- Back link --}}
    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('browse') }}" class="inline-flex items-center gap-1.5 text-sm text-[var(--muted)] hover:text-[var(--ink)] transition-colors mb-8">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        Back
    </a>

    {{-- Hero --}}
    <div class="glass rounded-2xl p-8 mb-8 flex flex-col sm:flex-row gap-6 items-center sm:items-start">
        <div class="w-32 h-32 shrink-0 bg-[var(--shadow-dark)]/20 rounded-xl overflow-hidden">
            @if ($item->getImageUrl())
                <img src="{{ $item->getImageUrl() }}" alt="{{ $item->name }}" class="w-full h-full object-contain p-2">
            @else
                <div class="w-full h-full flex items-center justify-center text-[var(--muted)] opacity-25">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                </div>
            @endif
        </div>
        <div class="text-center sm:text-left flex-1">
            <p class="text-sm text-[var(--muted)]">{{ $item->brand }}</p>
            <h1 class="font-display text-3xl font-light text-[var(--ink)] mt-1">{{ $item->name }}</h1>
            <div class="flex flex-wrap gap-2 mt-3 justify-center sm:justify-start">
                @if ($item->release_year)<span class="text-xs px-2 py-0.5 rounded-full border border-[var(--hairline)] text-[var(--muted)]">{{ $item->release_year }}</span>@endif
                @if ($item->concentration)<span class="text-xs px-2 py-0.5 rounded-full border border-[var(--hairline)] text-[var(--muted)]">{{ $item->concentration }}</span>@endif
                <span class="text-xs px-2 py-0.5 rounded-full border border-[var(--hairline)] text-[var(--muted)]">
                    {{ $item->gender_target === 'masculine' ? 'Men' : ($item->gender_target === 'feminine' ? 'Women' : 'Unisex') }}
                </span>
            </div>

            {{-- Wardrobe actions --}}
            <div class="mt-4 flex gap-2 justify-center sm:justify-start"
                x-data="{ inWardrobe: {{ $inWardrobe ? 'true' : 'false' }}, isFavorite: {{ $isFavorite ? 'true' : 'false' }}, loading: false }"
            >
                <button
                    @click="
                        loading = true;
                        const res = await fetch('{{ route('wardrobe.toggle', $item->id) }}', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Content-Type': 'application/json' }
                        });
                        const data = await res.json();
                        inWardrobe = data.in_wardrobe;
                        if (!inWardrobe) isFavorite = false;
                        loading = false;
                    "
                    :disabled="loading"
                    :class="inWardrobe ? 'btn-ghost' : 'btn-primary'"
                    class="text-sm py-1.5 px-4 rounded-lg"
                >
                    <span x-text="inWardrobe ? 'In wardrobe ✓' : 'Add to wardrobe'"></span>
                </button>

                <button
                    x-show="inWardrobe"
                    @click="
                        const res = await fetch('{{ route('wardrobe.favorite', $item->id) }}', {
                            method: 'PATCH',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Content-Type': 'application/json' }
                        });
                        const data = await res.json();
                        isFavorite = data.is_favorite;
                    "
                    :class="isFavorite ? 'text-[var(--color-accent)]' : 'text-[var(--muted)] hover:text-[var(--color-accent)]'"
                    class="p-2 transition-colors rounded-lg border border-[var(--hairline)]"
                    title="Toggle favorite"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :fill="isFavorite ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Description --}}
    @if ($item->description)
    <div class="mb-8">
        <p class="text-sm text-[var(--muted)] leading-relaxed">{{ Str::limit($item->description, 300) }}</p>
    </div>
    @endif

    {{-- Performance --}}
    @if ($item->rating || $item->sillage || $item->longevity)
    <div class="neu-raised rounded-2xl p-6 mb-6">
        <p class="text-xs text-[var(--muted)] uppercase tracking-widest mb-4">Performance</p>
        @if ($item->rating)
        <div class="mb-3">
            <div class="flex justify-between text-sm mb-1.5">
                <span class="text-[var(--muted)]">Rating</span>
                <span class="text-[var(--ink)]">{{ number_format($item->rating, 1) }} / 5</span>
            </div>
            <div class="h-1.5 w-full rounded-full bg-[var(--shadow-dark)] overflow-hidden">
                <div class="h-full rounded-full bg-[var(--color-accent)]" style="width: {{ ($item->rating / 5) * 100 }}%"></div>
            </div>
        </div>
        @endif
        @if ($item->longevity)
        <div class="mb-3">
            <div class="flex justify-between text-sm mb-1.5">
                <span class="text-[var(--muted)]">Longevity</span>
                <span class="text-[var(--ink)]">{{ number_format($item->longevity, 1) }} / 10</span>
            </div>
            <div class="h-1.5 w-full rounded-full bg-[var(--shadow-dark)] overflow-hidden">
                <div class="h-full rounded-full bg-[var(--color-accent)]" style="width: {{ ($item->longevity / 10) * 100 }}%"></div>
            </div>
        </div>
        @endif
        @if ($item->sillage)
        <div>
            <div class="flex justify-between text-sm mb-1.5">
                <span class="text-[var(--muted)]">Sillage</span>
                <span class="text-[var(--ink)]">{{ number_format($item->sillage, 1) }} / 10</span>
            </div>
            <div class="h-1.5 w-full rounded-full bg-[var(--shadow-dark)] overflow-hidden">
                <div class="h-full rounded-full bg-[var(--color-accent)]" style="width: {{ ($item->sillage / 10) * 100 }}%"></div>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- Notes pyramid --}}
    @php
        // Derived from the already-loaded notes relation rather than the
        // topNotes/heartNotes/baseNotes relations. Those are filtered subsets of
        // the same table, so using them here cost three extra queries per page —
        // and would lazy-load straight past the cache on a cached $item.
        $byLayer = $item->notes->groupBy('layer');
        $top     = $byLayer->get('top',   collect())->pluck('raw_note_name');
        $heart   = $byLayer->get('heart', collect())->pluck('raw_note_name');
        $base    = $byLayer->get('base',  collect())->pluck('raw_note_name');
    @endphp
    @if ($top->isNotEmpty() || $heart->isNotEmpty() || $base->isNotEmpty())
    <div class="neu-raised rounded-2xl p-6 mb-6">
        <p class="text-xs text-[var(--muted)] uppercase tracking-widest mb-4">Notes</p>
        @foreach ([['Top', $top], ['Heart', $heart], ['Base', $base]] as [$layer, $notes])
            @if ($notes->isNotEmpty())
            <div class="mb-4 last:mb-0">
                <p class="text-xs text-[var(--muted)] mb-2">{{ $layer }}</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach ($notes as $note)
                        <span class="text-xs px-2.5 py-1 rounded-full border border-[var(--hairline)] text-[var(--ink)]">{{ $note }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        @endforeach
    </div>
    @endif

    {{-- Accords --}}
    @if ($item->accords->isNotEmpty())
    <div class="neu-raised rounded-2xl p-6 mb-6">
        <p class="text-xs text-[var(--muted)] uppercase tracking-widest mb-4">Accords</p>
        <div class="space-y-2">
            @foreach ($item->accords->sortByDesc('strength')->take(8) as $accord)
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-[var(--ink)]">{{ $accord->accord }}</span>
                    <span class="text-[var(--muted)]">{{ round($accord->strength * 100) }}%</span>
                </div>
                <div class="h-1.5 w-full rounded-full bg-[var(--shadow-dark)] overflow-hidden">
                    <div class="h-full rounded-full bg-[var(--color-accent)]/60" style="width: {{ round($accord->strength * 100) }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- External link --}}
    @if ($item->external_source_url)
    <a href="{{ $item->external_source_url }}" target="_blank" rel="noopener noreferrer" class="btn-ghost w-full justify-center text-sm mt-2">
        View on Fragrantica ↗
    </a>
    @endif

    {{-- Smells like this --------------------------------------------------
         Pre-computed neighbours from the TF-IDF + cosine similarity index.
         See FragranceController::similarFragrances(). --}}
    @if ($similar->isNotEmpty())
    <div class="mt-10">
        <div class="flex items-baseline justify-between mb-4">
            <h2 class="font-display text-2xl font-light text-[var(--ink)]">Smells like this</h2>
            <span class="text-xs text-[var(--muted)]">matched on shared notes</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach ($similar as $s)
            <a href="{{ url('/fragrances/' . $s['id']) }}" class="neu-raised rounded-2xl overflow-hidden group block">
                <div class="relative aspect-square bg-[var(--shadow-dark)]/30 overflow-hidden">
                    @if ($s['image_url'])
                        <img
                            src="{{ $s['image_url'] }}"
                            alt="{{ $s['name'] }}"
                            class="w-full h-full object-contain p-4 group-hover:scale-105 transition-transform duration-300"
                            loading="lazy"
                        >
                    @else
                        <div class="w-full h-full flex items-center justify-center text-[var(--muted)]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 opacity-25" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/></svg>
                        </div>
                    @endif

                    <span class="absolute top-2 right-2 text-[10px] font-medium px-2 py-0.5 rounded-full bg-[var(--color-accent)] text-white shadow">
                        {{ $s['match'] }}%
                    </span>

                    @if ($s['in_wardrobe'])
                        <span class="absolute top-2 left-2 text-[10px] px-2 py-0.5 rounded-full bg-[var(--ink)]/70 text-white">
                            In wardrobe
                        </span>
                    @endif
                </div>

                <div class="p-3">
                    <p class="text-xs text-[var(--muted)] truncate">{{ $s['brand'] }}</p>
                    <p class="text-sm font-medium text-[var(--ink)] group-hover:text-[var(--color-accent)] transition-colors truncate mt-0.5">{{ $s['name'] }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
