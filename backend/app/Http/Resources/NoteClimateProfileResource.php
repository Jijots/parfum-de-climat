<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource: NoteClimateProfileResource
 *
 * Serializes a NoteClimateProfile for the admin panel's note management view.
 * Includes all climate fields so admins can review and edit them.
 *
 * Example output:
 * {
 *   "id": 7,
 *   "name": "Bergamot",
 *   "note_family": "Citrus",
 *   "temperature_range": { "min": 18, "max": 38 },
 *   "humidity_preference": "any",
 *   "ideal_seasons": ["spring", "summer"],
 *   "climate_weight": 0.80,
 *   "fragrance_count": 142
 * }
 */
class NoteClimateProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'note_family' => $this->note_family,

            // Temperature window grouped for clarity in the admin form UI
            'temperature_range' => [
                'min' => $this->min_temp_celsius,
                'max' => $this->max_temp_celsius,
            ],

            'humidity_preference' => $this->humidity_preference,
            'ideal_seasons'       => $this->ideal_seasons,
            'climate_weight'      => $this->climate_weight,

            // Number of fragrance notes currently linked to this profile.
            // Loaded via withCount('fragranceNotes') in the controller.
            'fragrance_count' => $this->whenCounted('fragranceNotes'),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
