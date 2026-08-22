<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Google OAuth
    |--------------------------------------------------------------------------
    |
    | Credentials come from a Web application OAuth client in Google Cloud
    | Console. The redirect URI registered there must match GOOGLE_REDIRECT_URI
    | exactly, including scheme and trailing path — Google rejects anything else
    | rather than warning about it.
    |
    */
    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI', env('APP_URL') . '/auth/google/callback'),
    ],


    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenWeatherMap
    |--------------------------------------------------------------------------
    | Real-time weather data provider. Free tier: 60 calls/minute.
    | Get a key at https://openweathermap.org/api
    */
    'openweathermap' => [
        'api_key'  => env('OPENWEATHERMAP_API_KEY'),
        'base_url' => env('OPENWEATHERMAP_BASE_URL', 'https://api.openweathermap.org/data/2.5'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Python Recommendation Engine
    |--------------------------------------------------------------------------
    | Supports two modes:
    |   'subprocess'   → Python is spawned per-request via proc_open()
    |   'microservice' → Engine runs as a persistent FastAPI server
    |
    | Subprocess mode:
    |   ENGINE_SCRIPT_PATH should be the absolute path to recommendation_engine.py
    |
    | Microservice mode:
    |   Start engine: python engine/recommendation_engine.py --serve
    |   Set ENGINE_MODE=microservice and ENGINE_MICROSERVICE_URL
    */
    'engine' => [
        'mode'             => env('ENGINE_MODE', 'subprocess'),
        // Debian/Ubuntu Docker images only ship 'python3', not 'python'.
        // Default to 'python3' so subprocess mode works without setting the env var.
        'python_bin'       => env('PYTHON_EXECUTABLE', 'python3'),
        'script_path'      => env('ENGINE_SCRIPT_PATH', base_path('../engine/recommendation_engine.py')),
        'microservice_url' => env('ENGINE_MICROSERVICE_URL', 'http://127.0.0.1:8001'),
        'timeout'          => env('ENGINE_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | PerfumAPI (Fragrantica scraper)
    |--------------------------------------------------------------------------
    | Used ONLY during fragrance import (php artisan fragrances:import).
    | Never called at request time — our MySQL DB is the runtime source of truth.
    */
    'perfumapi' => [
        'base_url'        => env('PERFUMAPI_BASE_URL', 'https://perfumapi-frontend.onrender.com'),
        'timeout'         => env('PERFUMAPI_TIMEOUT', 30),
        'connect_timeout' => env('PERFUMAPI_CONNECT_TIMEOUT', 10),
    ],

];
