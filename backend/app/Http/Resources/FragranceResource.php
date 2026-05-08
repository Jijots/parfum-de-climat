<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource: FragranceResource
 *
 * Transforms a Fragrance model into the standardised JSON shape consumed
 * by both the Flutter app and the Tailwind web views.
 *
 * Notes and accords are conditionally included via whenLoaded() — they are
 * only serialised when the relationship was eager-loaded by the controller,
 * preventing N+1 queries on list endpoints.
 *
 * Example output (detail view):
 * {
 *   "id": 42,
 *   "name": "Baccarat Rouge 540",
 *   "brand": "Maison Francis Kurkdjian",
 *   "gender_target": "unisex",
 *   "olfactive_family": "Amber Floral",
 *   "concentration": "EDP",
 *   "release_year": 2015,
 *   "description": "A luminous woody floral...",
 *   "performance": { "sillage": 8.5, "longevity": 9.1, "rating": 4.3, "votes": 82000 },
 *   "image_url": "http://localhost/storage/fragrances/br540.jpg",
 *   "notes": { "top": [...], "heart": [...], "base": [...] },
 *   "accords": [...],
 *   "source": "perfumapi",
 *   "is_active": true,
 *   "created_at": "2024-01-15T10:30:00.000000Z"
 * }
 */
class FragranceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'brand'            => $this->brand,
            'gender_target'    => $this->gender_target,
            'olfactive_family' => $this->olfactive_family,
            'concentration'    => $this->concentration,
            'release_year'     => $this->release_year,
            'description'      => $this->description,

            // ── Performance metrics ───────────────────────────────────────
            // Grouped under 'performance' to keep the root object clean.
            // Flutter uses these for the detail page's stat bars.
            'performance' => [
                'sillage'  => $this->sillage,
                'longevity'=> $this->longevity,
                'rating'   => $this->rating,
                'votes'    => $this->votes,
            ],

            // ── Image ─────────────────────────────────────────────────────
            // Resolved via Fragrance::getImageUrl() — prefers local cache,
            // falls back to remote CDN URL, returns null if neither exists.
            'image_url' => $this->getImageUrl(),

            // ── Notes (conditionally loaded) ──────────────────────────────
            // Only included when the controller eager-loads with('notes').
            // Structured by pyramid layer for the Flutter detail screen.
            'notes' => $this->when(
                $this->relationLoaded('notes'),
                fn () => [
                    'top'   => $this->notes->where('layer', 'top')->values()
                                    ->map(fn ($n) => $n->raw_note_name),
                    'heart' => $this->notes->where('layer', 'heart')->values()
                                    ->map(fn ($n) => $n->raw_note_name),
                    'base'  => $this->notes->where('layer', 'base')->values()
                                    ->map(fn ($n) => $n->raw_note_name),
                ]
            ),

            // ── Accords (conditionally loaded) ────────────────────────────
            'accords' => $this->when(
                $this->relationLoaded('accords'),
                fn () => $this->accords->map(fn ($a) => [
                    'accord'   => $a->accord,
                    'strength' => $a->strength,
                ])->values()
            ),

            // ── Provenance ────────────────────────────────────────────────
            'source'    => $this->external_source,
            'is_active' => $this->is_active,

            // ── Climate profile flag ──────────────────────────────────────
            // True when the fragrance has at least one note mapped to a
            // NoteClimateProfile — meaning the Python engine can score it.
            // List endpoint supplies mapped_notes_count via withCount().
            // Detail endpoint computes it from the eager-loaded notes relation.
            'has_climate_profile' => isset($this->mapped_notes_count)
                ? $this->mapped_notes_count > 0
                : ($this->relationLoaded('notes') && $this->notes->whereNotNull('note_profile_id')->isNotEmpty()),

            // ── Timestamps ────────────────────────────────────────────────
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
