<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fragrance;
use App\Models\FragranceNote;
use App\Models\NoteClimateProfile;
use App\Models\WeatherLog;
use Illuminate\Contracts\View\View;

/**
 * Controller: Admin\DashboardController
 *
 * Renders the admin overview page with key statistics and a preview of the
 * most recent weather-recommendation logs.
 *
 * Routes:
 *   GET /admin  → index()
 */
class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     *
     * GET /admin
     *
     * Stats cards:
     *   total_fragrances — all active (non-soft-deleted) entries in the catalogue
     *   unmapped_notes   — notes with no NoteClimateProfile assigned; the engine
     *                      cannot score fragrances whose notes are all unmapped
     *   note_profiles    — total NoteClimateProfile records seeded/created
     *   total_logs       — total weather recommendation requests logged
     *
     * Recent logs: latest 10, with user and chosen fragrance eager-loaded to
     * avoid N+1 queries on the preview table.
     */
    public function index(): View
    {
        $stats = [
            'total_fragrances' => Fragrance::count(),
            'unmapped_notes'   => FragranceNote::whereNull('note_profile_id')->count(),
            'note_profiles'    => NoteClimateProfile::count(),
            'total_logs'       => WeatherLog::count(),
        ];

        $recentLogs = WeatherLog::with([
                'user:id,name,email',
                'chosenFragrance:id,name,brand',
            ])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentLogs'));
    }
}
