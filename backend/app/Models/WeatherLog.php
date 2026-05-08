<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model: WeatherLog
 *
 * A complete audit record of one recommendation engine invocation.
 *
 * Every time a user requests fragrance recommendations, a WeatherLog is created
 * with the full context: GPS coordinates, weather snapshot, the exact payload
 * sent to the Python engine, and the full ranked response it returned.
 *
 * Dual-storage design:
 *  - Flat scalar columns  → fast SQL analytics (e.g., avg temperature by month,
 *                           most recommended fragrances by season, user history feed)
 *  - Raw JSON blob fields → complete auditability; any past recommendation can
 *                           be replayed and scored without re-fetching weather data.
 *
 * The `chosen_fragrance_id` enables an implicit feedback loop:
 * when the user taps "Wearing this today" on one of the recommendation cards,
 * the Flutter app sends a PATCH request updating this field. Over time this
 * dataset validates engine accuracy and can inform weight adjustments in
 * NoteClimateProfile.climate_weight.
 *
 * @property int         $id
 * @property int         $user_id
 * @property float       $latitude
 * @property float       $longitude
 * @property string|null $location_name         Reverse-geocoded city, e.g. "Manila, PH"
 * @property float       $temperature_celsius
 * @property int         $humidity_percent      0–100
 * @property string      $weather_condition     e.g., "Clear", "Rain", "Haze"
 * @property string      $season                'spring'|'summer'|'autumn'|'winter'
 * @property array       $raw_weather_payload   Full OpenWeatherMap response
 * @property array       $engine_request_payload Payload sent to Python engine
 * @property array       $engine_response_payload Ranked list returned by engine
 * @property int|null    $chosen_fragrance_id   The recommendation the user chose
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class WeatherLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'latitude',
        'longitude',
        'location_name',
        'temperature_celsius',
        'humidity_percent',
        'weather_condition',
        'season',
        'raw_weather_payload',
        'engine_request_payload',
        'engine_response_payload',
        'chosen_fragrance_id',
    ];

    /**
     * Attribute casting definitions.
     *
     * JSON columns are automatically serialized/deserialized as PHP arrays,
     * so controllers and the engine service never need to call json_decode().
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'raw_weather_payload'    => 'array',
            'engine_request_payload' => 'array',
            'engine_response_payload'=> 'array',
            'latitude'               => 'float',
            'longitude'              => 'float',
            'temperature_celsius'    => 'float',
            'humidity_percent'       => 'integer',
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * The user who triggered this recommendation.
     *
     * @return BelongsTo<User, WeatherLog>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The fragrance the user chose to wear from the recommendations.
     * NULL if the user dismissed the recommendation screen.
     *
     * @return BelongsTo<Fragrance, WeatherLog>
     */
    public function chosenFragrance(): BelongsTo
    {
        return $this->belongsTo(Fragrance::class, 'chosen_fragrance_id');
    }
}
