@extends('layouts.admin')

@section('title', 'Weather Logs')
@section('page_title', 'Weather Recommendation Logs')

@section('content')

    {{-- ── Filters ──────────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('admin.logs.index') }}" class="mb-6 flex flex-col sm:flex-row gap-3">

        {{-- User search --}}
        <div class="flex-1">
            <label for="user" class="sr-only">Search by user</label>
            <input id="user" type="search" name="user" value="{{ request('user') }}"
                   placeholder="Search by user name or email…" class="input-field w-full">
        </div>

        {{-- Season --}}
        <div class="sm:w-36">
            <label for="season" class="sr-only">Season</label>
            <select id="season" name="season" class="input-field w-full">
                <option value="">All Seasons</option>
                @foreach (['spring', 'summer', 'autumn', 'winter'] as $s)
                    <option value="{{ $s }}" {{ request('season') === $s ? 'selected' : '' }}>
                        {{ ucfirst($s) }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Condition --}}
        <div class="sm:w-40">
            <label for="condition" class="sr-only">Weather condition</label>
            <input id="condition" type="search" name="condition" value="{{ request('condition') }}"
                   placeholder="Condition…" class="input-field w-full">
        </div>

        <button type="submit" class="btn-primary shrink-0">Filter</button>

        @if (request()->hasAny(['user', 'season', 'condition']))
            <a href="{{ route('admin.logs.index') }}" class="btn-ghost shrink-0">Clear</a>
        @endif

    </form>

    {{-- ── Table ────────────────────────────────────────────────────────────── --}}
    <div class="glass rounded-xl overflow-hidden">

        @if ($logs->isEmpty())
            <div class="px-6 py-16 text-center">
                <p class="text-sm text-[var(--muted)]">No recommendation logs match your filters.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Weather</th>
                            <th>Season</th>
                            <th>Top Recommendation</th>
                            <th>Chose</th>
                            <th class="text-right">Time</th>
                            <th class="w-8"></th>{{-- expand toggle --}}
                        </tr>
                    </thead>
                    {{--
                        x-data on <tbody> holds a shared `expanded` object keyed by
                        log ID. This lets each primary row's toggle button and its
                        sibling detail row reference the same reactive state, which
                        is not possible if x-data is placed on individual <tr>s
                        (each <tr> would be an independent Alpine component scope).
                    --}}
                    <tbody x-data="{ expanded: {} }">
                        @foreach ($logs as $log)
                            <tr>

                                {{-- User --}}
                                <td>
                                    @if ($log->user)
                                        <p class="text-sm font-medium text-[var(--ink)]">{{ $log->user->name }}</p>
                                        <p class="text-xs text-[var(--muted)]">{{ $log->user->email }}</p>
                                    @else
                                        <span class="text-xs text-[var(--muted)]">Deleted user</span>
                                    @endif
                                </td>

                                {{-- Weather condition --}}
                                <td>
                                    <p class="text-sm text-[var(--ink)]">{{ $log->weather_condition }}</p>
                                    <p class="text-xs text-[var(--muted)]">
                                        @if ($log->temperature_celsius !== null)
                                            {{ $log->temperature_celsius }}°C
                                        @endif
                                        @if ($log->humidity_percent !== null)
                                            · {{ $log->humidity_percent }}% RH
                                        @endif
                                    </p>
                                </td>

                                {{-- Season --}}
                                <td>
                                    <span class="badge">{{ $log->season ?? '—' }}</span>
                                </td>

                                {{-- Top recommendation from engine_response --}}
                                <td>
                                    @php
                                        $topRec = data_get($log->engine_response, 'recommendations.0');
                                    @endphp
                                    @if ($topRec)
                                        <p class="text-sm text-[var(--ink)]">{{ data_get($topRec, 'name', '—') }}</p>
                                        <p class="text-xs text-[var(--muted)]">
                                            Score: {{ number_format(data_get($topRec, 'score', 0), 2) }}
                                        </p>
                                    @else
                                        <span class="text-xs text-[var(--muted)]">—</span>
                                    @endif
                                </td>

                                {{-- Chosen fragrance --}}
                                <td>
                                    @if ($log->chosenFragrance)
                                        <p class="text-sm text-[var(--ink)]">{{ $log->chosenFragrance->name }}</p>
                                        <p class="text-xs text-[var(--muted)]">{{ $log->chosenFragrance->brand }}</p>
                                    @else
                                        <span class="text-xs text-[var(--muted)]">Not logged</span>
                                    @endif
                                </td>

                                {{-- Time --}}
                                <td class="text-right">
                                    <time class="text-xs text-[var(--muted)]"
                                          datetime="{{ $log->created_at->toIso8601String() }}"
                                          title="{{ $log->created_at->format('d M Y H:i') }}">
                                        {{ $log->created_at->diffForHumans() }}
                                    </time>
                                </td>

                                {{-- Expand toggle (only if there's a payload) --}}
                                <td class="text-right">
                                    @if ($log->engine_response)
                                        <button
                                            type="button"
                                            class="btn-icon"
                                            @click="expanded[{{ $log->id }}] = !expanded[{{ $log->id }}]"
                                            :aria-expanded="(!!expanded[{{ $log->id }}]).toString()"
                                            aria-label="Toggle engine response"
                                        >
                                            <svg class="h-4 w-4 transition-transform duration-150"
                                                 :class="{ 'rotate-180': expanded[{{ $log->id }}] }"
                                                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                    @endif
                                </td>

                            </tr>

                            {{-- Expanded detail row: full engine response payload --}}
                            @if ($log->engine_response)
                                <tr x-show="expanded[{{ $log->id }}]" class="bg-[var(--bg)]">
                                    <td colspan="7" class="px-5 py-4">
                                        <p class="section-label mb-2">Engine Response Payload</p>
                                        <pre class="text-xs text-[var(--muted)] overflow-x-auto whitespace-pre-wrap break-words
                                                    rounded-lg p-4 neu-inset font-mono leading-relaxed">{{ json_encode($log->engine_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </td>
                                </tr>
                            @endif

                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($logs->hasPages())
                <div class="px-5 py-4 border-t border-[var(--hairline)]">
                    {{ $logs->links() }}
                </div>
            @endif

        @endif

    </div>

    <p class="mt-3 text-xs text-[var(--muted)]">
        Showing {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }}
        of {{ $logs->total() }} logs — read-only audit view
    </p>

@endsection

