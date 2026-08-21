<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // ── Trusted proxies (Cloudflare + Railway) ────────────────────────
        // Trust all upstream proxies so Laravel reads X-Forwarded-Proto and
        // generates signed URLs (email verification, password reset) with
        // the correct https:// scheme instead of http://.
        $middleware->trustProxies(at: '*');

        // ── API middleware stack ───────────────────────────────────────────
        // Append Sanctum's stateful middleware so the session-based cookie
        // auth works for the Blade admin panel (SPA mode).
        $middleware->statefulApi();

        // ── Named middleware aliases ───────────────────────────────────────
        // 'admin' → guards routes that require the admin role.
        // API usage:  Route::middleware(['auth:sanctum', 'admin'])
        // Web usage:  Route::middleware(['auth', 'admin'])
        // ── Inertia ────────────────────────────────────────────────────────
        // Appended to the web group only. Routes still returning Blade views
        // are unaffected — Inertia only takes over for responses built with
        // Inertia::render(), so pages can be ported one at a time.
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);

        // ── Auth redirect target ───────────────────────────────────────────
        // By default Laravel's 'auth' middleware redirects unauthenticated
        // web requests to /login, which doesn't exist in this project.
        // Admin web routes redirect to /admin/login instead.
        $middleware->redirectGuestsTo(function (Request $request): string {
            if ($request->is('admin*')) {
                return route('admin.login');
            }
            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // ── JSON error responses ───────────────────────────────────────────
        // Ensures unauthenticated API requests get a clean JSON 401 response
        // rather than a redirect to /login (which makes no sense for Flutter).
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated. Please log in to continue.',
                ], Response::HTTP_UNAUTHORIZED);
            }
        });

        // Ensures validation errors return JSON for API requests.
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors'  => $e->errors(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        });

        // Ensures 404s return JSON for API requests.
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'The requested resource was not found.',
                ], Response::HTTP_NOT_FOUND);
            }
        });
    })->create();
