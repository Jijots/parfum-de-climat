@extends('layouts.app')

@section('title', 'Server Error')

@section('content')

    <div class="flex min-h-[calc(100vh-3.5rem)] items-center justify-center px-6 py-24">
        <div class="text-center max-w-md mx-auto">

            <p class="font-display text-[clamp(5rem,20vw,9rem)] font-light leading-none text-[var(--muted)] opacity-20 select-none">
                500
            </p>

            <h1 class="font-display text-[clamp(1.5rem,4vw,2.25rem)] font-light text-[var(--ink)] mt-2 mb-4">
                Something went wrong.
            </h1>

            <p class="text-base font-light text-[var(--muted)] leading-relaxed mb-8">
                The server encountered an unexpected error.
                Please try again in a moment.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ url('/') }}" class="btn-primary">Go Home</a>
                <button onclick="window.history.back()" class="btn-ghost">Go Back</button>
            </div>

        </div>
    </div>

@endsection
