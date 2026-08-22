<?php

namespace App\Providers;

use App\Database\NeonPostgresConnector;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Replace the built-in pgsql connector with one that supports the
        // Neon endpoint-ID workaround for older libpq (no SNI support).
        $this->app->bind('db.connector.pgsql', NeonPostgresConnector::class);
    }

    /**
     * Bootstrap any application services.
     *
     * Forces HTTPS scheme for all generated URLs when running in production.
     * Required because Railway (and most cloud platforms) terminate TLS at
     * their load balancer and forward requests to the container over plain HTTP.
     * Without this, Laravel's URL generator sees an HTTP request and produces
     * http:// asset URLs, which browsers block as Mixed Content on HTTPS pages.
     */
    public function boot(): void
    {
        // Vite mints the per-request CSP nonce and stamps it on the script and
        // style tags it injects — including the inline HMR snippet that
        // @viteReactRefresh emits in development, which a nonce-based policy
        // would otherwise block.
        //
        // SecurityHeaders reads it back via Vite::cspNonce() so the policy and
        // the tags cannot drift apart. Providers boot before middleware runs,
        // so the value is always set by the time the header is built.
        Vite::useCspNonce();

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
