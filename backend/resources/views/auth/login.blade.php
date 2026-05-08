@extends('layouts.app')

@section('title', 'Admin Login')

@section('content')

    {{--
        Login page: centered .glass card containing neumorphic inputs.
        Full viewport height minus the fixed header (3.5rem / 14 Tailwind units).
        No self-service links (register, forgot password) — admin-only panel.
    --}}
    <div class="flex min-h-[calc(100vh-3.5rem)] items-center justify-center px-6 py-12">

        <div class="glass w-full max-w-sm rounded-2xl p-8 shadow-[var(--shadow-flat)]">

            {{-- Header --}}
            <div class="mb-8 text-center">
                <h1 class="font-display text-3xl font-light text-[var(--ink)]">Admin Panel</h1>
                <p class="mt-1 text-sm text-[var(--muted)]">Parfum de Climat — restricted access</p>
            </div>

            {{-- Global validation error (e.g. "Your account does not have administrator access.") --}}
            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-[var(--error)]/30 bg-[var(--error)]/10 px-4 py-3">
                    @foreach ($errors->all() as $error)
                        <p class="text-sm text-[var(--error)]">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login') }}" novalidate>
                @csrf

                {{-- Email --}}
                <div class="mb-5">
                    <label for="email" class="field-label">Email Address</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="input-field mt-1 w-full @error('email') border-[var(--error)] @enderror"
                        autocomplete="email"
                        autofocus
                        required
                    >
                    @error('email')
                        <p class="field-error mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="mb-6">
                    <label for="password" class="field-label">Password</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="input-field mt-1 w-full @error('password') border-[var(--error)] @enderror"
                        autocomplete="current-password"
                        required
                    >
                    @error('password')
                        <p class="field-error mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember me --}}
                <div class="mb-6 flex items-center gap-3">
                    <input
                        id="remember"
                        type="checkbox"
                        name="remember"
                        class="h-4 w-4 rounded border-[var(--hairline)] bg-[var(--bg)] accent-[var(--color-accent)] cursor-pointer"
                    >
                    <label for="remember" class="text-sm text-[var(--muted)] cursor-pointer select-none">
                        Keep me signed in
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn-primary w-full justify-center">
                    Sign In
                </button>

            </form>

        </div>
    </div>

@endsection
