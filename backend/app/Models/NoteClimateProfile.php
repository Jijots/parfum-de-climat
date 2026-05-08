<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: NoteClimateProfile
 *
 * Represents the climate suitability profile for a single olfactive note.
 * This is the core data entity powering the "when to wear" recommendation logic.
 *
 * How it works:
 *  1. This table is pre-seeded with ~60 common notes and their climate ranges.
 *  2. When a fragrance is imported from PerfumAPI, each of its raw note strings
 *     is matched (case-insensitive) against this table.
 *  3. On a recommendation request, the Python engine receives all mapped profiles
 *     for each fragrance in the user's collection and scores them against the
 *     current weather (temperature, humidity, season).
 *
 * Admin workflow for unmapped notes:
 *  - The admin panel surfaces all FragranceNotes where note_profile_id IS NULL.
 *  - An admin can either create a new NoteClimateProfile or link the note to
 *    an existing profile (e.g., "Citrus Bergamot" → "Bergamot").
 *
 * @property int          $id
 * @property string       $name               Canonical note name (unique, Title Case)
 * @property string|null  $note_family        e.g., "Citrus", "Woody", "Floral"
 * @property int|null     $min_temp_celsius    Lower ideal temperature bound
 * @property int|null     $max_temp_celsius    Upper ideal temperature bound
 * @property string       $humidity_preference 'low'|'medium'|'high'|'any'
 * @property array        $ideal_seasons       e.g., ["spring", "summer"]
 * @property float        $climate_weight      0.00–1.00 influence on composite score
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class NoteClimateProfile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'note_family',
        'min_temp_celsius',
        'max_temp_celsius',
        'humidity_preference',
        'ideal_seasons',
        'climate_weight',
    ];

    /**
     * Attribute casting definitions.
     *
     * `ideal_seasons` is stored as a JSON array in the DB and automatically
     * cast to/from a PHP array by Eloquent.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ideal_seasons'    => 'array',
            'climate_weight'   => 'float',
            'min_temp_celsius' => 'integer',
            'max_temp_celsius' => 'integer',
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * All fragrance note instances linked to this climate profile.
     * One profile can be referenced by many notes across many fragrances.
     *
     * @return HasMany<FragranceNote>
     */
    public function fragranceNotes(): HasMany
    {
        return $this->hasMany(FragranceNote::class, 'note_profile_id');
    }
}
