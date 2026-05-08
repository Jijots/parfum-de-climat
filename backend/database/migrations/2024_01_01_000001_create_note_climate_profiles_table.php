<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_note_climate_profiles_table
 *
 * This table is the core of the "when to wear" logic.
 *
 * Instead of tagging each fragrance with a climate range manually, we assign
 * temperature windows and seasonal preferences to individual olfactive notes
 * (e.g., "Bergamot", "Oud", "Vanilla"). The Python recommendation engine then
 * aggregates these profiles across all of a fragrance's notes to produce a
 * composite climate suitability score at runtime.
 *
 * Pre-seeded examples:
 * ┌──────────────┬────────────┬──────────┬──────────┬────────────────────────┐
 * │ name         │ note_family│ min_temp │ max_temp │ ideal_seasons          │
 * ├──────────────┼────────────┼──────────┼──────────┼────────────────────────┤
 * │ Bergamot     │ Citrus     │   18     │   38     │ ["spring","summer"]    │
 * │ Oud          │ Woody      │   -5     │   18     │ ["autumn","winter"]    │
 * │ Vanilla      │ Gourmand   │  -10     │   12     │ ["winter"]             │
 * │ Sea Salt     │ Aquatic    │   22     │   38     │ ["summer"]             │
 * │ Vetiver      │ Earthy     │   10     │   30     │ ["spring","autumn"]    │
 * └──────────────┴────────────┴──────────┴──────────┴────────────────────────┘
 *
 * Seeding: NoteClimateProfileSeeder pre-populates ~60 common notes.
 * Unmapped notes (from PerfumAPI) surface in the admin panel for resolution.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('note_climate_profiles', function (Blueprint $table) {
            $table->id();

            // ── Identity ──────────────────────────────────────────────────
            // Canonical note name — normalized to Title Case on insert.
            // Must match the string returned by PerfumAPI after trim + title-case.
            $table->string('name')->unique();

            // Broad olfactive family for display grouping and engine fallback.
            // e.g., "Citrus", "Woody", "Floral", "Gourmand", "Aquatic",
            //        "Spicy", "Earthy", "Musk", "Powdery", "Green", "Aromatic"
            $table->string('note_family')->nullable();

            // ── Temperature Window (°C) ───────────────────────────────────
            // NULL = performs well at any temperature in that direction.
            // e.g., min=18, max=null  → suitable above 18°C with no upper limit.
            $table->tinyInteger('min_temp_celsius')->nullable();
            $table->tinyInteger('max_temp_celsius')->nullable();

            // ── Humidity Preference ───────────────────────────────────────
            // Heavy/resinous notes (Oud, Amber, Leather) can turn cloying in
            // high humidity. Fresh/aquatic notes generally project better in it.
            // 'any' → the engine applies no humidity penalty for this note.
            $table->enum('humidity_preference', ['low', 'medium', 'high', 'any'])
                  ->default('any');

            // ── Seasonal Suitability ──────────────────────────────────────
            // JSON array of season strings. Multi-season notes are supported.
            // Valid values: "spring", "summer", "autumn", "winter"
            // e.g., ["spring", "summer"] or ["autumn", "winter"]
            $table->json('ideal_seasons');

            // ── Engine Weight ─────────────────────────────────────────────
            // Controls how much this note's climate range influences the
            // composite fragrance score. Range: 0.00 (negligible) → 1.00 (dominant).
            //
            // Guidance for seeding:
            //  - Dominant character notes (e.g., Bergamot top, Rose heart): 0.70–0.90
            //  - Supporting notes (e.g., Musk base, Ambergris): 0.40–0.60
            //  - Trace/accent notes (e.g., a hint of Pepper): 0.20–0.35
            $table->decimal('climate_weight', 3, 2)->default(0.50);

            $table->timestamps();

            // Index for fast lookup during import normalization
            $table->index('note_family');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note_climate_profiles');
    }
};
