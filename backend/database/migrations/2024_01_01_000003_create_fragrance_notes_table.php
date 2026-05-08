<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_fragrance_notes_table
 *
 * Links fragrances to individual olfactive notes across the three layers
 * of the fragrance pyramid (top / heart / base).
 *
 * Two fields exist for the note name — this is intentional:
 *
 *  1. `raw_note_name`   — The exact string returned by PerfumAPI after
 *                         trim + title-case normalization (e.g., "Pink Pepper").
 *                         Always populated. Preserved so original API data
 *                         is never lost regardless of mapping status.
 *
 *  2. `note_profile_id` — FK to note_climate_profiles. Populated during
 *                         import via a case-insensitive name lookup. NULL if
 *                         no matching profile was found.
 *
 * When note_profile_id is NULL, the Python engine excludes that note from
 * climate scoring. The admin panel surfaces unmapped notes so an admin can:
 *   a) Create a new NoteClimateProfile for it, or
 *   b) Alias it to an existing one (e.g., "Citrus Bergamot" → "Bergamot").
 *
 * Layer mapping from PerfumAPI field names:
 *   notes_top    → layer = 'top'
 *   notes_middle → layer = 'heart'  (we use fragrance pyramid terminology)
 *   notes_base   → layer = 'base'
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('fragrance_notes')) {
            return;
        }

        Schema::create('fragrance_notes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fragrance_id')
                  ->constrained()
                  ->cascadeOnDelete(); // Notes are removed with the fragrance

            // Raw note string from PerfumAPI, e.g., "Pink Pepper", "Iso E Super"
            $table->string('raw_note_name');

            // Resolved FK to note_climate_profiles.
            // NULL = not yet mapped; this note is excluded from engine scoring.
            $table->foreignId('note_profile_id')
                  ->nullable()
                  ->constrained('note_climate_profiles')
                  ->nullOnDelete(); // If a profile is deleted, unlink gracefully

            // Which layer of the olfactive pyramid this note belongs to.
            // 'heart' corresponds to PerfumAPI's 'notes_middle' field.
            $table->enum('layer', ['top', 'heart', 'base']);

            $table->timestamps();

            // Prevent the same note appearing twice in the same layer for
            // the same fragrance — enforced on raw name to catch duplicates
            // even before profile mapping occurs.
            $table->unique(['fragrance_id', 'raw_note_name', 'layer'], 'uq_frag_note_layer');

            // Index for the admin panel's "find all fragrances with note X" query
            $table->index('note_profile_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fragrance_notes');
    }
};
