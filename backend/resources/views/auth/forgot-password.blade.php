@extends('layouts.app')

@section('title', 'Forgot Password')

@section('content')
<div class="flex min-h-[calc(100vh-3.5rem)] items-center justify-center px-6 py-12">
    <div class="w-full max-w-sm">

        <div class="mb-8 text-center">
            <h1 class="font-display text-4xl font-light text-[var(--ink)]">Forgot password?</h1>
            <p class="mt-2 text-sm text-[var(--muted)]">Enter your email and we'll send you a reset link.</p>
        </div>

        <div class="glass rounded-2xl p-8 shadow-[var(--shadow-flat)]">

            @if (session('status'))
                <div class="mb-6 rounded-lg px-4 py-3 text-sm text-[var(--ink)]" style="border: 1px solid var(--color-accent-border); background-color: var(--color-accent-dim);">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-[var(--error)]/30 bg-[var(--error)]/10 px-4 py-3">
                    @foreach ($errors->all() as $error)
                        <p class="text-sm text-[var(--error)]">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" novalidate>
                @csrf

                <div class="mb-6">
                    <label for="email" class="field-label">Email address</label>
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
                    @error('email')<p class="field-error mt-1">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="btn-primary w-full justify-center">Send Reset Link</button>
            </form>

        </div>

        <p class="mt-6 text-center text-sm text-[var(--muted)]">
            Remembered it?
            <a href="{{ route('login') }}" class="text-[var(--ink)] hover:text-[var(--color-accent)] transition-colors">Sign in</a>
        </p>

    </div>
</div>
@endsection
