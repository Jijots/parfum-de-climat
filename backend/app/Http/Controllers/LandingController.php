<?php

namespace App\Http\Controllers;

use App\Services\CatalogCache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

use Illuminate\Contracts\View\View;

/**
 * Controller: LandingController
 *
 * Renders the public-facing landing page at GET /.
 * No authentication required.
 */
class LandingController extends Controller
{
    /**
     * Display the landing page.
     *
     * The view (welcome.blade.php) is a full-page marketing presentation
     * of the Parfum de Climat concept — hero, feature cards, CTA.
     */
    public function index(CatalogCache $cache)
    {
        // Counted through the catalog cache: the landing page is the most
        // requested route in the app, and three COUNT(*) queries against a
        // 24k-row table in another region is not a sensible cost for figures
        // that change only when the catalogue is re-imported.
        $stats = $cache->browse('landing-stats', 'all', 0, fn () => [
            'fragrances' => DB::table('fragrances')->where('is_active', true)->count(),
            'brands'     => DB::table('fragrances')->where('is_active', true)->distinct()->count('brand'),
            'profiles'   => DB::table('note_climate_profiles')->count(),
        ]);

        return Inertia::render('Welcome', [
            'stats' => $stats,
            'urls'  => [
                'app'      => route('app'),
                'browse'   => route('browse'),
                'register' => route('register'),
                'login'    => route('login'),
            ],
        ]);
    }
}
