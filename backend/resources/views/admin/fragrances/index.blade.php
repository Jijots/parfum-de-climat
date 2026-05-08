@extends('layouts.admin')

@section('title', 'Fragrances')
@section('page_title', 'Fragrances')

@section('topbar_actions')
    <a href="{{ route('admin.fragrances.create') }}" class="btn-primary text-sm">
        + Add Fragrance
    </a>
@endsection

@section('content')

    {{-- ── Filters ──────────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('admin.fragrances.index') }}" class="mb-6 flex flex-col sm:flex-row gap-3">

        {{-- Search --}}
        <div class="flex-1">
            <label for="search" class="sr-only">Search by name or brand</label>
            <input
                id="search"
                type="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search name or brand…"
                class="input-field w-full"
            >
        </div>

        {{-- Source --}}
        <div class="sm:w-40">
            <label for="source" class="sr-only">Source</label>
            <select id="source" name="source" class="input-field w-full">
                <option value="">All Sources</option>
                <option value="manual"   {{ request('source') === 'manual'   ? 'selected' : '' }}>Manual</option>
                <option value="parfumo"  {{ request('source') === 'parfumo'  ? 'selected' : '' }}>Parfumo</option>
                <option value="kaggle"   {{ request('source') === 'kaggle'   ? 'selected' : '' }}>Kaggle</option>
                <option value="imported" {{ request('source') === 'imported' ? 'selected' : '' }}>Imported</option>
            </select>
        </div>

        {{-- Status --}}
        <div class="sm:w-36">
            <label for="status" class="sr-only">Status</label>
            <select id="status" name="status" class="input-field w-full">
                <option value="">All Status</option>
                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="deleted"  {{ request('status') === 'deleted'  ? 'selected' : '' }}>Deleted</option>
            </select>
        </div>

        <button type="submit" class="btn-primary shrink-0">Filter</button>

        @if (request()->hasAny(['search', 'source', 'status']))
            <a href="{{ route('admin.fragrances.index') }}" class="btn-ghost shrink-0">Clear</a>
        @endif

    </form>

    {{-- ── Table ────────────────────────────────────────────────────────────── --}}
    <div class="glass rounded-xl overflow-hidden">

        @if ($fragrances->isEmpty())
            <div class="px-6 py-16 text-center">
                <p class="text-sm text-[var(--muted)]">No fragrances match your filters.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Fragrance</th>
                            <th>Family</th>
                            <th>Notes</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($fragrances as $fragrance)
                            <tr class="{{ $fragrance->trashed() ? 'opacity-50' : '' }}">

                                {{-- Name + Brand --}}
                                <td>
                                    <p class="font-medium text-[var(--ink)] text-sm">{{ $fragrance->name }}</p>
                                    <p class="text-xs text-[var(--muted)]">{{ $fragrance->brand }}</p>
                                </td>

                                {{-- Olfactive Family --}}
                                <td class="text-sm text-[var(--muted)]">
                                    {{ $fragrance->olfactive_family ?? '—' }}
                                </td>

                                {{-- Notes mapping badge --}}
                                <td>
                                    @php
                                        $mapped = $fragrance->mapped_notes_count ?? 0;
                                        $total  = $fragrance->notes_count ?? 0;
                                    @endphp
                                    @if ($total > 0)
                                        <span class="badge {{ $mapped === $total ? 'badge-active' : 'badge-error' }}">
                                            {{ $mapped }}/{{ $total }} mapped
                                        </span>
                                    @else
                                        <span class="text-xs text-[var(--muted)]">No notes</span>
                                    @endif
                                </td>

                                {{-- External source --}}
                                <td>
                                    <span class="badge">{{ $fragrance->external_source ?? 'manual' }}</span>
                                </td>

                                {{-- Status --}}
                                <td>
                                    @if ($fragrance->trashed())
                                        <span class="badge badge-error">Deleted</span>
                                    @elseif ($fragrance->is_active)
                                        <span class="badge badge-active">Active</span>
                                    @else
                                        <span class="badge badge-inactive">Inactive</span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a
                                            href="{{ route('admin.fragrances.edit', $fragrance->id) }}"
                                            class="btn-ghost text-xs px-3 py-1.5"
                                        >
                                            {{ $fragrance->trashed() ? 'View' : 'Edit' }}
                                        </a>

                                        @if ($fragrance->trashed())
                                            {{-- Restore --}}
                                            <form method="POST" action="{{ route('admin.fragrances.destroy', $fragrance->id) }}">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn-ghost text-xs px-3 py-1.5 text-green-600">
                                                    Restore
                                                </button>
                                            </form>
                                        @else
                                            {{-- Soft delete --}}
                                            <form method="POST" action="{{ route('admin.fragrances.destroy', $fragrance->id) }}"
                                                  x-data
                                                  @submit.prevent="if (confirm('Deactivate «{{ addslashes($fragrance->name) }}»?')) $el.submit()">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn-ghost text-xs px-3 py-1.5 text-[var(--error)]">
                                                    Remove
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($fragrances->hasPages())
                <div class="px-5 py-4 border-t border-[var(--hairline)]">
                    {{ $fragrances->links() }}
                </div>
            @endif

        @endif

    </div>

    {{-- Result count --}}
    <p class="mt-3 text-xs text-[var(--muted)]">
        Showing {{ $fragrances->firstItem() ?? 0 }}–{{ $fragrances->lastItem() ?? 0 }}
        of {{ $fragrances->total() }} fragrances
    </p>

@endsection
