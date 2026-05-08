<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WeatherLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Controller: Admin\WeatherLogController
 *
 * Read-only viewer for weather recommendation logs.
 * No write operations are exposed — this is an audit/analytics view.
 *
 * Each log entry captures the GPS coordinates, live weather conditions,
 * the full Python engine response payload, and (optionally) which fragrance
 * the user chose to wear from the top-3 recommendations.
 *
 * Routes:
 *   GET /admin/logs → index()
 */
class WeatherLogController extends Controller
{
    /**
     * Display a paginated, filterable list of weather logs.
     *
     * GET /admin/logs
     *
     * Filters:
     *   ?user      — partial match on user name or email
     *   ?season    — exact match on season enum (spring / summer / autumn / winter)
     *   ?condition — partial match on weather_condition string (e.g. "Rain", "Clear")
     *
     * Pagination: 30 per page, newest first. Query string is preserved so filters
     * survive page navigation.
     */
    public function index(Request $request): View
    {
        $logs = WeatherLog::with([
                'user:id,name,email',
                'chosenFragrance:id,name,brand',
            ])
            ->when($request->filled('user'), function ($q) use ($request) {
                $q->whereHas('user', fn ($u) =>
                    $u->where('name', 'like', '%' . $request->user . '%')
                      ->orWhere('email', 'like', '%' . $request->user . '%')
                );
            })
            ->when($request->filled('season'), fn ($q) =>
                $q->where('season', $request->season)
            )
            ->when($request->filled('condition'), fn ($q) =>
                $q->where('weather_condition', 'like', '%' . $request->condition . '%')
            )
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.logs.index', compact('logs'));
    }
}
