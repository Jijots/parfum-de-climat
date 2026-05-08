<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_fragrance_accords_table
 *
 * Stores the weighted olfactive accord descriptors for each fragrance
 * (analogous to Fragrantica's horizontal bar chart, e.g., "Woody 85%").
 *
 * IMPORTANT: PerfumAPI does NOT scrape accord data from Fragrantica — only
 * individual notes (top/middle/base) are returned. This table is therefore
 * populated MANUALLY by admins via the admin panel for fragrances where
 * accord detail is desired for the fragrance detail page UI.
 *
 * The Python recommendation engine does NOT use accords for scoring.
 * It operates exclusively on note_climate_profiles linked via fragrance_notes.
 * Accords are purely for frontend display enrichment.
 *
 * If a future data source exposes accord data programmatically, the import
 * Artisan command can be extended to populate this table automatically.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('fragrance_accords')) {
            return;
        }

        Schema::create('fragrance_accords', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fragrance_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // The accord label, e.g., "Woody", "Aquatic", "Gourmand",
            // "Powdery", "Citrus", "Floral", "Spicy", "Earthy", "Musk"
            $table->string('accord');

            // Relative dominance of this accord in the fragrance's character.
            // 0.00 = barely perceptible trace → 1.00 = the dominant character.
            // Mirrors the bar chart percentage shown on Fragrantica (divided by 100).
            $table->decimal('strength', 3, 2)->default(0.50);

            $table->timestamps();

            $table->unique(['fragrance_id', 'accord']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fragrance_accords');
    }
};
