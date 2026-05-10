@extends('layouts.app')

@section('title', 'Browse Fragrances')

@section('content')
<div class="mx-auto max-w-6xl px-6 py-12">

    <div class="mb-8">
        <h1 class="font-display text-4xl font-light text-[var(--ink)]">Browse</h1>
        <p class="mt-2 text-sm text-[var(--muted)]">{{ $fragrances->total() }} fragrances in the catalog.</p>
    </div>

    <div
        x-data="{
            results: @js($initialResults),
            total: {{ $fragrances->total() }},
            currentPage: {{ $fragrances->currentPage() }},
            lastPage: {{ $fragrances->lastPage() }},
            search: '{{ request('search', '') }}',
            gender: '{{ request('gender', 'all') }}',
            loading: false,
            wardrobe: @js($collectionIds),

            // AbortController for the in-flight fetch — lets us cancel stale requests
            // immediately rather than waiting for them to resolve and ignoring the result.
            _abort: null,
            // Timer for debouncing search input
            _debounceTimer: null,
            // Timer that delays showing the loading indicator so fast responses
            // (<150 ms) never cause a distracting flash.
            _loadingTimer: null,

            init() {
                this.$watch('search', () => this.debounce());
                this.$watch('gender', () => this.doSearch(1));
            },

            debounce() {
                clearTimeout(this._debounceTimer);
                this._debounceTimer = setTimeout(() => this.doSearch(1), 400);
            },

            async doSearch(page = 1) {
                // Cancel any previous in-flight request immediately
                if (this._abort) {
                    this._abort.abort();
                    this._abort = null;
                }
                clearTimeout(this._loadingTimer);

                const controller  = new AbortController();
                this._abort       = controller;

                // Only show the loading overlay if the request takes longer than 150 ms.
                // This prevents a distracting flash for fast cached responses.
                this._loadingTimer = setTimeout(() => { this.loading = true; }, 150);

                const params = new URLSearchParams({
                    q:      this.search,
                    gender: this.gender,
                    page:   String(page),
                });

                try {
                    const res  = await fetch('{{ route('browse.search') }}?' + params, {
                        signal:  controller.signal,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    const data = await res.json();

                    this.results     = data.fragrances;
                    this.total       = data.total;
                    this.currentPage = data.current_page;
                    this.lastPage    = data.last_page;
                } catch (e) {
                    if (e.name === 'AbortError') return; // Intentionally cancelled — do nothing
                } finally {
                    clearTimeout(this._loadingTimer);
                    this.loading = false;
                    this._abort  = null;
                }
            },

            async goToPage(page) {
                if (this.loading) return;
                if (page < 1 || page > this.lastPage || page === this.currentPage) return;
                await this.doSearch(page);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },

            pages() {
                if (this.lastPage <= 1) return [];

                const windowSize = 5;
                let start = Math.max(1, this.currentPage - Math.floor(windowSize / 2));
                let end = Math.min(this.lastPage, start + windowSize - 1);

                if (end - start + 1 < windowSize) {
                    start = Math.max(1, end - windowSize + 1);
                }

                const pages = [];
                for (let p = start; p <= end; p++) pages.push(p);
                return pages;
            },

            async toggleWardrobe(id) {
                const res = await fetch('{{ url('/wardrobe') }}/' + id + '/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                });
                const data = await res.json();
                const item = this.results.find(f => f.id === id);
                if (item) item.in_wardrobe = data.in_wardrobe;
            }
        }"
    >

        {{-- Search + filters --}}
        <div class="mb-6 flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-[var(--muted)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                </svg>
                <input type="text" x-model="search" placeholder="Search by name or brand…" class="input-field w-full pl-9">
            </div>

            <div class="flex gap-1 glass rounded-xl p-1 self-start">
                <template x-for="[val, label] in [['all','All'],['masculine','Men'],['feminine','Women'],['unisex','Unisex']]" :key="val">
                    <button
                        @click="gender = val"
                        :class="gender === val ? 'bg-[var(--color-accent)] text-white' : 'text-[var(--muted)] hover:text-[var(--ink)]'"
                        class="px-3 py-1.5 rounded-lg text-sm transition-colors"
                        x-text="label"
                    ></button>
                </template>
            </div>
        </div>

        <div class="relative min-h-[28rem]">
            {{-- Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                <template x-for="f in results" :key="f.id">
                    <div class="neu-raised rounded-2xl overflow-hidden group">
                    {{-- Image --}}
                        <a :href="'{{ url('/fragrances') }}/' + f.id" class="block aspect-square bg-[var(--shadow-dark)]/30 overflow-hidden">
                            <img
                                x-show="f.image_url"
                                :src="f.image_url"
                                :alt="f.name"
                                class="w-full h-full object-contain p-4 group-hover:scale-105 transition-transform duration-300"
                                loading="lazy"
                                x-on:error="f.image_url = null"
                            >
                            <div x-show="!f.image_url" class="w-full h-full flex items-center justify-center text-[var(--muted)]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 opacity-25" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                            </div>
                        </a>

                        <div class="p-3">
                            <p class="text-xs text-[var(--muted)] truncate" x-text="f.brand"></p>
                            <a :href="'{{ url('/fragrances') }}/' + f.id" class="block text-sm font-medium text-[var(--ink)] hover:text-[var(--color-accent)] transition-colors truncate mt-0.5" x-text="f.name"></a>

                            <div class="mt-2 flex items-center justify-between">
                                <span
                                    class="text-xs px-2 py-0.5 rounded-full border border-[var(--hairline)] text-[var(--muted)]"
                                    x-text="f.gender === 'masculine' ? 'Men' : f.gender === 'feminine' ? 'Women' : 'Unisex'"
                                ></span>
                                <button
                                    @click="toggleWardrobe(f.id)"
                                    :title="f.in_wardrobe ? 'Remove from wardrobe' : 'Add to wardrobe'"
                                    :class="f.in_wardrobe ? 'text-[var(--color-accent)]' : 'text-[var(--muted)] hover:text-[var(--color-accent)]'"
                                    class="transition-colors"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :fill="f.in_wardrobe ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Empty state --}}
                <div x-show="results.length === 0" class="col-span-full py-12 text-center text-[var(--muted)]">
                    No fragrances found for "<span x-text="search"></span>".
                </div>
            </div>

            {{-- In-place loading layer --}}
            <div
                x-show="loading"
                x-transition.opacity.duration.180ms
                class="absolute inset-0 z-10 flex items-center justify-center rounded-2xl bg-[var(--surface)]/55 backdrop-blur-[1px]"
            >
                <div class="inline-flex items-center gap-2 rounded-full border border-[var(--hairline)] bg-[var(--surface)] px-3 py-1.5 text-xs text-[var(--muted)] shadow-sm">
                    <span class="inline-block h-2 w-2 rounded-full bg-[var(--color-accent)] animate-pulse"></span>
                    Searching…
                </div>
            </div>
        </div>

        {{-- Pagination --}}
        <div x-show="lastPage > 1" class="mt-8 flex items-center justify-center gap-2">
            <button
                @click="goToPage(currentPage - 1)"
                :disabled="loading || currentPage <= 1"
                class="btn-ghost px-3 py-2 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
            >Prev</button>

            <template x-if="pages()[0] > 1">
                <button @click="goToPage(1)" class="btn-ghost px-3 py-2 text-sm">1</button>
            </template>
            <template x-if="pages()[0] > 2">
                <span class="px-1 text-sm text-[var(--muted)]">…</span>
            </template>

            <template x-for="page in pages()" :key="page">
                <button
                    @click="goToPage(page)"
                    :class="page === currentPage ? 'bg-[var(--color-accent)] text-white border-transparent' : ''"
                    class="btn-ghost px-3 py-2 text-sm min-w-10"
                    x-text="page"
                ></button>
            </template>

            <template x-if="pages()[pages().length - 1] < lastPage - 1">
                <span class="px-1 text-sm text-[var(--muted)]">…</span>
            </template>
            <template x-if="pages()[pages().length - 1] < lastPage">
                <button @click="goToPage(lastPage)" class="btn-ghost px-3 py-2 text-sm" x-text="lastPage"></button>
            </template>

            <button
                @click="goToPage(currentPage + 1)"
                :disabled="loading || currentPage >= lastPage"
                class="btn-ghost px-3 py-2 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
            >Next</button>
        </div>

    </div>
</div>
@endsection
