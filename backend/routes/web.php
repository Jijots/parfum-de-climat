<?php

/**
 * routes/web.php — Parfum de Climat
 * ─────────────────────────────────────────────────────────────────────────────
 * Web routes for the Blade UI. Separate from routes/api.php which serves the
 * Flutter mobile app via Sanctum token auth.
 *
 * Auth strategy for the admin panel:
 *   - Session-based via the default 'web' guard (Auth::attempt())
 *   - The 'auth' middleware ensures sessions; 'admin' checks the role flag
 *   - EnsureUserIsAdmin returns abort(403) for web requests (not JSON)
 *   - Unauthenticated web users are redirected to admin.login
 *     (configured in bootstrap/app.php via redirectGuestsTo)
 *
 * Route naming convention:
 *   admin.dashboard
 *   admin.fragrances.index / .create / .store / .edit / .update / .destroy
 *   admin.note-profiles.index / .create / .store / .edit / .update / .destroy
 *   admin.logs.index
 *   admin.login / admin.login.store / admin.logout
 *   landing
 */

use App\Http\Controllers\Admin;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Web;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

/* ── Public ──────────────────────────────────────────────────────────────── */

Route::get('/', [LandingController::class, 'index'])->name('landing');

/* ── Public User Auth ────────────────────────────────────────────────────── */

// Guest-only auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [Web\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [Web\AuthController::class, 'login'])->middleware('throttle:6,1')->name('login.store');
    Route::get('/register', [Web\AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [Web\AuthController::class, 'register'])->middleware('throttle:4,1')->name('register.store');

    // Social sign-in. Throttled because each callback hits Google.
    Route::get('/auth/{provider}/redirect', [Web\OAuthController::class, 'redirect'])
        ->middleware('throttle:10,1')->name('oauth.redirect');
    Route::get('/auth/{provider}/callback', [Web\OAuthController::class, 'callback'])
        ->middleware('throttle:10,1')->name('oauth.callback');

    // Password reset
    Route::get('/forgot-password', [Web\PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [Web\PasswordResetController::class, 'sendResetLink'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [Web\PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [Web\PasswordResetController::class, 'resetPassword'])->middleware('throttle:5,1')->name('password.update');
});

Route::post('/logout', [Web\AuthController::class, 'logout'])->name('logout')->middleware('auth');

/* ── Public App (guests + auth users) ───────────────────────────────────── */
// Guests get recommendations sampled from the full catalog.
// Authenticated users get recommendations from their personal wardrobe.
// WeatherLog is only persisted for authenticated users.

Route::get('/app', [Web\RecommendController::class, 'index'])->name('app');
Route::post('/app/recommend', [Web\RecommendController::class, 'recommend'])->middleware('throttle:20,1')->name('app.recommend');

Route::get('/browse', [Web\BrowseController::class, 'index'])->name('browse');
Route::get('/browse/search', [Web\BrowseController::class, 'search'])->middleware('throttle:30,1')->name('browse.search');
Route::get('/wardrobe', [Web\WardrobeController::class, 'index'])->name('wardrobe');
Route::post('/wardrobe/{fragrance}/toggle', [Web\WardrobeController::class, 'toggle'])->name('wardrobe.toggle');
Route::patch('/wardrobe/{fragrance}/favorite', [Web\WardrobeController::class, 'toggleFavorite'])->name('wardrobe.favorite');

Route::get('/fragrances/{fragrance}', [Web\FragranceController::class, 'show'])->name('fragrance.show');

/* ── Auth-only ────────────────────────────────────────────────────────────── */
// History and verification flows require an authenticated account.

Route::middleware(['auth'])->group(function () {
    Route::get('/profile',   [Web\ProfileController::class, 'show'])->name('profile');
    Route::patch('/profile', [Web\ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', fn (Request $request) => Inertia::render('Auth/VerifyEmail', [
        'email' => $request->user()?->email,
        'urls'  => [
            'resend' => route('verification.send'),
            'logout' => route('logout'),
        ],
    ]))->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('app')->with('status', 'email-verified');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('app');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1')->name('verification.send');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/app/recommend/{log}/choose', [Web\RecommendController::class, 'choose'])->name('app.choose');
    Route::get('/history', [Web\HistoryController::class, 'index'])->name('history');
});

/* ── Admin ───────────────────────────────────────────────────────────────── */

Route::prefix('admin')->name('admin.')->group(function () {

    /*
     * Authentication routes (guest-only).
     * Authenticated users hitting these are redirected to the dashboard.
     */
    Route::middleware('guest')->group(function () {
        Route::get('/login', [Admin\AuthController::class, 'showLogin'])
            ->name('login');

        Route::post('/login', [Admin\AuthController::class, 'login'])
            ->middleware('throttle:5,1')
            ->name('login.store');
    });

    // Logout is accessible to authenticated users only (prevents CSRF logout attacks).
    Route::post('/logout', [Admin\AuthController::class, 'logout'])
        ->name('logout')
        ->middleware('auth');

    /*
     * Protected admin routes.
     * 'auth'  — verifies active session (web guard)
     * 'admin' — verifies the user has the admin role
     */
    Route::middleware(['auth', 'admin'])->group(function () {

        /* Dashboard */
        Route::get('/', [Admin\DashboardController::class, 'index'])
            ->name('dashboard');

        /*
         * Fragrances CRUD.
         * Excludes 'show' — the edit view serves as the detail page.
         * Soft-deleted fragrances are accessible via the edit route (controller
         * calls withTrashed() explicitly to handle restore/force-delete actions).
         */
        Route::resource('fragrances', Admin\FragranceController::class)
            ->except(['show']);

        /*
         * Note Climate Profiles CRUD.
         * Route model binding uses the default key (id).
         * 'show' excluded — form.blade.php doubles as create + edit.
         */
        Route::resource('note-profiles', Admin\NoteProfileController::class)
            ->except(['show']);

        /* Weather Logs — read-only; no create/edit/delete for the admin panel */
        Route::get('/logs', [Admin\WeatherLogController::class, 'index'])
            ->name('logs.index');
    });
});
