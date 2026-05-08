@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('topbar_actions')
    <a href="{{ route('admin.fragrances.create') }}" class="btn-primary text-sm">
        + New Fragrance
    </a>
@endsection

@section('content')

    {{-- ── Stat Cards ───────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4 mb-8">

        {{-- Total Fragrances --}}
        <div class="stat-card">
            <p class="field-label">Fragrances</p>
            <p class="font-display text-[clamp(2rem,4vw,2.75rem)] font-light text-[var(--ink)] leading-none mt-2">
                {{ number_format($stats['total_fragrances']) }}
            </p>
            <a href="{{ route('admin.fragrances.index') }}" class="mt-3 inline-block text-xs text-[var(--color-accent)] hover:underline">
                View catalogue →
            </a>
        </div>

        {{-- Unmapped Notes — warning state when > 0 --}}
        <div class="stat-card {{ $stats['unmapped_notes'] > 0 ? 'border border-[var(--error)]/30' : '' }}">
            <p class="field-label {{ $stats['unmapped_notes'] > 0 ? 'text-[var(--error)]' : '' }}">
                Unmapped Notes
            </p>
            <p class="font-display text-[clamp(2rem,4vw,2.75rem)] font-light leading-none mt-2
                       {{ $stats['unmapped_notes'] > 0 ? 'text-[var(--error)]' : 'text-[var(--ink)]' }}">
                {{ number_format($stats['unmapped_notes']) }}
            </p>
            @if ($stats['unmapped_notes'] > 0)
                <a href="{{ route('admin.note-profiles.index') }}" class="mt-3 inline-block text-xs text-[var(--error)] hover:underline">
                    Fix now →
                </a>
            @else
                <p class="mt-3 text-xs text-[var(--muted)]">All notes mapped</p>
            @endif
        </div>

        {{-- Note Profiles --}}
        <div class="stat-card">
            <p class="field-label">Note Profiles</p>
            <p class="font-display text-[clamp(2rem,4vw,2.75rem)] font-light text-[var(--ink)] leading-none mt-2">
                {{ number_format($stats['note_profiles']) }}
            </p>
            <a href="{{ route('admin.note-profiles.index') }}" class="mt-3 inline-block text-xs text-[var(--color-accent)] hover:underline">
                Manage profiles →
            </a>
        </div>

        {{-- Total Logs --}}
        <div class="stat-card">
            <p class="field-label">Recommendations</p>
            <p class="font-display text-[clamp(2rem,4vw,2.75rem)] font-light text-[var(--ink)] leading-none mt-2">
                {{ number_format($stats['total_logs']) }}
            </p>
            <a href="{{ route('admin.logs.index') }}" class="mt-3 inline-block text-xs text-[var(--color-accent)] hover:underline">
                View logs →
            </a>
        </div>

    </div>

    {{-- ── Recent Activity ──────────────────────────────────────────────────── --}}
    <div class="glass rounded-xl overflow-hidden">

        <div class="flex items-center justify-between px-5 py-4 border-b border-[var(--hairline)]">
            <h2 class="font-display text-lg font-light text-[var(--ink)]">Recent Recommendations</h2>
            <a href="{{ route('admin.logs.index') }}" class="text-xs text-[var(--color-accent)] hover:underline">
                View all →
            </a>
        </div>

        @if ($recentLogs->isEmpty())
            <div class="px-5 py-12 text-center">
                <p class="text-sm text-[var(--muted)]">No recommendation logs yet.</p>
                <p class="text-xs text-[var(--muted)] mt-1">Logs appear here once users start requesting weather recommendations.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Weather</th>
                            <th>Season</th>
                            <th>Chose</th>
                            <th class="text-right">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentLogs as $log)
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

                                {{-- Weather --}}
                                <td>
                                    <p class="text-sm text-[var(--ink)]">{{ $log->weather_condition }}</p>
                                    @if ($log->temperature_celsius !== null)
                                        <p class="text-xs text-[var(--muted)]">{{ $log->temperature_celsius }}°C</p>
                                    @endif
                                </td>

                                {{-- Season --}}
                                <td>
                                    <span class="badge">{{ $log->season ?? '—' }}</span>
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
                                    <time
                                        class="text-xs text-[var(--muted)]"
                                        datetime="{{ $log->created_at->toIso8601String() }}"
                                        title="{{ $log->created_at->format('d M Y H:i') }}"
                                    >
                                        {{ $log->created_at->diffForHumans() }}
                                    </time>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </div>

@endsection
