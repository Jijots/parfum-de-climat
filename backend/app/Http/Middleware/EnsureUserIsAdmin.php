<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: EnsureUserIsAdmin
 *
 * Guards admin-only routes (fragrance CRUD, note profile management,
 * import triggers, and the Blade admin panel). Any authenticated user
 * without the 'admin' role is rejected with a 403 response.
 *
 * Registration: added to bootstrap/app.php as the named alias 'admin'
 * so routes can be protected with ->middleware('admin').
 *
 * Response strategy:
 *   - API / JSON requests → JSON 403  (preserves Flutter API behaviour)
 *   - Web / Blade requests → abort(403) → renders errors/403.blade.php
 *
 * Usage in routes/api.php:
 *   Route::middleware(['auth:sanctum', 'admin'])->group(...)
 *
 * Usage in routes/web.php:
 *   Route::middleware(['auth', 'admin'])->group(...)
 */
class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isAdmin()) {
            // API and programmatic clients receive a structured JSON 403.
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'This action requires administrator privileges.',
                ], Response::HTTP_FORBIDDEN);
            }

            // Browser requests get a standard 403 abort. Laravel will render
            // resources/views/errors/403.blade.php if it exists.
            abort(Response::HTTP_FORBIDDEN, 'This action requires administrator privileges.');
        }

        return $next($request);
    }
}
