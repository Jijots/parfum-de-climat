<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Service: WeatherService
 *
 * Fetches real-time weather data from the OpenWeatherMap Current Weather API
 * and derives season from the user's timezone + current date.
 *
 * API used: GET https://api.openweathermap.org/data/2.5/weather
 * Docs: https://openweathermap.org/current
 * Auth: API key via query param `appid` (free tier: 60 calls/min)
 *
 * Season derivation logic:
 *  The current calendar month is used alongside the user's hemisphere
 *  (inferred from their IANA timezone) to determine the correct season.
 *  This correctly handles Southern Hemisphere reversed seasons:
 *   - "Asia/Manila" (Northern) → June = Summer
 *   - "Australia/Sydney" (Southern) → June = Winter
 *
 * Hemisphere detection: Uses the UTC offset sign of the timezone string.
 * Timezones with identifiers containing geographic hints ("Australia/",
 * "Pacific/Auckland", "America/Sao_Paulo", etc.) are matched to their
 * correct hemisphere via the SOUTHERN_HEMISPHERE_REGIONS lookup.
 */
class WeatherService
{
    /**
     * How long a weather reading stays fresh.
     *
     * Ten minutes: shorter than any meaningful change in conditions, long
     * enough that a burst of requests from the same area costs one API call.
     */
    private const CACHE_TTL = 600;

    /**
     * Decimal places kept when building a cache key from coordinates.
     * Two is about 1.1 km — the right weather, not the right address.
     */
    private const COORD_PRECISION = 2;

