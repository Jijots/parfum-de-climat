@extends('layouts.app')

@section('title', 'Verify Your Email')

@section('content')
<div class="flex min-h-[calc(100vh-3.5rem)] items-center justify-center px-6 py-12">
    <div class="w-full max-w-md">

        <div class="mb-8 text-center">
            <h1 class="font-display text-4xl font-light text-[var(--ink)]">Verify your email</h1>
            <p class="mt-2 text-sm text-[var(--muted)]">We sent a verification link to {{ auth()->user()->email }}.</p>
        </div>

        <div class="glass rounded-2xl p-8 shadow-[var(--shadow-flat)]">
            @if (session('status') === 'verification-link-sent')
                <div class="mb-6 rounded-lg px-4 py-3 text-sm text-[var(--ink)]" style="border: 1px solid var(--color-accent-border); background-color: var(--color-accent-dim);">
                    A fresh verification email has been sent.
                </div>
            @endif

            <p class="text-sm leading-6 text-[var(--muted)]">
                Verify your email before using saved account features like recommendation history and account-level tracking. Your guest wardrobe has already been merged into this account.
            </p>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <form method="POST" action="{{ route('verification.send') }}" class="flex-1">
                    @csrf
                    <button type="submit" class="btn-primary w-full justify-center">Resend verification email</button>
                </form>

                <a href="{{ route('app') }}" class="btn-ghost w-full justify-center sm:w-auto">Continue browsing</a>
            </div>
        </div>
    </div>
</div>
@endsection
