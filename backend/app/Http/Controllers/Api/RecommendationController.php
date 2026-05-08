<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecommendRequest;
use App\Http\Resources\WeatherLogResource;
use App\Models\UserCollection;
use App\Models\WeatherLog;
use App\Services\EngineService;
use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RecommendationController extends Controller
{
    public function __construct(
        private readonly WeatherService $weatherService,
        private readonly EngineService  $engineService,
    ) {}

    public function recommend(RecommendRequest $request): JsonResponse
    {
        $user = $request->user();

        // Fetch current weather
        try {
            $weather = $this->weatherService->getCurrentWeather(
                (float) $request->latitude,
                (float) $request->longitude
            );
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        // Derive the current season from the user's timezone
        $season = $this->weatherService->deriveSeason($user->timezone);

        // Load only fragrances with mapped note profiles — unmapped ones can't be scored.
        $collectionItems = UserCollection::where('user_id', $user->id)
            ->with([
                'fragrance' => fn ($q) => $q->where('is_active', true),
                'fragrance.mappedNotes.profile',
            ])
            ->get()
            ->filter(fn ($item) => $item->fragrance !== null)
            ->filter(fn ($item) => $item->fragrance->mappedNotes->isNotEmpty());

        if ($collectionItems->isEmpty()) {
            return response()->json([
                'message' => 'Your collection has no fragrances with climate profiles assigned. ' .
                             'Add fragrances to your wardrobe and ensure their notes are mapped.',
                'recommendations' => [],
            ], Response::HTTP_OK);
        }

        // Serialize collection into the shape the Python engine expects:
        // Each item: { fragrance_id, name, is_favorite, notes: [{name, layer, min_temp, max_temp, ...}] }
        $engineCollection = $collectionItems->map(function ($item) {
            return [
                'fragrance_id' => $item->fragrance_id,
                'name'         => $item->fragrance->name,
                'brand'        => $item->fragrance->brand,
                'is_favorite'  => $item->is_favorite,
                'sillage'      => $item->fragrance->sillage,
                'notes'        => $item->fragrance->mappedNotes->map(fn ($note) => [
                    'name'                => $note->profile->name,
                    'layer'               => $note->layer,
                    'note_family'         => $note->profile->note_family,
                    'min_temp_celsius'    => $note->profile->min_temp_celsius,
                    'max_temp_celsius'    => $note->profile->max_temp_celsius,
                    'humidity_preference' => $note->profile->humidity_preference,
                    'ideal_seasons'       => $note->profile->ideal_seasons,
                    'climate_weight'      => $note->profile->climate_weight,
                ])->values()->toArray(),
            ];
        })->values()->toArray();

        // Compose the full engine request payload
        $engineRequestPayload = [
            'weather' => [
                'temperature_celsius' => $weather['temperature_celsius'],
                'humidity_percent'    => $weather['humidity_percent'],
                'condition'           => $weather['weather_condition'],
                'season'              => $season,
            ],
            'collection' => $engineCollection,
        ];

        // Call the Python engine (subprocess or microservice, depending on ENGINE_MODE).
        try {
            $engineResponsePayload = $this->engineService->run($engineRequestPayload);
        } catch (\RuntimeException $e) {
            Log::error('[RecommendationController] Engine call failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'The recommendation engine is temporarily unavailable: ' . $e->getMessage(),
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        // Persist the weather log for history and audit.
        $log = WeatherLog::create([
            'user_id'                 => $user->id,
            'latitude'                => $request->latitude,
            'longitude'               => $request->longitude,
            'location_name'           => $weather['location_name'],
            'temperature_celsius'     => $weather['temperature_celsius'],
            'humidity_percent'        => $weather['humidity_percent'],
            'weather_condition'       => $weather['weather_condition'],
            'season'                  => $season,
            'raw_weather_payload'     => $weather['raw_payload'],
            'engine_request_payload'  => $engineRequestPayload,
            'engine_response_payload' => $engineResponsePayload,
        ]);

        // Return top 3 to Flutter.
        $top3 = array_slice($engineResponsePayload, 0, 3);

        return response()->json([
            'log_id'          => $log->id,
            'weather'         => [
                'temperature_celsius' => $weather['temperature_celsius'],
                'humidity_percent'    => $weather['humidity_percent'],
                'condition'           => $weather['weather_condition'],
                'season'              => $season,
                'location'            => $weather['location_name'],
            ],
            'recommendations' => $top3,
        ]);
    }

    public function choose(Request $request, int $logId): JsonResponse
    {
        $request->validate([
            'fragrance_id' => ['required', 'integer', 'exists:fragrances,id'],
        ]);

        $log = WeatherLog::where('id', $logId)
                         ->where('user_id', $request->user()->id)
                         ->first();

        if (! $log) {
            return response()->json([
                'message' => 'Recommendation log not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $log->update(['chosen_fragrance_id' => $request->fragrance_id]);

        return response()->json([
            'message' => 'Your choice has been recorded. Enjoy your fragrance.',
        ]);
    }

    public function history(Request $request): AnonymousResourceCollection
    {
        $logs = WeatherLog::where('user_id', $request->user()->id)
            ->with('chosenFragrance')
            ->orderByDesc('created_at')
            ->paginate(15);

        return WeatherLogResource::collection($logs);
    }

}
