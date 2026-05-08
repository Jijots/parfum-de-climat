@extends('layouts.admin')

@section('title', 'Add Fragrance')
@section('page_title', 'Add Fragrance')

@section('topbar_actions')
    <a href="{{ route('admin.fragrances.index') }}" class="btn-ghost text-sm">← Back</a>
@endsection

@section('content')

    <form
        method="POST"
        action="{{ route('admin.fragrances.store') }}"
        enctype="multipart/form-data"
        novalidate
    >
        @csrf

        <div class="grid gap-6 lg:grid-cols-3">

            {{-- ── Left column: core identity ───────────────────────────────── --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Basic info card --}}
                <div class="glass rounded-xl p-6">
                    <h2 class="font-display text-lg font-light text-[var(--ink)] mb-5">Identity</h2>

                    <div class="grid gap-4 sm:grid-cols-2">

                        <div class="sm:col-span-2">
                            <label for="name" class="field-label">Fragrance Name <span class="text-[var(--error)]">*</span></label>
                            <input id="name" type="text" name="name" value="{{ old('name') }}"
                                   class="input-field mt-1 w-full @error('name') border-[var(--error)] @enderror"
                                   placeholder="e.g. Aventus" required>
                            @error('name') <p class="field-error mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="brand" class="field-label">Brand <span class="text-[var(--error)]">*</span></label>
                            <input id="brand" type="text" name="brand" value="{{ old('brand') }}"
                                   class="input-field mt-1 w-full @error('brand') border-[var(--error)] @enderror"
                                   placeholder="e.g. Creed" required>
                            @error('brand') <p class="field-error mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="release_year" class="field-label">Release Year</label>
                            <input id="release_year" type="number" name="release_year"
                                   value="{{ old('release_year') }}"
                                   min="1800" max="{{ date('Y') }}"
                                   class="input-field mt-1 w-full @error('release_year') border-[var(--error)] @enderror"
                                   placeholder="{{ date('Y') }}">
                            @error('release_year') <p class="field-error mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="concentration" class="field-label">Concentration</label>
                            <select id="concentration" name="concentration"
                                    class="input-field mt-1 w-full @error('concentration') border-[var(--error)] @enderror">
                                <option value="">— Select —</option>
                                @foreach (['Parfum', 'Eau de Parfum', 'Eau de Toilette', 'Eau de Cologne', 'Eau Fraîche', 'Soie de Parfum'] as $c)
                                    <option value="{{ $c }}" {{ old('concentration') === $c ? 'selected' : '' }}>{{ $c }}</option>
                                @endforeach
                            </select>
                            @error('concentration') <p class="field-error mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="olfactive_family" class="field-label">Olfactive Family</label>
                            <input id="olfactive_family" type="text" name="olfactive_family"
                                   value="{{ old('olfactive_family') }}"
                                   class="input-field mt-1 w-full @error('olfactive_family') border-[var(--error)] @enderror"
                                   placeholder="e.g. Woody Aromatic">
                            @error('olfactive_family') <p class="field-error mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="gender_target" class="field-label">Gender Target <span class="text-[var(--error)]">*</span></label>
                            <select id="gender_target" name="gender_target"
                                    class="input-field mt-1 w-full @error('gender_target') border-[var(--error)] @enderror" required>
                                <option value="">— Select —</option>
                                @foreach (['masculine' => 'Masculine', 'feminine' => 'Feminine', 'unisex' => 'Unisex'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('gender_target') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('gender_target') <p class="field-error mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="description" class="field-label">Description</label>
                            <textarea id="description" name="description" rows="4"
                                      class="input-field mt-1 w-full @error('description') border-[var(--error)] @enderror"
                                      placeholder="A brief editorial description…" maxlength="2000">{{ old('description') }}</textarea>
                            @error('description') <p class="field-error mt-1">{{ $message }}</p> @enderror
                        </div>

                    </div>
                </div>

                {{-- Performance card --}}
                <div class="glass rounded-xl p-6">
                    <h2 class="font-display text-lg font-light text-[var(--ink)] mb-5">Performance</h2>
                    <p class="text-xs text-[var(--muted)] mb-5">Scale 0–10 for sillage and longevity; 0–5 for rating.</p>

                    <div class="grid gap-5 sm:grid-cols-3">

                        @foreach ([
                            ['sillage',   'Sillage',   0, 10, 0.5],
                            ['longevity', 'Longevity', 0, 10, 0.5],
                        ] as [$name, $label, $min, $max, $step])
                            <div x-data="{ val: {{ old($name, '') ?: 'null' }} }">
                                <label for="{{ $name }}" class="field-label flex justify-between">
                                    {{ $label }}
                                    <span class="text-[var(--ink)]" x-text="val !== null ? val : '—'"></span>
                                </label>
                                <input
                                    id="{{ $name }}" type="range" name="{{ $name }}"
                                    min="{{ $min }}" max="{{ $max }}" step="{{ $step }}"
                                    value="{{ old($name, '') }}"
                                    class="mt-2 w-full accent-[var(--color-accent)] cursor-pointer"
                                    x-model="val"
                                >
                                <div class="flex justify-between text-[10px] text-[var(--muted)] mt-1">
                                    <span>{{ $min }}</span><span>{{ $max }}</span>
                                </div>
                                @error($name) <p class="field-error mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endforeach

                        <div x-data="{ val: {{ old('rating', '') ?: 'null' }} }">
                            <label for="rating" class="field-label flex justify-between">
                                Community Rating
                                <span class="text-[var(--ink)]" x-text="val !== null ? val : '—'"></span>
                            </label>
                            <input
                                id="rating" type="range" name="rating"
                                min="0" max="5" step="0.1"
                                value="{{ old('rating', '') }}"
                                class="mt-2 w-full accent-[var(--color-accent)] cursor-pointer"
                                x-model="val"
                            >
                            <div class="flex justify-between text-[10px] text-[var(--muted)] mt-1">
                                <span>0</span><span>5</span>
                            </div>
                            @error('rating') <p class="field-error mt-1">{{ $message }}</p> @enderror
                        </div>

                    </div>

                    <div class="mt-4">
                        <label for="votes" class="field-label">Community Votes</label>
                        <input id="votes" type="number" name="votes" value="{{ old('votes') }}"
                               min="0"
                               class="input-field mt-1 w-48 @error('votes') border-[var(--error)] @enderror"
                               placeholder="0">
                        @error('votes') <p class="field-error mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

            </div>

            {{-- ── Right column: image + settings ───────────────────────────── --}}
            <div class="space-y-6">

                {{-- Image --}}
                <div class="glass rounded-xl p-6">
                    <h2 class="font-display text-lg font-light text-[var(--ink)] mb-5">Image</h2>

                    <div class="mb-4">
                        <label for="image" class="field-label">Upload Image</label>
                        <p class="text-xs text-[var(--muted)] mt-0.5 mb-2">JPG/PNG/WEBP, max 4 MB</p>
                        <input id="image" type="file" name="image" accept="image/*"
                               class="block w-full text-sm text-[var(--muted)] cursor-pointer
                                      file:mr-3 file:rounded-md file:border-0 file:bg-[var(--color-accent)]/15
                                      file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-[var(--color-accent)]
                                      hover:file:bg-[var(--color-accent)]/25">
                        @error('image') <p class="field-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="divider my-4"></div>

                    <div>
                        <label for="remote_image_url" class="field-label">Remote Image URL</label>
                        <input id="remote_image_url" type="url" name="remote_image_url"
                               value="{{ old('remote_image_url') }}"
                               class="input-field mt-1 w-full @error('remote_image_url') border-[var(--error)] @enderror"
                               placeholder="https://…">
                        @error('remote_image_url') <p class="field-error mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Settings --}}
                <div class="glass rounded-xl p-6">
                    <h2 class="font-display text-lg font-light text-[var(--ink)] mb-5">Settings</h2>

                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-[var(--hairline)] accent-[var(--color-accent)]">
                        <div>
                            <p class="text-sm font-medium text-[var(--ink)]">Active</p>
                            <p class="text-xs text-[var(--muted)]">Visible to the recommendation engine</p>
                        </div>
                    </label>
                </div>

                {{-- Submit --}}
                <div class="flex gap-3">
                    <button type="submit" class="btn-primary flex-1 justify-center">
                        Create Fragrance
                    </button>
                    <a href="{{ route('admin.fragrances.index') }}" class="btn-ghost">Cancel</a>
                </div>

            </div>

        </div>

    </form>

@endsection
