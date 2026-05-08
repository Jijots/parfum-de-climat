<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model: UserCollection
 *
 * Represents a single entry in a user's personal fragrance wardrobe.
 * This is the explicit pivot model for the users ↔ fragrances many-to-many.
 *
 * Using an explicit pivot model (rather than a bare pivot table) gives us:
 *  - Full Eloquent model features (events, casts, accessors) on pivot data.
 *  - A clean API endpoint: GET/POST/PUT/DELETE /api/v1/collection/{fragranceId}
 *  - The ability to attach personal_notes and is_favorite without raw DB queries.
 *
 * Engine interaction:
 *  - `is_favorite` → the Python engine applies a small score boost (+0.05) to
 *    favorited fragrances when two candidates are otherwise equal.
 *
 * @property int         $id
 * @property int         $user_id
 * @property int         $fragrance_id
 * @property string|null $personal_notes   Free-text user notes for this bottle
 * @property bool        $is_favorite      Tie-breaking boost signal for the engine
 * @property string|null $acquired_date    ISO date string, display only
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class UserCollection extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_collections';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'fragrance_id',
        'personal_notes',
        'is_favorite',
        'acquired_date',
    ];

    /**
     * Attribute casting definitions.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_favorite'   => 'boolean',
            'acquired_date' => 'date:Y-m-d',
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * The user who owns this collection entry.
     *
     * @return BelongsTo<User, UserCollection>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The fragrance in this collection entry.
     * Eager-loads notes and accords for the collection display screen.
     *
     * @return BelongsTo<Fragrance, UserCollection>
     */
    public function fragrance(): BelongsTo
    {
        return $this->belongsTo(Fragrance::class);
    }
}
