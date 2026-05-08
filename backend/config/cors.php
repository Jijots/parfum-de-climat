<?php

/**
 * CORS Configuration
 *
 * Controls Cross-Origin Resource Sharing headers for the Parfum de Climat API.
 *
 * During development, '*' is used for allowed_origins so the Flutter web debug
 * client and the Tailwind admin panel can connect without friction.
 *
 * BEFORE PRODUCTION:
 *  - Replace '*' in allowed_origins with your actual domains.
 *  - Flutter mobile apps do not make browser CORS requests (they use the
 *    http package directly), so CORS only matters for:
 *      a) The Blade/Tailwind admin panel (same-origin in production)
 *      b) Any future web version of the Flutter app
 *
 * The /api/* path prefix is handled by the HandleCors middleware which is
 * registered globally in Laravel 11's bootstrap/app.php.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Development: allow any origin.
    // Production: replace with ['https://yourdomain.com', 'https://admin.yourdomain.com']
    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Credentials (cookies) are needed for the Blade admin panel session auth.
    // Must be false when allowed_origins is '*'. Set to true once origins are
    // restricted to specific domains in production.
    'supports_credentials' => false,

];