    /**
     * Timezone region prefixes that indicate Southern Hemisphere location.
     * Used for season inversion logic.
     */
    private const SOUTHERN_HEMISPHERE_REGIONS = [
        'Australia',
        'Pacific/Auckland',
        'Pacific/Fiji',
        'Pacific/Port_Moresby',
        'America/Sao_Paulo',
        'America/Argentina/Buenos_Aires',
        'America/Santiago',
        'Africa/Johannesburg',
        'Africa/Nairobi',
        'Indian/Mauritius',
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // Public API
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Fetch the current weather for the given GPS coordinates.
     *
     * @param  float   $latitude   WGS84 latitude (-90 to 90)
     * @param  float   $longitude  WGS84 longitude (-180 to 180)
     * @return array{
     *   temperature_celsius: float,
     *   humidity_percent: int,
     *   weather_condition: string,
     *   location_name: string,
     *   raw_payload: array<string, mixed>
     * }
     *
     * @throws \RuntimeException  When the API key is missing or the request fails.
     */
    /**
     * Weather for a coordinate pair.
     *
     * Cached on ROUNDED coordinates for two reasons that happen to align.
     *
     * Quota: previously every recommendation hit OpenWeatherMap. The free tier
     * is finite, the response is identical for anyone within about a kilometre,
     * and conditions do not change minute to minute — so an uncached call was
     * spending quota to re-fetch a value we already had.
     *
     * Privacy: two decimal places is roughly 1.1 km. That is precise enough to
     * pick the right weather and far too coarse to identify a house, so the
     * exact position never becomes a cache key.
     */
    public function getCurrentWeather(float $latitude, float $longitude): array
    {
        $key = sprintf(
            'weather:coord:%.2f,%.2f',
            round($latitude, self::COORD_PRECISION),
            round($longitude, self::COORD_PRECISION)
        );

        return Cache::remember($key, self::CACHE_TTL, fn () => $this->fetchByCoordinates($latitude, $longitude));
    }

    private function fetchByCoordinates(float $latitude, float $longitude): array
    {
        $apiKey = config('services.openweathermap.api_key');

        if (empty($apiKey)) {
            throw new \RuntimeException(
                'OpenWeatherMap API key is not configured. ' .
                'Set OPENWEATHERMAP_API_KEY in your .env file.'
            );
        }

        try {
            $response = Http::timeout(10)
                            ->connectTimeout(5)
                            ->get(config('services.openweathermap.base_url') . '/weather', [
                                'lat'   => $latitude,
                                'lon'   => $longitude,
                                'appid' => $apiKey,
                                'units' => 'metric', // Temperature in °C
                            ]);

            if (! $response->successful()) {
                Log::error('[WeatherService] API request failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                throw new \RuntimeException(
                    "Weather API returned HTTP {$response->status()}."
                );
            }

            $data = $response->json();

            return $this->parseWeatherResponse($data);

        } catch (ConnectionException $e) {
            Log::error('[WeatherService] Connection failed: ' . $e->getMessage());
            throw new \RuntimeException('Could not reach the weather service. Please try again.');
        }
    }

    /**
     * Fetch the current weather for the given city name.
     *
     * @param  string  $city  City name, optionally with country code (e.g. "Manila,PH")
     * @return array{
     *   temperature_celsius: float,
     *   humidity_percent: int,
     *   weather_condition: string,
     *   location_name: string,
     *   raw_payload: array<string, mixed>
     * }
     *
     * @throws \RuntimeException  When the API key is missing, city is not found, or the request fails.
     */
    /**
     * Weather for a named place.
     *
     * Keyed on a normalised name so "manila", "Manila" and " Manila " share one
     * entry, and hashed so an unusual place name cannot produce a key longer
     * than a cache driver will accept.
     */
    public function getCurrentWeatherByCity(string $city): array
    {
        $key = 'weather:city:' . md5(mb_strtolower(trim($city)));

        return Cache::remember($key, self::CACHE_TTL, fn () => $this->fetchByCity($city));
    }

    private function fetchByCity(string $city): array
    {
        $apiKey = config('services.openweathermap.api_key');

        if (empty($apiKey)) {
            throw new \RuntimeException('OpenWeatherMap API key is not configured.');
        }

        $baseUrl = rtrim((string) config('services.openweathermap.base_url'), '/');

        $response = Http::timeout(10)->get($baseUrl . '/weather', [
            'q'     => $city,
            'appid' => $apiKey,
            'units' => 'metric',
        ]);

        if ($response->status() === 404) {
            throw new \RuntimeException("City \"{$city}\" not found. Please check the spelling.");
        }

        if (! $response->successful()) {
            throw new \RuntimeException('Weather service is temporarily unavailable.');
        }

        return $this->parseWeatherResponse($response->json());
    }

    /**
     * Derive the meteorological season for a given timezone and date.
     *
     * Accounts for Southern Hemisphere reversed seasons by detecting the
     * user's hemisphere from their IANA timezone string.
     *
     * @param  string       $timezone  IANA timezone string, e.g., "Asia/Manila"
     * @param  Carbon|null  $date      Defaults to now() in the given timezone
     * @return string  One of: 'spring', 'summer', 'autumn', 'winter'
     */
    public function deriveSeason(string $timezone, ?Carbon $date = null): string
    {
        $date = $date ?? Carbon::now($timezone);
        $month = $date->month;

        // Determine the meteorological season for the Northern Hemisphere
        $northernSeason = match (true) {
            in_array($month, [3, 4, 5])   => 'spring',
            in_array($month, [6, 7, 8])   => 'summer',
            in_array($month, [9, 10, 11]) => 'autumn',
            default                       => 'winter', // Dec, Jan, Feb
        };

        // Invert season for Southern Hemisphere timezones
        if ($this->isSouthernHemisphere($timezone)) {
            return $this->invertSeason($northernSeason);
        }

        return $northernSeason;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Normalise a raw OpenWeatherMap response into the standard weather array
     * shape used throughout this application.
     *
     * Extracted so both `getCurrentWeather` (by coordinate) and
     * `getCurrentWeatherByCity` (by name) can share identical output.
     *
     * @param  array<string, mixed>  $data  Raw OWM API response
     * @return array{
     *   temperature_celsius: float,
     *   humidity_percent: int,
     *   weather_condition: string,
     *   location_name: string,
     *   raw_payload: array<string, mixed>
     * }
     */
    private function parseWeatherResponse(array $data): array
    {
        return [
            'temperature_celsius' => (float) $data['main']['temp'],
            'humidity_percent'    => (int) $data['main']['humidity'],
            'weather_condition'   => $data['weather'][0]['main'] ?? 'Unknown',
            'location_name'       => $this->formatLocationName($data),
            'raw_payload'         => $data,
            // Extended fields for the weather card
            'feels_like'          => round((float) ($data['main']['feels_like'] ?? $data['main']['temp']), 1),
            'temp_min'            => round((float) ($data['main']['temp_min'] ?? $data['main']['temp']), 1),
            'temp_max'            => round((float) ($data['main']['temp_max'] ?? $data['main']['temp']), 1),
            'humidity'            => (int) $data['main']['humidity'],
            'wind_speed'          => round((float) ($data['wind']['speed'] ?? 0), 1),
            'pressure'            => (int) ($data['main']['pressure'] ?? 0) ?: null,
            'visibility_km'       => isset($data['visibility']) ? round($data['visibility'] / 1000, 1) : null,
            'clouds'              => $data['clouds']['all'] ?? null,
        ];
    }

    /**
     * Detect if a timezone belongs to the Southern Hemisphere.
     *
     * Uses a prefix-matching lookup against known Southern Hemisphere
     * timezone region identifiers. This is a pragmatic approach that
     * covers the vast majority of use cases without a geolocation library.
     *
     * @param  string  $timezone  IANA timezone string
     */
    private function isSouthernHemisphere(string $timezone): bool
    {
        foreach (self::SOUTHERN_HEMISPHERE_REGIONS as $region) {
            if (str_starts_with($timezone, $region)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Invert a Northern Hemisphere season to its Southern equivalent.
     *
     * Northern summer (Jun–Aug) → Southern winter, and vice versa.
     * Spring/Autumn swap similarly.
     *
     * @param  string  $season  'spring'|'summer'|'autumn'|'winter'
     */
    private function invertSeason(string $season): string
    {
        return match ($season) {
            'spring' => 'autumn',
            'summer' => 'winter',
            'autumn' => 'spring',
            'winter' => 'summer',
            default  => $season,
        };
    }

    /**
     * Build a human-readable location name from the OWM response.
     *
     * OpenWeatherMap returns city name and country code separately.
     * We join them as "Manila, PH" for display in the app.
     *
     * @param  array<string, mixed>  $data  Raw OWM API response
     */
    private function formatLocationName(array $data): string
    {
        $city    = $data['name'] ?? '';
        $country = $data['sys']['country'] ?? '';
        // OWM sometimes returns a state/province in sys.state (reverse geocode responses)
        $state   = $data['sys']['state'] ?? '';

        if (! $city) {
            return 'Unknown Location';
        }

        // Build "City, State, Country" when state is available and differs from city
        if ($state && strtolower($state) !== strtolower($city)) {
            return $country ? "{$city}, {$state}, {$country}" : "{$city}, {$state}";
        }

        return $country ? "{$city}, {$country}" : $city;
    }
}
