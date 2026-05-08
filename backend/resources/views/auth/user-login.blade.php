@extends('layouts.app')

@section('title', 'Sign In')

@section('content')
<div class="flex min-h-[calc(100vh-3.5rem)] items-center justify-center px-6 py-12">
    <div class="w-full max-w-sm">

        <div class="mb-8 text-center">
            <h1 class="font-display text-4xl font-light text-[var(--ink)]">Welcome back</h1>
            <p class="mt-2 text-sm text-[var(--muted)]">Sign in to your Parfum de Climat account</p>
        </div>

        <div class="glass rounded-2xl p-8 shadow-[var(--shadow-flat)]">
            @if (session('status') === 'email-verified')
                <div class="mb-6 rounded-lg px-4 py-3 text-sm text-[var(--ink)]" style="border: 1px solid var(--color-accent-border); background-color: var(--color-accent-dim);">
                    Your email has been verified. You can now use all account features.
                </div>
            @endif

            @if (session('status') && session('status') !== 'email-verified')
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

            <form method="POST" action="{{ route('login.store') }}" novalidate>
                @csrf

                <div class="mb-5">
                    <label for="email" class="field-label">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="input-field mt-1 w-full @error('email') border-[var(--error)] @enderror" autocomplete="email" autofocus required>
                    @error('email')<p class="field-error mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="mb-6">
                    <div class="flex items-center justify-between">
                        <label for="password" class="field-label">Password</label>
                        <a href="{{ route('password.request') }}" class="text-xs text-[var(--muted)] hover:text-[var(--color-accent)] transition-colors">Forgot password?</a>
                    </div>
                    <div class="relative mt-1" x-data="{ show: false }">
                        <input id="password" :type="show ? 'text' : 'password'" name="password" class="input-field w-full pr-10 @error('password') border-[var(--error)] @enderror" autocomplete="current-password" required>
                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-[var(--muted)] hover:text-[var(--ink)] transition-colors" tabindex="-1" :aria-label="show ? 'Hide password' : 'Show password'">
                            <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <svg x-show="show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                        </button>
                    </div>
                    @error('password')<p class="field-error mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="mb-6 flex items-center gap-3">
                    <input id="remember" type="checkbox" name="remember" class="h-4 w-4 rounded border-[var(--hairline)] bg-[var(--bg)] accent-[var(--color-accent)] cursor-pointer">
                    <label for="remember" class="text-sm text-[var(--muted)] cursor-pointer select-none">Keep me signed in</label>
                </div>

                <button type="submit" class="btn-primary w-full justify-center">Sign In</button>

            </form>

        </div>

        <p class="mt-6 text-center text-sm text-[var(--muted)]">
            Don't have an account?
            <a href="{{ route('register') }}" class="text-[var(--ink)] hover:text-[var(--color-accent)] transition-colors">Create one</a>
        </p>

        <p class="mt-2 text-center text-xs text-[var(--muted)]">
            Accounts must verify their email before saved history and account-level tracking are enabled.
        </p>

    </div>
</div>
@endsection
