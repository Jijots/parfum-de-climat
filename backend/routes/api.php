<?php

// All routes are versioned under /api/v1/.
// Authentication: Laravel Sanctum — Flutter app sends Authorization: Bearer {token}.

use App\Http\Controllers\Api\Admin\FragranceAdminController;
use App\Http\Controllers\Api\Admin\NoteClimateProfileController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FragranceController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\UserCollectionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Authentication (Public)
    // No auth middleware — these are the entry points for the Flutter app.
    // Throttled to prevent brute-force and account enumeration.
    Route::prefix('auth')->middleware('throttle:6,1')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])
             ->name('auth.register');
        Route::post('/login', [AuthController::class, 'login'])
             ->name('auth.login');
    });

    // Authenticated Routes
    Route::middleware('auth:sanctum')->group(function () {

        // Auth (requires token)
        Route::prefix('auth')->group(function () {
            Route::get('/me', [AuthController::class, 'me'])
                 ->name('auth.me');
            Route::post('/logout', [AuthController::class, 'logout'])
                 ->name('auth.logout');
        });

        // Fragrance Catalog (read-only, any authenticated user)
        Route::prefix('fragrances')->group(function () {
            Route::get('/', [FragranceController::class, 'index'])
                 ->name('fragrances.index');
            Route::get('/{fragrance}', [FragranceController::class, 'show'])
                 ->name('fragrances.show')
                 ->whereNumber('fragrance');
        });

        // User Collection (wardrobe)
        // The {fragrance} segment refers to the fragrance_id, not the
        // collection entry id — keeping URLs semantically meaningful.
        Route::prefix('collection')->group(function () {
            Route::get('/', [UserCollectionController::class, 'index'])
                 ->name('collection.index');
            Route::post('/', [UserCollectionController::class, 'store'])
                 ->name('collection.store');
            Route::put('/{fragrance}', [UserCollectionController::class, 'update'])
                 ->name('collection.update')
                 ->whereNumber('fragrance');
            Route::delete('/{fragrance}', [UserCollectionController::class, 'destroy'])
                 ->name('collection.destroy')
                 ->whereNumber('fragrance');
        });

        // Recommendations
        // IMPORTANT: /history must be declared BEFORE /{log}/choose to prevent
        // Laravel from matching "history" as a log ID parameter.
        Route::prefix('recommend')->group(function () {
            Route::get('/history', [RecommendationController::class, 'history'])
                 ->name('recommend.history');
            Route::post('/', [RecommendationController::class, 'recommend'])
                 ->middleware('throttle:20,1')
                 ->name('recommend');
            Route::patch('/{log}/choose', [RecommendationController::class, 'choose'])
                 ->name('recommend.choose')
                 ->whereNumber('log');
        });

        // Admin Routes
        // Double-protected: auth:sanctum AND the 'admin' role middleware.
        Route::middleware('admin')->prefix('admin')->group(function () {

            // Fragrance Catalog Management
            Route::prefix('fragrances')->group(function () {
                // IMPORTANT: Static sub-routes ('unmapped-notes', 'notes/{id}/map')
                // must be declared BEFORE wildcard routes ('/{fragrance}') to ensure
                // correct route matching order.
                Route::get('/unmapped-notes', [FragranceAdminController::class, 'unmappedNotes'])
                     ->name('admin.fragrances.unmapped-notes');
                Route::post('/notes/{note}/map', [FragranceAdminController::class, 'mapNote'])
                     ->name('admin.fragrances.notes.map')
                     ->whereNumber('note');

                Route::get('/', [FragranceAdminController::class, 'index'])
                     ->name('admin.fragrances.index');
                Route::post('/', [FragranceAdminController::class, 'store'])
                     ->name('admin.fragrances.store');
                Route::put('/{fragrance}', [FragranceAdminController::class, 'update'])
                     ->name('admin.fragrances.update')
                     ->whereNumber('fragrance');
                Route::delete('/{fragrance}', [FragranceAdminController::class, 'destroy'])
                     ->name('admin.fragrances.destroy')
                     ->whereNumber('fragrance');
            });

            // Note Climate Profile Management
            Route::prefix('notes')->group(function () {
                Route::get('/', [NoteClimateProfileController::class, 'index'])
                     ->name('admin.notes.index');
                Route::post('/', [NoteClimateProfileController::class, 'store'])
                     ->name('admin.notes.store');
                Route::get('/{profile}', [NoteClimateProfileController::class, 'show'])
                     ->name('admin.notes.show')
                     ->whereNumber('profile');
                Route::put('/{profile}', [NoteClimateProfileController::class, 'update'])
                     ->name('admin.notes.update')
                     ->whereNumber('profile');
                Route::delete('/{profile}', [NoteClimateProfileController::class, 'destroy'])
                     ->name('admin.notes.destroy')
                     ->whereNumber('profile');
            });
        });
    });
});
