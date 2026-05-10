@extends('layouts.app')

@section('title', 'My Wardrobe')

@section('content')
<div class="mx-auto max-w-6xl px-6 py-12">

    @if ($isGuestWardrobe ?? false)
        <div class="mb-6 glass rounded-2xl p-4 text-sm text-[var(--muted)]">
            This is your temporary wardrobe for this browser session. It will be merged into your account if you sign in or register before the session ends.
        </div>
    @endif

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="font-display text-4xl font-light text-[var(--ink)]">My Wardrobe</h1>
            <p class="mt-2 text-sm text-[var(--muted)]">{{ $collection->total() }} {{ $collection->total() === 1 ? 'fragrance' : 'fragrances' }} in your collection.</p>
        </div>
        <a href="{{ route('browse') }}" class="btn-ghost text-sm">+ Add more</a>
    </div>

    @if ($collection->isEmpty())
        <div class="neu-raised rounded-2xl p-12 text-center">
            <p class="font-display text-2xl font-light text-[var(--ink)] mb-3">Your wardrobe is empty</p>
            <p class="text-sm text-[var(--muted)] mb-6">Browse the catalog and add fragrances you own.</p>
            <a href="{{ route('browse') }}" class="btn-primary inline-flex">Browse Fragrances</a>
        </div>
    @else
        {{-- Favorites strip --}}
        @if ($favorites->isNotEmpty())
            <div class="mb-8">
                <p class="text-xs text-[var(--muted)] uppercase tracking-widest mb-4">Favorites</p>
                <div class="flex gap-4 overflow-x-auto pb-2">
                    @foreach ($favorites as $item)
                        <a href="{{ route('fragrance.show', $item->fragrance_id) }}" class="flex-none w-28 text-center group">
                            <div class="w-28 h-28 glass rounded-xl overflow-hidden mb-2 mx-auto">
                                @if ($item->fragrance->getImageUrl())
                                    <img src="{{ $item->fragrance->getImageUrl() }}" alt="{{ $item->fragrance->name }}" class="w-full h-full object-contain p-2 group-hover:scale-105 transition-transform duration-300" loading="lazy">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-[var(--muted)] opacity-25">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                                    </div>
                                @endif
                            </div>
                            <p class="text-xs text-[var(--ink)] font-medium truncate">{{ $item->fragrance->name }}</p>
                            <p class="text-xs text-[var(--muted)] truncate">{{ $item->fragrance->brand }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Full collection grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach ($collection as $item)
                <div
                    class="neu-raised rounded-2xl overflow-hidden"
                    x-data="{ favorite: {{ $item->is_favorite ? 'true' : 'false' }}, removing: false }"
                >
                    <a href="{{ route('fragrance.show', $item->fragrance_id) }}" class="block aspect-square bg-[var(--shadow-dark)]/30 overflow-hidden group">
                        @if ($item->fragrance->getImageUrl())
                            <img src="{{ $item->fragrance->getImageUrl() }}" alt="{{ $item->fragrance->name }}" class="w-full h-full object-contain p-4 group-hover:scale-105 transition-transform duration-300" loading="lazy">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-[var(--muted)] opacity-25">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                            </div>
                        @endif
                    </a>

                    <div class="p-3">
                        <p class="text-xs text-[var(--muted)] truncate">{{ $item->fragrance->brand }}</p>
                        <a href="{{ route('fragrance.show', $item->fragrance_id) }}" class="block text-sm font-medium text-[var(--ink)] hover:text-[var(--color-accent)] transition-colors truncate mt-0.5">{{ $item->fragrance->name }}</a>

                        <div class="mt-2 flex items-center justify-between">
                            {{-- Favorite toggle --}}
                            <button
                                @click="
                                    const res = await fetch('{{ route('wardrobe.favorite', $item->fragrance_id) }}', {
                                        method: 'PATCH',
                                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Content-Type': 'application/json' }
                                    });
                                    const data = await res.json();
                                    favorite = data.is_favorite;
                                "
                                :class="favorite ? 'text-[var(--color-accent)]' : 'text-[var(--muted)] hover:text-[var(--color-accent)]'"
                                class="transition-colors"
                                title="Toggle favorite"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :fill="favorite ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                                </svg>
                            </button>

                            {{-- Remove --}}
                            <button
                                @click="
                                    removing = true;
                                    await fetch('{{ route('wardrobe.toggle', $item->fragrance_id) }}', {
                                        method: 'POST',
                                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Content-Type': 'application/json' }
                                    });
                                    $el.closest('.neu-raised').remove();
                                "
                                class="text-xs text-[var(--muted)] hover:text-[var(--error)] transition-colors"
                                title="Remove from wardrobe"
                            >Remove</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($collection->hasPages())
            <div class="mt-8">
                {{ $collection->links() }}
            </div>
        @endif
    @endif

</div>
@endsection
