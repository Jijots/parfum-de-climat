<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
