<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource: UserCollectionResource
 *
 * Serializes a UserCollection (pivot) entry for the Flutter wardrobe screen.
 * Nests the full FragranceResource so the app never needs a second API call
 * to display the collection list.
 *
 * Example output:
 * {
 *   "id": 3,
 *   "fragrance": { ...FragranceResource... },
 *   "personal_notes": "Birthday gift — 80ml remaining",
 *   "is_favorite": true,
 *   "acquired_date": "2024-06-15",
 *   "added_at": "2024-06-20T08:00:00.000000Z"
 * }
 */
class UserCollectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,

            // Full fragrance detail nested inline — avoids a second API round-trip
            // on the Flutter wardrobe screen.
            'fragrance'      => new FragranceResource($this->whenLoaded('fragrance')),

            'personal_notes' => $this->personal_notes,
            'is_favorite'    => $this->is_favorite,
            'acquired_date'  => $this->acquired_date?->format('Y-m-d'),
            'added_at'       => $this->created_at,
        ];
    }
}
