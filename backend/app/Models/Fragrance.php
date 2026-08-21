<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model: Fragrance
 *
 * Represents a single fragrance entry in the master catalog.
 *
 * Records are created in one of two ways:
 *  1. Automated import via `php artisan fragrances:import` (source: PerfumAPI)
 *  2. Manual creation via the admin panel (source: 'manual')
 *
 * CLIMATE LOGIC: This model deliberately has NO climate/temperature columns.
 * All "when to wear" data lives on NoteClimateProfile records linked via
 * FragranceNote. The Python engine aggregates note profiles at runtime to
 * produce a composite suitability score.
 *
 * @property int              $id
 * @property string           $name
 * @property string           $brand
 * @property int|null         $release_year
 * @property string|null      $concentration        e.g., "EDP", "EDT"
 * @property string|null      $description
 * @property string           $gender_target        'masculine'|'feminine'|'unisex'
 * @property string|null      $olfactive_family
 * @property float|null       $sillage              0.00–10.00
 * @property float|null       $longevity            0.00–10.00
 * @property float|null       $rating               0.00–5.00
 * @property int|null         $votes
 * @property string|null      $remote_image_url
 * @property string|null      $cached_image_path
 * @property string           $external_source      'perfumapi'|'manual'
 * @property string|null      $external_source_url  Unique Fragrantica permalink
 * @property \Carbon\Carbon|null $last_synced_at
 * @property bool             $is_active
 * @property int|null         $added_by
 * @property \Carbon\Carbon|null $deleted_at
 * @property \Carbon\Carbon   $created_at
 * @property \Carbon\Carbon   $updated_at
 */
class Fragrance extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'brand',
        'release_year',
        'concentration',
        'description',
        'gender_target',
        'olfactive_family',
        'sillage',
        'longevity',
        'rating',
        'votes',
        'remote_image_url',
        'cached_image_path',
        'external_source',
        'external_source_url',
        'last_synced_at',
        'is_active',
        'added_by',
    ];

    /**
     * Attribute casting definitions.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sillage'        => 'float',
            'longevity'      => 'float',
            'rating'         => 'float',
            'is_active'      => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * All olfactive notes for this fragrance across all pyramid layers.
     * Use ->notes()->where('layer', 'top') to filter by layer.
     *
     * @return HasMany<FragranceNote>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(FragranceNote::class);
    }

    /**
     * Top-layer notes only (first impression, 0–30 min).
     *
     * @return HasMany<FragranceNote>
     */
    public function topNotes(): HasMany
    {
        return $this->hasMany(FragranceNote::class)->where('layer', 'top');
    }

    /**
     * Heart-layer notes only (core character, 30 min – 4 hrs).
     *
     * @return HasMany<FragranceNote>
     */
    public function heartNotes(): HasMany
    {
        return $this->hasMany(FragranceNote::class)->where('layer', 'heart');
    }

    /**
     * Base-layer notes only (lasting dry-down, 4 hrs+).
     *
     * @return HasMany<FragranceNote>
     */
    public function baseNotes(): HasMany
    {
        return $this->hasMany(FragranceNote::class)->where('layer', 'base');
    }

    /**
     * Olfactive accord descriptors (manually curated for display only).
     *
     * @return HasMany<FragranceAccord>
     */
    public function accords(): HasMany
    {
        return $this->hasMany(FragranceAccord::class);
    }

    /**
     * Users who have this fragrance in their collection.
     *
     * @return BelongsToMany<User>
     */
    public function collectors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_collections')
                    ->withPivot(['personal_notes', 'is_favorite', 'acquired_date'])
                    ->withTimestamps();
    }

    /**
     * The admin user who created or imported this record.
     *
     * @return BelongsTo<User, Fragrance>
     */
    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * The pre-computed "smells like this" neighbours, most similar first.
     *
     * These rows are produced offline by `fragrances:build-similarity-index`,
     * which runs TF-IDF + cosine similarity over every fragrance's note list.
     * Nothing is computed at request time — this is a plain indexed lookup.
     *
     * Because the index is a snapshot, it goes stale when new fragrances are
     * imported: newcomers have no rows, and existing rows don't know about them.
     * Re-run the command after any catalog import.
     *
     * @return BelongsToMany<Fragrance>
     */
    public function similar(): BelongsToMany
    {
        return $this->belongsToMany(
                        Fragrance::class,
                        'fragrance_similarities',
                        'fragrance_id',
                        'similar_fragrance_id'
                    )
                    ->withPivot(['score', 'rank'])
                    ->orderBy('fragrance_similarities.rank');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helper Methods
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Returns the URL to serve the bottle image.
     *
     * Prefers the locally cached copy (no external CDN dependency at runtime).
     * Falls back to the remote Fragrantica CDN URL if the cache is absent.
     * Returns null if neither exists (e.g., manual entry with no image yet).
     */
    public function getImageUrl(): ?string
    {
        if ($this->cached_image_path) {
            // Prefer R2 (persistent) when configured, otherwise fall back to local public disk
            $disk = config('filesystems.disks.r2.key') ? 'r2' : 'public';

            if (\Storage::disk($disk)->exists($this->cached_image_path)) {
                return \Storage::disk($disk)->url($this->cached_image_path);
            }
        }

        return $this->remote_image_url;
    }

    /**
     * Returns only the notes that have been mapped to a NoteClimateProfile.
     * These are the notes the Python engine will use for scoring.
     *
     * @return HasMany<FragranceNote>
     */
    public function mappedNotes(): HasMany
    {
        return $this->hasMany(FragranceNote::class)
                    ->whereNotNull('note_profile_id')
                    ->with('profile');
    }
}
