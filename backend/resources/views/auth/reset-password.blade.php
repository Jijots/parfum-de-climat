@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
<div class="flex min-h-[calc(100vh-3.5rem)] items-center justify-center px-6 py-12">
    <div class="w-full max-w-sm">

        <div class="mb-8 text-center">
            <h1 class="font-display text-4xl font-light text-[var(--ink)]">Reset password</h1>
            <p class="mt-2 text-sm text-[var(--muted)]">Choose a new password for your account.</p>
        </div>

        <div class="glass rounded-2xl p-8 shadow-[var(--shadow-flat)]">

            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-[var(--error)]/30 bg-[var(--error)]/10 px-4 py-3">
                    @foreach ($errors->all() as $error)
                        <p class="text-sm text-[var(--error)]">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" novalidate>
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-5">
                    <label for="email" class="field-label">Email address</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email', $email) }}"
                        class="input-field mt-1 w-full @error('email') border-[var(--error)] @enderror"
                        autocomplete="email"
                        autofocus
                        required
                    >
                    @error('email')<p class="field-error mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="mb-5">
                    <label for="password" class="field-label">New password</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="input-field mt-1 w-full @error('password') border-[var(--error)] @enderror"
                        autocomplete="new-password"
                        required
                    >
                    @error('password')<p class="field-error mt-1">{{ $message }}</p>@enderror
                    <p class="mt-1 text-xs text-[var(--muted)]">Min. 12 characters with uppercase, lowercase, number, and symbol.</p>
                </div>

                <div class="mb-6">
                    <label for="password_confirmation" class="field-label">Confirm new password</label>
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        class="input-field mt-1 w-full"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <button type="submit" class="btn-primary w-full justify-center">Reset Password</button>
            </form>

        </div>

    </div>
</div>
@endsection
