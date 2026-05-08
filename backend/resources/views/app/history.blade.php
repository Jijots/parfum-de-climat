@extends('layouts.app')

@section('title', 'History')

@section('content')
<div class="mx-auto max-w-2xl px-6 py-12">

    <div class="mb-8">
        <h1 class="font-display text-4xl font-light text-[var(--ink)]">History</h1>
        <p class="mt-2 text-sm text-[var(--muted)]">Your past fragrance recommendations.</p>
    </div>

    @if ($logs->isEmpty())
        <div class="neu-raised rounded-2xl p-12 text-center">
            <p class="font-display text-2xl font-light text-[var(--ink)] mb-3">No history yet</p>
            <p class="text-sm text-[var(--muted)] mb-6">Your recommendation sessions will appear here.</p>
            <a href="{{ route('app') }}" class="btn-primary inline-flex">Get a recommendation</a>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($logs as $log)
                <div class="neu-raised rounded-2xl overflow-hidden" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full text-left p-5 flex items-center justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <p class="text-sm font-medium text-[var(--ink)] truncate">{{ $log->location_name ?? 'Unknown location' }}</p>
                                @if ($log->chosenFragrance)
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-[var(--color-accent)]/10 text-[var(--color-accent)] border border-[var(--color-accent)]/20 shrink-0">Wore: {{ $log->chosenFragrance->name }}</span>
                                @endif
                            </div>
                            <p class="text-xs text-[var(--muted)]">
                                {{ $log->created_at->format('M j, Y · g:i A') }} · {{ $log->temperature_celsius }}°C · {{ $log->weather_condition }}
                            </p>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[var(--muted)] shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                        </svg>
                    </button>

                    <div x-show="open" x-transition class="border-t border-[var(--hairline)] px-5 py-4">
                        <p class="text-xs text-[var(--muted)] uppercase tracking-widest mb-3">Recommendations</p>
                        @php $recs = $log->engine_response_payload ?? []; @endphp
                        @if (count($recs))
                            <div class="space-y-2">
                                @foreach (array_slice($recs, 0, 3) as $i => $rec)
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-[var(--muted)] w-4">{{ $i + 1 }}</span>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between mb-0.5">
                                                <p class="text-sm text-[var(--ink)]">{{ $rec['name'] ?? '—' }}</p>
                                                <p class="text-xs text-[var(--muted)]">{{ isset($rec['score']) ? round($rec['score'] * 100) . '%' : '' }}</p>
                                            </div>
                                            <div class="h-1 w-full rounded-full bg-[var(--shadow-dark)] overflow-hidden">
                                                @if (isset($rec['score']))
                                                    <div class="h-full rounded-full {{ $log->chosen_fragrance_id && isset($rec['fragrance_id']) && $log->chosen_fragrance_id === $rec['fragrance_id'] ? 'bg-[var(--color-accent)]' : 'bg-[var(--muted)]/30' }}" style="width: {{ round($rec['score'] * 100) }}%"></div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-[var(--muted)]">No data available.</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">{{ $logs->links() }}</div>
    @endif

</div>
@endsection
