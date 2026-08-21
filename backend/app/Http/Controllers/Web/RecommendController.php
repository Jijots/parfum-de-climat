<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Fragrance;
use App\Models\UserCollection;
use App\Models\WeatherLog;
use App\Services\EngineService;
use App\Services\SessionWardrobeService;
use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecommendController extends Controller
{
    public function __construct(
        private readonly WeatherService $weatherService,
        private readonly EngineService  $engineService,
        private readonly SessionWardrobeService $sessionWardrobe,
    ) {}

    public function index(Request $request)
    {
        return Inertia::render('Recommend', [
            'isGuest' => ! $request->user(),
            'urls' => [
                'recommend' => route('app.recommend'),
                'choose'    => url('/app/recommend'),
                'browse'    => route('browse'),
                'fragrance' => url('/fragrances'),
                'login'     => route('login'),
                'register'  => route('register'),
            ],
            'csrf' => csrf_token(),
        ]);
    }

    public function recommend(Request $request): JsonResponse
    {
        $request->validate([
            'latitude'  => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'city'      => ['nullable', 'string', 'max:100'],
        ]);

        // $user is null for unauthenticated (guest) visitors.
        $user = $request->user();

        /* ── 1. Fetch weather ─────────────────────────────────────────────── */

        try {
            if ($request->filled('city')) {
                $weather = $this->weatherService->getCurrentWeatherByCity($request->city);
            } else {
                $request->validate([
                    'latitude'  => ['required', 'numeric'],
                    'longitude' => ['required', 'numeric'],
                ]);
                $weather = $this->weatherService->getCurrentWeather(
                    (float) $request->latitude,
                    (float) $request->longitude
                );
            }
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        }

        // Use the authenticated user's stored timezone for season derivation;
        // guests default to UTC (Northern Hemisphere assumption).
        $season = $this->weatherService->deriveSeason($user?->timezone ?? 'UTC');

        /* ── 2. Build fragrance collection for the engine ─────────────────── */

        if ($user) {
            /*
             * Authenticated path — personalised wardrobe.
             * Only score fragrances the user has explicitly added.
             * Favourite flag is passed so the engine can apply its boost.
             */
            $collectionItems = UserCollection::where('user_id', $user->id)
                ->with([
                    'fragrance' => fn ($q) => $q->where('is_active', true),
                    'fragrance.mappedNotes.profile',
                ])
                ->get()
                ->filter(fn ($item) => $item->fragrance !== null)
                ->filter(fn ($item) => $item->fragrance->mappedNotes->isNotEmpty());

            if ($collectionItems->isEmpty()) {
                // Wardrobe is empty — fall back to a gender-filtered random pool
                // so new users still get weather + recommendations immediately.
                $fallbackQuery = Fragrance::where('is_active', true)
                    ->has('mappedNotes')
                    ->with('mappedNotes.profile');

                if ($user->gender && $user->gender !== 'unisex') {
                    $fallbackQuery->whereIn('gender_target', [$user->gender, 'unisex']);
                }

                $fallbackFragrances = $fallbackQuery->inRandomOrder()->limit(150)->get();

                $engineCollection = $fallbackFragrances->map(fn ($fragrance) => [
                    'fragrance_id' => $fragrance->id,
                    'name'         => $fragrance->name,
                    'brand'        => $fragrance->brand,
                    'is_favorite'  => false,
                    'sillage'      => $fragrance->sillage,
                    'notes'        => $fragrance->mappedNotes->map(fn ($note) => [
                        'name'                => $note->profile->name,
                        'layer'               => $note->layer,
                        'note_family'         => $note->profile->note_family,
                        'min_temp_celsius'    => $note->profile->min_temp_celsius,
                        'max_temp_celsius'    => $note->profile->max_temp_celsius,
                        'humidity_preference' => $note->profile->humidity_preference,
                        'ideal_seasons'       => $note->profile->ideal_seasons,
                        'climate_weight'      => $note->profile->climate_weight,
                    ])->values()->toArray(),
                ])->values()->toArray();
            } else {

            $engineCollection = $collectionItems->map(fn ($item) => [
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
            ])->values()->toArray();

            } // end else (wardrobe not empty)

        } else {
            /*
             * Guest path — use the temporary wardrobe stored in the session.
             * If no temporary wardrobe exists yet, fall back to a catalog sample.
             */
            $fragrances = $this->sessionWardrobe->recommendationFragrances($request);

            if ($fragrances->isEmpty() && $this->sessionWardrobe->fragranceIds($request) !== []) {
                $raw = $weather['raw_payload'];
                return response()->json([
                    'message' => 'Your temporary wardrobe has no fragrances with climate profiles yet. Try different fragrances from Browse.',
                    'recommendations' => [],
                    'weather' => [
                        'location'      => $weather['location_name'],
                        'temperature'   => (int) round($weather['temperature_celsius']),
                        'feels_like'    => (int) round($raw['main']['feels_like'] ?? $weather['temperature_celsius']),
                        'temp_min'      => (int) round($raw['main']['temp_min']   ?? $weather['temperature_celsius']),
                        'temp_max'      => (int) round($raw['main']['temp_max']   ?? $weather['temperature_celsius']),
                        'condition'     => $weather['weather_condition'],
                        'description'   => ucfirst($raw['weather'][0]['description'] ?? $weather['weather_condition']),
                        'icon_code'     => $raw['weather'][0]['icon'] ?? null,
                        'humidity'      => $weather['humidity_percent'],
                        'wind_speed'    => round($raw['wind']['speed'] ?? 0, 1),
                        'wind_deg'      => $raw['wind']['deg'] ?? null,
                        'visibility_km' => isset($raw['visibility']) ? round($raw['visibility'] / 1000, 1) : null,
                        'pressure'      => $raw['main']['pressure'] ?? null,
                        'clouds'        => $raw['clouds']['all'] ?? null,
                        'season'        => $season,
                    ],
                ]);
            }

            if ($fragrances->isEmpty()) {
                $fragrances = Fragrance::where('is_active', true)
                    ->has('mappedNotes')
                    ->with('mappedNotes.profile')
                    ->inRandomOrder()
                    ->limit(150)
                    ->get();
            }

            $engineCollection = $fragrances->map(fn ($fragrance) => [
                'fragrance_id' => $fragrance->id,
                'name'         => $fragrance->name,
                'brand'        => $fragrance->brand,
                'is_favorite'  => (bool) ($fragrance->session_is_favorite ?? false),
                'sillage'      => $fragrance->sillage,
                'notes'        => $fragrance->mappedNotes->map(fn ($note) => [
                    'name'                => $note->profile->name,
                    'layer'               => $note->layer,
                    'note_family'         => $note->profile->note_family,
                    'min_temp_celsius'    => $note->profile->min_temp_celsius,
                    'max_temp_celsius'    => $note->profile->max_temp_celsius,
                    'humidity_preference' => $note->profile->humidity_preference,
                    'ideal_seasons'       => $note->profile->ideal_seasons,
                    'climate_weight'      => $note->profile->climate_weight,
                ])->values()->toArray(),
            ])->values()->toArray();
        }

        /* ── 3. Run recommendation engine ─────────────────────────────────── */

        $enginePayload = [
            'weather' => [
                'temperature_celsius' => $weather['temperature_celsius'],
                'humidity_percent'    => $weather['humidity_percent'],
                'condition'           => $weather['weather_condition'],
                'season'              => $season,
            ],
            'collection' => $engineCollection,
        ];

        try {
            $engineResponse = $this->engineService->run($enginePayload);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => 'Recommendation engine unavailable. Please try again shortly.'], 503);
        }

        /* ── 4. Persist WeatherLog (authenticated users only) ─────────────── */

        $logId = null;

        if ($user && $user->hasVerifiedEmail()) {
            $log = WeatherLog::create([
                'user_id'                 => $user->id,
                'latitude'                => $request->latitude  ?? ($weather['raw_payload']['coord']['lat'] ?? 0),
                'longitude'               => $request->longitude ?? ($weather['raw_payload']['coord']['lon'] ?? 0),
                'location_name'           => $weather['location_name'],
                'temperature_celsius'     => $weather['temperature_celsius'],
                'humidity_percent'        => $weather['humidity_percent'],
                'weather_condition'       => $weather['weather_condition'],
                'season'                  => $season,
                'raw_weather_payload'     => $weather['raw_payload'],
                'engine_request_payload'  => $enginePayload,
                'engine_response_payload' => $engineResponse,
            ]);
            $logId = $log->id;
        }

        /* ── 5. Return top 3 with image URLs ──────────────────────────────── */

        $top3 = array_slice($engineResponse, 0, 3);

        $images = Fragrance::whereIn('id', array_column($top3, 'fragrance_id'))
            ->get()
            ->keyBy('id');

        $top3 = array_map(function ($rec) use ($images) {
            /** @var Fragrance|null $fragrance */
            $fragrance = $images->get($rec['fragrance_id']);
            $rec['image_url'] = $fragrance ? $fragrance->getImageUrl() : null;
            return $rec;
        }, $top3);

        /* ── 6. Compute "Recommended Pick-Ups" ───────────────────────────── */
        // Fragrances NOT in the wardrobe, filtered by shared note families,
        // then climate-scored by the engine.  Only shown when the user/guest
        // already has items (no pick-ups for the catalog-sample guest path).

        $pickUps = [];
        $wardrobeIds = array_column($engineCollection, 'fragrance_id');

        if (! empty($wardrobeIds)) {
            // Aggregate note families present in the current wardrobe
            $wardobeNoteFamilies = collect($engineCollection)
                ->flatMap(fn ($item) => array_column($item['notes'], 'note_family'))
                ->unique()
                ->filter()
                ->values()
                ->toArray();

            // Candidates: active, not in wardrobe, share at least one note family
            $candidateQuery = Fragrance::where('is_active', true)
                ->whereNotIn('id', $wardrobeIds)
                ->has('mappedNotes')
                ->with('mappedNotes.profile');

            // Filter pick-up candidates by the authenticated user's gender preference
            if ($user && $user->gender && $user->gender !== 'unisex') {
                $candidateQuery->whereIn('gender_target', [$user->gender, 'unisex']);
            }

            if (! empty($wardobeNoteFamilies)) {
                $candidateQuery->whereHas('mappedNotes.profile', function ($q) use ($wardobeNoteFamilies) {
                    $q->whereIn('note_family', $wardobeNoteFamilies);
                });
            }

            $candidates = $candidateQuery->inRandomOrder()->limit(80)->get();

            if ($candidates->isNotEmpty()) {
                $candidateEngineCollection = $candidates->map(fn ($f) => [
                    'fragrance_id' => $f->id,
                    'name'         => $f->name,
                    'brand'        => $f->brand,
                    'is_favorite'  => false,
                    'sillage'      => $f->sillage,
                    'notes'        => $f->mappedNotes->map(fn ($note) => [
                        'name'                => $note->profile->name,
                        'layer'               => $note->layer,
                        'note_family'         => $note->profile->note_family,
                        'min_temp_celsius'    => $note->profile->min_temp_celsius,
                        'max_temp_celsius'    => $note->profile->max_temp_celsius,
                        'humidity_preference' => $note->profile->humidity_preference,
                        'ideal_seasons'       => $note->profile->ideal_seasons,
                        'climate_weight'      => $note->profile->climate_weight,
                    ])->values()->toArray(),
                ])->values()->toArray();

                try {
                    $pickUpResponse = $this->engineService->run([
                        'weather'    => $enginePayload['weather'],
                        'collection' => $candidateEngineCollection,
                    ]);

                    $pickUpSlice  = array_slice($pickUpResponse, 0, 3);
                    $pickUpImages = Fragrance::whereIn('id', array_column($pickUpSlice, 'fragrance_id'))
                        ->get()
                        ->keyBy('id');

                    $pickUps = array_map(function ($rec) use ($pickUpImages) {
                        $f = $pickUpImages->get($rec['fragrance_id']);
                        $rec['image_url']           = $f ? $f->getImageUrl() : null;
                        $rec['external_source_url'] = $f?->external_source_url;
                        return $rec;
                    }, $pickUpSlice);
                } catch (\RuntimeException) {
                    // Pick-ups are optional; don't fail the whole request
                    $pickUps = [];
                }
            }
        }

        $raw = $weather['raw_payload'];

        return response()->json([
            'log_id' => $logId,   // null for guests — no DB record
            'guest'  => $user === null,
            'weather' => [
                'location'      => $weather['location_name'],
                'temperature'   => (int) round($weather['temperature_celsius']),
                'feels_like'    => (int) round($raw['main']['feels_like'] ?? $weather['temperature_celsius']),
                'temp_min'      => (int) round($raw['main']['temp_min']   ?? $weather['temperature_celsius']),
                'temp_max'      => (int) round($raw['main']['temp_max']   ?? $weather['temperature_celsius']),
                'condition'     => $weather['weather_condition'],
                'description'   => ucfirst($raw['weather'][0]['description'] ?? $weather['weather_condition']),
                'icon_code'     => $raw['weather'][0]['icon'] ?? null,
                'humidity'      => $weather['humidity_percent'],
                'wind_speed'    => round($raw['wind']['speed'] ?? 0, 1),
                'wind_deg'      => $raw['wind']['deg'] ?? null,
                'visibility_km' => isset($raw['visibility']) ? round($raw['visibility'] / 1000, 1) : null,
                'pressure'      => $raw['main']['pressure'] ?? null,
                'clouds'        => $raw['clouds']['all'] ?? null,
                'season'        => $season,
            ],
            'recommendations' => $top3,
            'pick_ups'        => $pickUps,
        ]);
    }

    public function choose(Request $request, int $log): JsonResponse
    {
        $request->validate(['fragrance_id' => ['required', 'integer', 'exists:fragrances,id']]);

        $weatherLog = WeatherLog::where('id', $log)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $weatherLog) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $weatherLog->update(['chosen_fragrance_id' => $request->fragrance_id]);

        return response()->json(['message' => 'Recorded.']);
    }
}
