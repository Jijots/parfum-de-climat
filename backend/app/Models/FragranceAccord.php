<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model: FragranceAccord
 *
 * Represents a broad olfactive character descriptor for a fragrance,
 * analogous to the bar chart shown on Fragrantica (e.g., "Woody 85%").
 *
 * NOTE: PerfumAPI does not scrape accord data from Fragrantica. These records
 * are populated manually by admins via the admin panel for display purposes only.
 *
 * The Python recommendation engine does NOT use accords for scoring — it
 * operates solely on NoteClimateProfile data. Accords are purely UI enrichment
 * on the fragrance detail screen.
 *
 * @property int    $id
 * @property int    $fragrance_id
 * @property string $accord         e.g., "Woody", "Aquatic", "Gourmand"
 * @property float  $strength       0.00 (trace) – 1.00 (dominant)
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class FragranceAccord extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'fragrance_id',
        'accord',
        'strength',
    ];

    /**
     * Attribute casting definitions.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'strength' => 'float',
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * The fragrance this accord descriptor belongs to.
     *
     * @return BelongsTo<Fragrance, FragranceAccord>
     */
    public function fragrance(): BelongsTo
    {
        return $this->belongsTo(Fragrance::class);
    }
}
