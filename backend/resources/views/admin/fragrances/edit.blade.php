@extends('layouts.admin')

@section('title', $fragrance->name . ' — Edit')
@section('page_title', $fragrance->name)

@section('topbar_actions')
    <a href="{{ route('admin.fragrances.index') }}" class="btn-ghost text-sm">← Fragrances</a>
@endsection

@section('content')

    {{-- ── Trashed banner ─────────────────────────────────────────────────── --}}
    @if ($fragrance->trashed())
        <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4
                    rounded-lg border border-[var(--error)]/30 bg-[var(--error)]/8 px-4 py-3">
            <div>
                <p class="text-sm font-medium text-[var(--error)]">This fragrance has been soft-deleted.</p>
                <p class="text-xs text-[var(--muted)] mt-0.5">
                    Deleted {{ $fragrance->deleted_at->diffForHumans() }}.
                    Restore it to make it available to users again.
                </p>
            </div>
            <div class="flex gap-2 shrink-0">
                {{-- Restore --}}
                <form method="POST" action="{{ route('admin.fragrances.destroy', $fragrance->id) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-ghost text-sm text-green-600 whitespace-nowrap">
                        Restore Fragrance
                    </button>
                </form>
                {{-- Force delete --}}
                <form method="POST" action="{{ route('admin.fragrances.destroy', $fragrance->id) }}"
                      x-data
                      @submit.prevent="if (confirm('Permanently delete «{{ addslashes($fragrance->name) }}»? This cannot be undone.')) $el.submit()">
                    @csrf @method('DELETE')
                    <input type="hidden" name="force" value="1">
                    <button type="submit" class="btn-ghost text-sm text-[var(--error)] whitespace-nowrap">
                        Delete Permanently
                    </button>
                </form>
            </div>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('admin.fragrances.update', $fragrance->id) }}"
        enctype="multipart/form-data"
        novalidate
    >
        @csrf @method('PUT')

        <div class="grid gap-6 lg:grid-cols-3">

            {{-- ── Left column ──────────────────────────────────────────────── --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Basic info --}}
                <div class="glass rounded-xl p-6">
                    <h2 class="font-display text-lg font-light text-[var(--ink)] mb-5">Identity</h2>
                    <div class="grid gap-4 sm:grid-cols-2">

                        <div class="sm:col-span-2">
                            <label for="name" class="field-label">Fragrance Name <span class="text-[var(--error)]">*</span></label>
                            <input id="name" type="text" name="name" value="{{ old('name', $fragrance->name) }}"
                                   class="input-field mt-1 w-full @error('name') border-[var(--error)] @enderror" required>
                            @error('name') <p class="field-error mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="brand" class="field-label">Brand <span class="text-[var(--error)]">*</span></label>
                            <input id="brand" type="text" name="brand" value="{{ old('brand', $fragrance->brand) }}"
                                   class="input-field mt-1 w-full @error('brand') border-[var(--error)] @enderror" required>
                            @error('brand') <p class="field-error mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="release_year" class="field-label">Release Year</label>
                            <input id="release_year" type="number" name="release_year"
                                   value="{{ old('release_year', $fragrance->release_year) }}"
                                   min="1800" max="{{ date('Y') }}"
                                   class="input-field mt-1 w-full @error('release_year') border-[var(--error)] @enderror">
                            @error('release_year') <p class="field-error mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="concentration" class="field-label">Concentration</label>
                            <select id="concentration" name="concentration" class="input-field mt-1 w-full">
                                <option value="">— Select —</option>
                                @foreach (['Parfum', 'Eau de Parfum', 'Eau de Toilette', 'Eau de Cologne', 'Eau Fraîche', 'Soie de Parfum'] as $c)
                                    <option value="{{ $c }}"
                                        {{ old('concentration', $fragrance->concentration) === $c ? 'selected' : '' }}>
                                        {{ $c }}
                                    </option>
                                @endforeach
                            </select>
                            @error('concentration') <p class="field-error mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="olfactive_family" class="field-label">Olfactive Family</label>
                            <input id="olfactive_family" type="text" name="olfactive_family"
                                   value="{{ old('olfactive_family', $fragrance->olfactive_family) }}"
                                   class="input-field mt-1 w-full @error('olfactive_family') border-[var(--error)] @enderror">
                            @error('olfactive_family') <p class="field-error mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="gender_target" class="field-label">Gender Target <span class="text-[var(--error)]">*</span></label>
                            <select id="gender_target" name="gender_target" class="input-field mt-1 w-full" required>
                                <option value="">— Select —</option>
                                @foreach (['masculine' => 'Masculine', 'feminine' => 'Feminine', 'unisex' => 'Unisex'] as $val => $label)
                                    <option value="{{ $val }}"
                                        {{ old('gender_target', $fragrance->gender_target) === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('gender_target') <p class="field-error mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="description" class="field-label">Description</label>
                            <textarea id="description" name="description" rows="4"
                                      class="input-field mt-1 w-full @error('description') border-[var(--error)] @enderror"
                                      maxlength="2000">{{ old('description', $fragrance->description) }}</textarea>
                            @error('description') <p class="field-error mt-1">{{ $message }}</p> @enderror
                        </div>

                    </div>
                </div>

                {{-- Performance --}}
                <div class="glass rounded-xl p-6">
                    <h2 class="font-display text-lg font-light text-[var(--ink)] mb-5">Performance</h2>
                    <div class="grid gap-5 sm:grid-cols-3">

                        @foreach ([
                            ['sillage',   'Sillage',   0, 10, 0.5],
                            ['longevity', 'Longevity', 0, 10, 0.5],
                        ] as [$field, $label, $min, $max, $step])
                            @php $currentVal = old($field, $fragrance->{$field}); @endphp
                            <div x-data="{ val: {{ $currentVal !== null ? $currentVal : 'null' }} }">
                                <label for="{{ $field }}" class="field-label flex justify-between">
                                    {{ $label }}
                                    <span class="text-[var(--ink)]" x-text="val !== null ? val : '—'"></span>
                                </label>
                                <input id="{{ $field }}" type="range" name="{{ $field }}"
                                       min="{{ $min }}" max="{{ $max }}" step="{{ $step }}"
                                       value="{{ $currentVal ?? '' }}"
                                       class="mt-2 w-full accent-[var(--color-accent)] cursor-pointer"
                                       x-model="val">
                                <div class="flex justify-between text-[10px] text-[var(--muted)] mt-1">
                                    <span>{{ $min }}</span><span>{{ $max }}</span>
                                </div>
                                @error($field) <p class="field-error mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endforeach

                        @php $ratingVal = old('rating', $fragrance->rating); @endphp
                        <div x-data="{ val: {{ $ratingVal !== null ? $ratingVal : 'null' }} }">
                            <label for="rating" class="field-label flex justify-between">
                                Community Rating
                                <span class="text-[var(--ink)]" x-text="val !== null ? val : '—'"></span>
                            </label>
                            <input id="rating" type="range" name="rating"
                                   min="0" max="5" step="0.1"
                                   value="{{ $ratingVal ?? '' }}"
                                   class="mt-2 w-full accent-[var(--color-accent)] cursor-pointer"
                                   x-model="val">
                            <div class="flex justify-between text-[10px] text-[var(--muted)] mt-1">
                                <span>0</span><span>5</span>
                            </div>
                            @error('rating') <p class="field-error mt-1">{{ $message }}</p> @enderror
                        </div>

                    </div>
                    <div class="mt-4">
                        <label for="votes" class="field-label">Community Votes</label>
                        <input id="votes" type="number" name="votes"
                               value="{{ old('votes', $fragrance->votes) }}" min="0"
                               class="input-field mt-1 w-48">
                        @error('votes') <p class="field-error mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- ── Note pyramid ──────────────────────────────────────────── --}}
                @if ($fragrance->notes->isNotEmpty())
                    <div class="glass rounded-xl p-6">
                        <div class="flex items-center justify-between mb-5">
                            <h2 class="font-display text-lg font-light text-[var(--ink)]">Note Pyramid</h2>
                            <p class="text-xs text-[var(--muted)]">
                                {{ $fragrance->notes->where('note_profile_id', '!=', null)->count() }}/{{ $fragrance->notes->count() }} mapped
                            </p>
                        </div>

                        {{-- Note profile assignment is handled via a dedicated API endpoint or inline form.
                             Here we display read-only pyramid data; profile links are shown as badges.
                             Full note-profile assignment UI would require a separate AJAX form per note
                             and is out of scope for this Blade admin — handle in a future enhancement. --}}
                        @foreach (['top' => 'Top Notes', 'heart' => 'Heart Notes', 'base' => 'Base Notes'] as $type => $heading)
                            @php $notesOfType = $fragrance->notes->where('note_type', $type); @endphp
                            @if ($notesOfType->isNotEmpty())
                                <div class="mb-4 last:mb-0">
                                    <p class="section-label mb-2">{{ $heading }}</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($notesOfType as $note)
                                            <span class="inline-flex items-center gap-1.5 rounded-full border border-[var(--hairline)]
                                                         px-3 py-1 text-xs
                                                         {{ $note->note_profile_id ? 'text-[var(--ink)]' : 'text-[var(--error)] border-[var(--error)]/30' }}">
                                                {{ $note->name }}
                                                @if ($note->profile)
                                                    <span class="text-[var(--muted)]">·</span>
                                                    <span class="text-[var(--muted)]">{{ $note->profile->name }}</span>
                                                @else
                                                    <span class="text-[var(--error)]">unmapped</span>
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif

                {{-- ── Accords ───────────────────────────────────────────────── --}}
                @if ($fragrance->accords->isNotEmpty())
                    <div class="glass rounded-xl p-6">
                        <h2 class="font-display text-lg font-light text-[var(--ink)] mb-5">Accords</h2>
                        <div class="space-y-2">
                            @foreach ($fragrance->accords->sortByDesc('weight') as $accord)
                                <div class="flex items-center gap-3">
                                    <span class="w-28 shrink-0 text-xs text-[var(--muted)]">{{ $accord->name }}</span>
                                    <div class="flex-1 h-2 rounded-full bg-[var(--bg)] neu-inset overflow-hidden">
                                        <div class="h-full rounded-full bg-[var(--color-accent)]"
                                             style="width: {{ min(100, $accord->weight * 100) }}%"></div>
                                    </div>
                                    <span class="w-10 shrink-0 text-right text-xs text-[var(--muted)]">
                                        {{ number_format($accord->weight * 100, 0) }}%
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

            {{-- ── Right column ─────────────────────────────────────────────── --}}
            <div class="space-y-6">

                {{-- Image --}}
                <div class="glass rounded-xl p-6">
                    <h2 class="font-display text-lg font-light text-[var(--ink)] mb-5">Image</h2>

                    {{-- Current image preview --}}
                    @if ($fragrance->cached_image_path)
                        <div class="mb-4">
                            <img src="{{ Storage::url($fragrance->cached_image_path) }}"
                                 alt="{{ $fragrance->name }}"
                                 class="w-full max-h-48 object-contain rounded-lg bg-[var(--bg)]">
                        </div>
                    @elseif ($fragrance->remote_image_url)
                        <div class="mb-4">
                            <img src="{{ $fragrance->remote_image_url }}"
                                 alt="{{ $fragrance->name }}"
                                 class="w-full max-h-48 object-contain rounded-lg bg-[var(--bg)]">
                        </div>
                    @endif

                    <div class="mb-4">
                        <label for="image" class="field-label">Replace Image</label>
                        <p class="text-xs text-[var(--muted)] mt-0.5 mb-2">Uploading replaces the current cached image</p>
                        <input id="image" type="file" name="image" accept="image/*"
                               class="block w-full text-sm text-[var(--muted)] cursor-pointer
                                      file:mr-3 file:rounded-md file:border-0 file:bg-[var(--color-accent)]/15
                                      file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-[var(--color-accent)]
                                      hover:file:bg-[var(--color-accent)]/25">
                        @error('image') <p class="field-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="remote_image_url" class="field-label">Remote Image URL</label>
                        <input id="remote_image_url" type="url" name="remote_image_url"
                               value="{{ old('remote_image_url', $fragrance->remote_image_url) }}"
                               class="input-field mt-1 w-full" placeholder="https://…">
                        @error('remote_image_url') <p class="field-error mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Settings --}}
                <div class="glass rounded-xl p-6">
                    <h2 class="font-display text-lg font-light text-[var(--ink)] mb-5">Settings</h2>
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $fragrance->is_active) ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-[var(--hairline)] accent-[var(--color-accent)]">
                        <div>
                            <p class="text-sm font-medium text-[var(--ink)]">Active</p>
                            <p class="text-xs text-[var(--muted)]">Visible to the recommendation engine</p>
                        </div>
                    </label>
                </div>

                {{-- Metadata --}}
                <div class="glass rounded-xl p-6">
                    <h2 class="font-display text-lg font-light text-[var(--ink)] mb-4">Metadata</h2>
                    <dl class="space-y-2 text-xs">
                        @if ($fragrance->addedBy)
                            <div class="flex gap-2">
                                <dt class="w-20 shrink-0 text-[var(--muted)]">Added by</dt>
                                <dd class="text-[var(--ink)]">{{ $fragrance->addedBy->name }}</dd>
                            </div>
                        @endif
                        <div class="flex gap-2">
                            <dt class="w-20 shrink-0 text-[var(--muted)]">Source</dt>
                            <dd class="text-[var(--ink)]">{{ $fragrance->external_source ?? 'manual' }}</dd>
                        </div>
                        @if ($fragrance->external_id)
                            <div class="flex gap-2">
                                <dt class="w-20 shrink-0 text-[var(--muted)]">External ID</dt>
                                <dd class="text-[var(--ink)] font-mono">{{ $fragrance->external_id }}</dd>
                            </div>
                        @endif
                        <div class="flex gap-2">
                            <dt class="w-20 shrink-0 text-[var(--muted)]">Created</dt>
                            <dd class="text-[var(--ink)]" title="{{ $fragrance->created_at->format('d M Y H:i') }}">
                                {{ $fragrance->created_at->diffForHumans() }}
                            </dd>
                        </div>
                        <div class="flex gap-2">
                            <dt class="w-20 shrink-0 text-[var(--muted)]">Updated</dt>
                            <dd class="text-[var(--ink)]" title="{{ $fragrance->updated_at->format('d M Y H:i') }}">
                                {{ $fragrance->updated_at->diffForHumans() }}
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- Actions --}}
                <div class="space-y-3">
                    <button type="submit" class="btn-primary w-full justify-center">
                        Save Changes
                    </button>

                    @if (! $fragrance->trashed())
                        <form method="POST" action="{{ route('admin.fragrances.destroy', $fragrance->id) }}"
                              x-data
                              @submit.prevent="if (confirm('Deactivate «{{ addslashes($fragrance->name) }}»? You can restore it later.')) $el.submit()">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-ghost w-full justify-center text-[var(--error)]">
                                Deactivate Fragrance
                            </button>
                        </form>
                    @endif
                </div>

            </div>

        </div>

    </form>

@endsection

