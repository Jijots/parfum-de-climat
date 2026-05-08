<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model: FragranceNote
 *
 * A single note on the olfactive pyramid of a fragrance.
 *
 * The dual-name design (raw_note_name + note_profile_id) is intentional:
 *
 *  - `raw_note_name`   preserves exactly what PerfumAPI returned. This ensures
 *    no data is lost if our normalization logic is wrong or changes later.
 *
 *  - `note_profile_id` is the FK to NoteClimateProfile. When NULL, the engine
 *    skips this note; the admin panel flags it for manual resolution.
 *
 * Layer terminology:
 *  - 'top'   → First impression (0–30 min). Usually the most volatile notes.
 *  - 'heart' → The soul/core (30 min – 4 hrs). Maps to PerfumAPI's 'notes_middle'.
 *  - 'base'  → The lasting dry-down (4 hrs+). Heaviest molecules.
 *
 * @property int         $id
 * @property int         $fragrance_id
 * @property string      $raw_note_name      Exact string from PerfumAPI (Title Case)
 * @property int|null    $note_profile_id    FK to note_climate_profiles; null = unmapped
 * @property string      $layer              'top' | 'heart' | 'base'
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class FragranceNote extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'fragrance_id',
        'raw_note_name',
        'note_profile_id',
        'layer',
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * The fragrance this note belongs to.
     *
     * @return BelongsTo<Fragrance, FragranceNote>
     */
    public function fragrance(): BelongsTo
    {
        return $this->belongsTo(Fragrance::class);
    }

    /**
     * The resolved climate profile for this note.
     *
     * Returns null when the note has not yet been mapped to a profile.
     * The engine ignores unmapped notes; admins resolve them via the panel.
     *
     * @return BelongsTo<NoteClimateProfile, FragranceNote>
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(NoteClimateProfile::class, 'note_profile_id');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helper Methods
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Convenience check — returns true if this note is linked to a climate profile
     * and will therefore contribute to the engine's scoring.
     */
    public function isMapped(): bool
    {
        return $this->note_profile_id !== null;
    }
}
