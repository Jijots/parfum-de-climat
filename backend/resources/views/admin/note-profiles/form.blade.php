@extends('layouts.admin')

@section('title', $profile ? 'Edit — ' . $profile->name : 'New Note Profile')
@section('page_title', $profile ? $profile->name : 'New Note Profile')

@section('topbar_actions')
    <a href="{{ route('admin.note-profiles.index') }}" class="btn-ghost text-sm">← Profiles</a>
@endsection

@section('content')

    <form
        method="POST"
        action="{{ $profile
            ? route('admin.note-profiles.update', $profile->id)
            : route('admin.note-profiles.store') }}"
        novalidate
    >
        @csrf
        @if ($profile) @method('PUT') @endif

        <div class="grid gap-6 lg:grid-cols-3">

            {{-- ── Left: core fields ────────────────────────────────────────── --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Identity card --}}
                <div class="glass rounded-xl p-6">
                    <div class="flex items-start justify-between mb-5">
                        <h2 class="font-display text-lg font-light text-[var(--ink)]">Profile Identity</h2>
                        @if ($profile && $profile->fragrance_notes_count > 0)
                            <span class="badge badge-active">
                                {{ $profile->fragrance_notes_count }} note{{ $profile->fragrance_notes_count !== 1 ? 's' : '' }} linked
                            </span>
                        @endif
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">

                        <div class="sm:col-span-2">
                            <label for="name" class="field-label">Note Name <span class="text-[var(--error)]">*</span></label>
                            <input id="name" type="text" name="name"
                                   value="{{ old('name', $profile?->name) }}"
                                   class="input-field mt-1 w-full @error('name') border-[var(--error)] @enderror"
                                   placeholder="e.g. Bergamot" required>
                            <p class="text-xs text-[var(--muted)] mt-1">Title-cased on save (e.g. "bergamot" → "Bergamot")</p>
                            @error('name') <p class="field-error mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="note_family" class="field-label">Note Family</label>
                            <input id="note_family" type="text" name="note_family"
                                   value="{{ old('note_family', $profile?->note_family) }}"
                                   class="input-field mt-1 w-full @error('note_family') border-[var(--error)] @enderror"
                                   placeholder="e.g. Citrus, Woody, Floral…">
                            @error('note_family') <p class="field-error mt-1">{{ $message }}</p> @enderror
                        </div>

                    </div>
                </div>

                {{-- Climate behaviour --}}
                <div class="glass rounded-xl p-6">
                    <h2 class="font-display text-lg font-light text-[var(--ink)] mb-5">Climate Behaviour</h2>

                    {{-- Temperature range --}}
                    <div class="grid gap-4 sm:grid-cols-2 mb-6">
                        <div>
                            <label for="min_temp_celsius" class="field-label">Min Temperature (°C)</label>
                            <input id="min_temp_celsius" type="number" name="min_temp_celsius"
                                   value="{{ old('min_temp_celsius', $profile?->min_temp_celsius) }}"
                                   min="-20" max="50"
                                   class="input-field mt-1 w-full @error('min_temp_celsius') border-[var(--error)] @enderror"
                                   placeholder="e.g. -5">
                            <p class="text-xs text-[var(--muted)] mt-1">Leave blank = no lower bound</p>
                            @error('min_temp_celsius') <p class="field-error mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="max_temp_celsius" class="field-label">Max Temperature (°C)</label>
                            <input id="max_temp_celsius" type="number" name="max_temp_celsius"
                                   value="{{ old('max_temp_celsius', $profile?->max_temp_celsius) }}"
                                   min="-20" max="50"
                                   class="input-field mt-1 w-full @error('max_temp_celsius') border-[var(--error)] @enderror"
                                   placeholder="e.g. 25">
                            <p class="text-xs text-[var(--muted)] mt-1">Leave blank = no upper bound</p>
                            @error('max_temp_celsius') <p class="field-error mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Humidity preference --}}
                    <div class="mb-6">
                        <label for="humidity_preference" class="field-label">
                            Humidity Preference <span class="text-[var(--error)]">*</span>
                        </label>
                        <select id="humidity_preference" name="humidity_preference"
                                class="input-field mt-1 w-full sm:w-56 @error('humidity_preference') border-[var(--error)] @enderror"
                                required>
                            @foreach (['low' => 'Low (arid / desert)', 'medium' => 'Medium (temperate)', 'high' => 'High (tropical / humid)', 'any' => 'Any humidity'] as $val => $label)
                                <option value="{{ $val }}"
                                    {{ old('humidity_preference', $profile?->humidity_preference) === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('humidity_preference') <p class="field-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Ideal seasons (checkboxes) --}}
                    <div>
                        <fieldset>
                            <legend class="field-label mb-2">
                                Ideal Seasons <span class="text-[var(--error)]">*</span>
                            </legend>
                            @error('ideal_seasons')
                                <p class="field-error mb-2">{{ $message }}</p>
                            @enderror

                            {{--
                                ideal_seasons is stored as a PHP array (JSON column).
                                During validation failure, old('ideal_seasons') returns the submitted array.
                                On edit, $profile->ideal_seasons is already a PHP array from the cast.
                            --}}
                            @php
                                $selectedSeasons = old('ideal_seasons', $profile?->ideal_seasons ?? []);
                            @endphp

                            <div class="flex flex-wrap gap-x-6 gap-y-3">
                                @foreach (['spring', 'summer', 'autumn', 'winter'] as $season)
                                    <label class="flex items-center gap-2 cursor-pointer select-none">
                                        <input
                                            type="checkbox"
                                            name="ideal_seasons[]"
                                            value="{{ $season }}"
                                            {{ in_array($season, $selectedSeasons) ? 'checked' : '' }}
                                            class="h-4 w-4 rounded border-[var(--hairline)] accent-[var(--color-accent)]"
                                        >
                                        <span class="text-sm capitalize text-[var(--ink)]">{{ $season }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    </div>
                </div>

            </div>

            {{-- ── Right: weight + actions ───────────────────────────────────── --}}
            <div class="space-y-6">

                {{-- Climate weight --}}
                <div class="glass rounded-xl p-6">
                    <h2 class="font-display text-lg font-light text-[var(--ink)] mb-2">Climate Weight</h2>
                    <p class="text-xs text-[var(--muted)] mb-5">
                        Controls how strongly this note influences the overall fragrance score.
                        0.0 = no influence, 1.0 = maximum influence.
                    </p>

                    @php $weightVal = old('climate_weight', $profile?->climate_weight ?? 0.5); @endphp
                    <div x-data="{ val: {{ $weightVal }} }">
                        <div class="flex items-center justify-between mb-2">
                            <label for="climate_weight" class="field-label">Weight</label>
                            <span class="font-display text-2xl font-light text-[var(--ink)]"
                                  x-text="parseFloat(val).toFixed(2)"></span>
                        </div>
                        <input
                            id="climate_weight"
                            type="range"
                            name="climate_weight"
                            min="0" max="1" step="0.01"
                            value="{{ $weightVal }}"
                            class="w-full accent-[var(--color-accent)] cursor-pointer"
                            x-model="val"
                            required
                        >
                        <div class="flex justify-between text-[10px] text-[var(--muted)] mt-1">
                            <span>0.0</span>
                            <span>0.5</span>
                            <span>1.0</span>
                        </div>
                    </div>
                    @error('climate_weight') <p class="field-error mt-2">{{ $message }}</p> @enderror
                </div>

                {{-- Actions --}}
                <div class="space-y-3">
                    <button type="submit" class="btn-primary w-full justify-center">
                        {{ $profile ? 'Save Changes' : 'Create Profile' }}
                    </button>
                    <a href="{{ route('admin.note-profiles.index') }}" class="btn-ghost block text-center">
                        Cancel
                    </a>
                </div>

                {{-- Delete (edit only) --}}
                @if ($profile)
                    <div class="divider"></div>
                    <form method="POST" action="{{ route('admin.note-profiles.destroy', $profile->id) }}"
                          x-data
                          @submit.prevent="
                              const msg = @js($profile->fragrance_notes_count > 0
                                  ? 'Delete «' . $profile->name . '»? ' . $profile->fragrance_notes_count . ' note(s) will become unmapped.'
                                  : 'Delete «' . $profile->name . '»?');
                              if (confirm(msg)) $el.submit()
                          ">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-ghost w-full justify-center text-[var(--error)]">
                            Delete Profile
                        </button>
                    </form>
                @endif

            </div>

        </div>

    </form>

@endsection
