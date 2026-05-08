@extends('layouts.admin')

@section('title', 'Note Profiles')
@section('page_title', 'Note Climate Profiles')

@section('topbar_actions')
    <a href="{{ route('admin.note-profiles.create') }}" class="btn-primary text-sm">
        + New Profile
    </a>
@endsection

@section('content')

    {{-- ── Unmapped notes health alert ─────────────────────────────────────── --}}
    @if ($unmappedCount > 0)
        <div class="mb-6 flex items-start gap-3 rounded-lg border border-[var(--error)]/30 bg-[var(--error)]/8 px-4 py-3">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-[var(--error)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
            </svg>
            <p class="text-sm text-[var(--error)]">
                <strong>{{ number_format($unmappedCount) }} fragrance note{{ $unmappedCount !== 1 ? 's' : '' }}</strong>
                {{ $unmappedCount === 1 ? 'has' : 'have' }} no climate profile assigned.
                The recommendation engine cannot score fragrances whose notes are all unmapped.
                Assign a profile or create a new one, then re-link the notes via the Fragrance edit screen.
            </p>
        </div>
    @endif

    {{-- ── Filters ──────────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('admin.note-profiles.index') }}" class="mb-6 flex flex-col sm:flex-row gap-3">

        <div class="flex-1">
            <label for="search" class="sr-only">Search by name</label>
            <input id="search" type="search" name="search" value="{{ request('search') }}"
                   placeholder="Search profile name…" class="input-field w-full">
        </div>

        <div class="sm:w-44">
            <label for="family" class="sr-only">Note Family</label>
            <select id="family" name="family" class="input-field w-full">
                <option value="">All Families</option>
                @foreach ($families as $family)
                    <option value="{{ $family }}" {{ request('family') === $family ? 'selected' : '' }}>
                        {{ $family }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn-primary shrink-0">Filter</button>

        @if (request()->hasAny(['search', 'family']))
            <a href="{{ route('admin.note-profiles.index') }}" class="btn-ghost shrink-0">Clear</a>
        @endif

    </form>

    {{-- ── Table ────────────────────────────────────────────────────────────── --}}
    <div class="glass rounded-xl overflow-hidden">

        @if ($profiles->isEmpty())
            <div class="px-6 py-16 text-center">
                <p class="text-sm text-[var(--muted)] mb-2">No note profiles found.</p>
                <a href="{{ route('admin.note-profiles.create') }}" class="btn-primary text-sm">
                    Create your first profile
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Note Name</th>
                            <th>Family</th>
                            <th>Temp Range</th>
                            <th>Humidity</th>
                            <th>Seasons</th>
                            <th>Weight</th>
                            <th>Linked Notes</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($profiles as $profile)
                            <tr>
                                {{-- Name --}}
                                <td class="font-medium text-[var(--ink)] text-sm">
                                    {{ $profile->name }}
                                </td>

                                {{-- Family --}}
                                <td>
                                    @if ($profile->note_family)
                                        <span class="badge">{{ $profile->note_family }}</span>
                                    @else
                                        <span class="text-xs text-[var(--muted)]">—</span>
                                    @endif
                                </td>

                                {{-- Temp range --}}
                                <td class="text-xs text-[var(--muted)]">
                                    @if ($profile->min_temp_celsius !== null || $profile->max_temp_celsius !== null)
                                        {{ $profile->min_temp_celsius ?? '?' }}°C
                                        –
                                        {{ $profile->max_temp_celsius ?? '?' }}°C
                                    @else
                                        Any
                                    @endif
                                </td>

                                {{-- Humidity --}}
                                <td>
                                    <span class="badge">{{ $profile->humidity_preference }}</span>
                                </td>

                                {{-- Seasons --}}
                                <td>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($profile->ideal_seasons ?? [] as $season)
                                            <span class="badge text-[10px] px-1.5 py-0.5">{{ $season }}</span>
                                        @endforeach
                                    </div>
                                </td>

                                {{-- Weight --}}
                                <td class="text-xs text-[var(--muted)]">
                                    {{ number_format($profile->climate_weight, 2) }}
                                </td>

                                {{-- Linked notes count --}}
                                <td>
                                    @if ($profile->fragrance_notes_count > 0)
                                        <span class="badge badge-active">{{ $profile->fragrance_notes_count }}</span>
                                    @else
                                        <span class="text-xs text-[var(--muted)]">0</span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.note-profiles.edit', $profile->id) }}"
                                           class="btn-ghost text-xs px-3 py-1.5">Edit</a>

                                        <form method="POST" action="{{ route('admin.note-profiles.destroy', $profile->id) }}"
                                              x-data
                                              @submit.prevent="
                                                  const msg = '{{ $profile->fragrance_notes_count > 0
                                                      ? 'Delete «' . addslashes($profile->name) . '»? ' . $profile->fragrance_notes_count . ' note(s) will become unmapped.'
                                                      : 'Delete «' . addslashes($profile->name) . '»?' }}';
                                                  if (confirm(msg)) $el.submit()
                                              ">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-ghost text-xs px-3 py-1.5 text-[var(--error)]">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($profiles->hasPages())
                <div class="px-5 py-4 border-t border-[var(--hairline)]">
                    {{ $profiles->links() }}
                </div>
            @endif

        @endif

    </div>

    <p class="mt-3 text-xs text-[var(--muted)]">
        {{ $profiles->total() }} profile{{ $profiles->total() !== 1 ? 's' : '' }} total
    </p>

@endsection
