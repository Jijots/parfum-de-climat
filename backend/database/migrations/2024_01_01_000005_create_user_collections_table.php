<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_user_collections_table
 *
 * A many-to-many pivot table representing a user's personal fragrance wardrobe.
 * Each row means: "User X owns Fragrance Y."
 *
 * Fields beyond the pivot keys:
 *
 *  - `personal_notes`  : Free-text field for user context, e.g.,
 *                        "Birthday gift 2024 — 60ml remaining, used mainly evenings."
 *
 *  - `is_favorite`     : Applies a tie-breaking score boost in the Python engine
 *                        when two fragrances achieve identical climate suitability
 *                        scores. Exposed in the UI as a ♡ toggle.
 *
 *  - `acquired_date`   : When the user got this bottle. Displayed on the
 *                        collection card for context; not used by the engine.
 *
 * Constraint: A user can only have one collection entry per fragrance.
 * If a user tries to add the same fragrance twice, the API returns 409 Conflict.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('user_collections')) {
            return;
        }

        Schema::create('user_collections', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('fragrance_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Optional user-written context for this bottle
            $table->text('personal_notes')->nullable();

            // Tie-breaking signal for the recommendation engine;
            // also rendered as a heart/star icon on the collection screen.
            $table->boolean('is_favorite')->default(false);

            // Display-only — when the user acquired this fragrance
            $table->date('acquired_date')->nullable();

            $table->timestamps();

            // Enforce one entry per user per fragrance
            $table->unique(['user_id', 'fragrance_id'], 'uq_user_fragrance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_collections');
    }
};
