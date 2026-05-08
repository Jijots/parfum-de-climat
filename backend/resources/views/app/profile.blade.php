@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="mx-auto max-w-lg px-6 py-16">

    <div class="mb-8">
        <h1 class="font-display text-4xl font-light text-[var(--ink)]">Profile</h1>
        <p class="mt-2 text-sm text-[var(--muted)]">Update your account details</p>
    </div>

    @if (session('status') === 'profile-updated')
        <div class="mb-6 rounded-lg border px-4 py-3" style="border-color:var(--color-accent-border);background:var(--color-accent-dim);">
            <p class="text-sm text-[var(--ink)]">Profile updated successfully.</p>
        </div>
    @endif

    <div class="glass rounded-2xl p-8 shadow-[var(--shadow-flat)]">

        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-[var(--error)]/30 bg-[var(--error)]/10 px-4 py-3">
                @foreach ($errors->all() as $error)
                    <p class="text-sm text-[var(--error)]">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" novalidate>
            @csrf
            @method('PATCH')

            <div class="mb-5">
                <label for="name" class="field-label">Full Name</label>
                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" class="input-field mt-1 w-full @error('name') border-[var(--error)] @enderror" autocomplete="name" required>
                @error('name')<p class="field-error mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="mb-5">
                <label class="field-label">Email</label>
                <p class="input-field mt-1 w-full bg-[var(--shadow-dark)]/5 cursor-default select-all" style="border-color:var(--hairline);">{{ $user->email }}</p>
                <p class="mt-1 text-xs text-[var(--muted)]">Email cannot be changed here.</p>
            </div>

            <div class="mb-5">
                <label for="gender" class="field-label">I'm looking for</label>
                <select id="gender" name="gender" class="input-field mt-1 w-full @error('gender') border-[var(--error)] @enderror">
                    <option value="" {{ (old('gender', $user->gender) === '' || old('gender', $user->gender) === null) ? 'selected' : '' }}>All fragrances</option>
                    <option value="masculine" {{ old('gender', $user->gender) === 'masculine' ? 'selected' : '' }}>Men's &amp; Unisex</option>
                    <option value="feminine" {{ old('gender', $user->gender) === 'feminine' ? 'selected' : '' }}>Women's &amp; Unisex</option>
                    <option value="unisex" {{ old('gender', $user->gender) === 'unisex' ? 'selected' : '' }}>Unisex only</option>
                </select>
                @error('gender')<p class="field-error mt-1">{{ $message }}</p>@enderror
                <p class="mt-1 text-xs text-[var(--muted)]">Used to personalise recommendations and pick-up suggestions.</p>
            </div>

            <div class="mb-6">
                <label for="timezone" class="field-label">Timezone</label>
                <input id="timezone" type="text" name="timezone" value="{{ old('timezone', $user->timezone) }}" class="input-field mt-1 w-full @error('timezone') border-[var(--error)] @enderror" x-data x-init="if (!$el.value || $el.value === 'UTC') $el.value = Intl.DateTimeFormat().resolvedOptions().timeZone">
                @error('timezone')<p class="field-error mt-1">{{ $message }}</p>@enderror
                <p class="mt-1 text-xs text-[var(--muted)]">Used to determine your local season for smarter recommendations.</p>
            </div>

            <button type="submit" class="btn-primary w-full justify-center">Save Changes</button>
        </form>

    </div>
</div>
@endsection
