<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_weather_logs_table
 *
 * Full audit log of every recommendation engine invocation.
 *
 * Design philosophy — store everything twice:
 *  1. Flat scalar columns  → fast SQL analytics (avg temp by month, popular seasons, etc.)
 *  2. Raw JSON blobs       → complete auditability; any past recommendation can
 *                            be replayed and its scoring debugged without touching live data.
 *
 * The `chosen_fragrance_id` column captures implicit user feedback loops:
 * when a user taps "Wearing this today" on one of the top-3 cards, we record it.
 * This dataset can later validate the engine's accuracy or be used to fine-tune
 * the note climate weights in note_climate_profiles.
 *
 * Engine response shape (stored in engine_response_payload):
 * [
 *   {
 *     "fragrance_id": 42,
 *     "score": 0.87,
 *     "matched_notes": ["Bergamot", "Neroli"],
 *     "reasoning": "Citrus-forward profile peaks in 24°C summer conditions."
 *   },
 *   ...
 * ]
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('weather_logs')) {
            return;
        }

        Schema::create('weather_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // ── Weather Snapshot ──────────────────────────────────────────
            // Flat columns for analytics; full blob for audit.
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);

            // Reverse-geocoded city/locality name, e.g., "Manila, PH"
            // Populated from OpenWeatherMap's `name` field in the response.
            $table->string('location_name')->nullable();

            $table->decimal('temperature_celsius', 5, 2);
            $table->tinyInteger('humidity_percent');          // 0–100
            $table->string('weather_condition');              // e.g., "Clear", "Rain", "Haze"

            // Derived server-side from user timezone + current date.
            // Values: "spring", "summer", "autumn", "winter"
            $table->string('season');

            // The full raw JSON response from OpenWeatherMap.
            // Stored for future re-processing or debugging without re-fetching.
            $table->json('raw_weather_payload');

            // ── Engine I/O ────────────────────────────────────────────────
            // The exact payload dispatched to the Python engine.
            // Shape: { weather: {...}, collection: [{fragrance_id, notes: [...]}] }
            $table->json('engine_request_payload');

            // The full ranked list returned by the Python engine (see class docblock).
            $table->json('engine_response_payload');

            // ── User Feedback ─────────────────────────────────────────────
            // Fragrance the user chose from the top-3 recommendation cards.
            // NULL = user dismissed the recommendation without choosing.
            $table->foreignId('chosen_fragrance_id')
                  ->nullable()
                  ->constrained('fragrances')
                  ->nullOnDelete();

            $table->timestamps();

            // Optimized for the user recommendation history feed (newest first)
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_logs');
    }
};
