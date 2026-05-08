<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource: WeatherLogResource
 *
 * Serializes a WeatherLog for the Flutter "recommendation history" screen.
 * Exposes only the fields needed for display — raw JSON payloads are omitted
 * from the API response (they are internal audit data).
 *
 * Example output:
 * {
 *   "id": 101,
 *   "location": { "name": "Manila, PH", "lat": 14.5995, "lng": 120.9842 },
 *   "weather": {
 *     "temperature_celsius": 32.4,
 *     "humidity_percent": 81,
 *     "condition": "Haze",
 *     "season": "summer"
 *   },
 *   "recommendations": [
 *     { "fragrance_id": 42, "score": 0.91, "reasoning": "Citrus notes..." },
 *     ...
 *   ],
 *   "chosen_fragrance": { ...FragranceResource... },
 *   "logged_at": "2024-07-12T09:15:00.000000Z"
 * }
 */
class WeatherLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            // ── Location ──────────────────────────────────────────────────
            'location' => [
                'name' => $this->location_name,
                'lat'  => $this->latitude,
                'lng'  => $this->longitude,
            ],

            // ── Weather snapshot ──────────────────────────────────────────
            'weather' => [
                'temperature_celsius' => $this->temperature_celsius,
                'humidity_percent'    => $this->humidity_percent,
                'condition'           => $this->weather_condition,
                'season'              => $this->season,
            ],

            // ── Engine output ─────────────────────────────────────────────
            // The engine_response_payload is the full ranked list. We expose
            // it directly — Flutter renders the top 3 from this array.
            'recommendations' => $this->engine_response_payload,

            // ── User feedback ─────────────────────────────────────────────
            // The fragrance the user chose to wear, if any.
            'chosen_fragrance' => new FragranceResource(
                $this->whenLoaded('chosenFragrance')
            ),

            'logged_at' => $this->created_at,
        ];
    }
}
