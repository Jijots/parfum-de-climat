<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\WeatherLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $logs = WeatherLog::where('user_id', $request->user()->id)
            ->with('chosenFragrance')
            ->orderByDesc('created_at')
            ->paginate(15);

        return Inertia::render('History', [
            'logs' => collect($logs->items())->map(fn ($log) => [
                'id'                  => $log->id,
                'location_name'       => $log->location_name,
                'temperature_celsius' => $log->temperature_celsius,
                'weather_condition'   => $log->weather_condition,
                'created_at'          => $log->created_at->format('j M Y, H:i'),
                'chosen_name'         => $log->chosenFragrance?->name,
                // Decoded here so the page never has to parse JSON. Only the
                // top 3 are kept — the stored payload holds the whole scored
                // collection, which can be hundreds of entries per log.
                'results' => collect($log->engine_response_payload ?? [])
                    ->take(3)
                    ->map(fn ($r) => [
                        'name'  => $r['name']  ?? '?',
                        'brand' => $r['brand'] ?? '?',
                        'score' => $r['score'] ?? 0,
                    ])->values(),
            ])->values(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page'    => $logs->lastPage(),
                'total'        => $logs->total(),
            ],
            'urls' => [
                'history' => route('history'),
                'app'     => route('app'),
            ],
        ]);
    }
}
