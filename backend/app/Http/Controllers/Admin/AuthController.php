<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller: Admin\AuthController
 *
 * Session-based authentication for the Blade admin panel.
 * Uses the default 'web' guard (session driver, users table).
 *
 * This is entirely separate from the Sanctum token auth used by the Flutter
 * API — do not mix the two systems.
 *
 * Routes:
 *   GET  /admin/login  → showLogin()
 *   POST /admin/login  → login()
 *   POST /admin/logout → logout()
 */
class AuthController extends Controller
{
    /**
     * Show the admin login form.
     *
     * GET /admin/login
     * Protected by 'guest' middleware — authenticated users are redirected away.
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Attempt to authenticate the admin user.
     *
     * POST /admin/login
     *
     * After verifying credentials, an additional role check ensures that only
     * users with isAdmin() === true can access the panel. A valid email/password
     * pair belonging to a non-admin user is rejected with a clear message to
     * avoid leaking role information through generic "wrong credentials" errors.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request): RedirectResponse
    {
        // Validate input shape before attempting auth.
        $credentials = $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        // Attempt session-based authentication.
        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withErrors(['email' => 'These credentials do not match our records.'])
                ->withInput($request->only('email'));
        }

        // Credentials matched — but verify the user has admin privileges.
        // If not, log them back out immediately to avoid a partial-auth state.
        if (! Auth::user()->isAdmin()) {
            Auth::logout();
            $request->session()->invalidate();

            return back()
                ->withErrors(['email' => 'Your account does not have administrator access.'])
                ->withInput($request->only('email'));
        }

        // Regenerate the session ID on login to prevent session fixation attacks.
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Sign the admin user out and invalidate the session.
     *
     * POST /admin/logout
     * Protected by 'auth' middleware.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        // Invalidate the session and regenerate the CSRF token.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
