@extends('layouts.app')

@section('title', 'Page Not Found')

@section('content')

    <div class="flex min-h-[calc(100vh-6rem)] md:min-h-[calc(100vh-4rem)] items-center justify-center px-6 py-24">
        <div class="text-center max-w-md mx-auto">

            <p class="font-display text-[clamp(5rem,20vw,9rem)] font-light leading-none text-[var(--color-accent)] opacity-30 select-none">
                404
            </p>

            <h1 class="font-display text-[clamp(1.5rem,4vw,2.25rem)] font-light text-[var(--ink)] mt-2 mb-4">
                Page not found.
            </h1>

            <p class="text-base font-light text-[var(--muted)] leading-relaxed mb-8">
                The page you're looking for doesn't exist or has been moved.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ url('/') }}" class="btn-primary">Go Home</a>
                <a href="{{ route('admin.login') }}" class="btn-ghost">Admin Panel</a>
            </div>

        </div>
    </div>

@endsection
