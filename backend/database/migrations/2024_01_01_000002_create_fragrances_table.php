<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_fragrances_table
 *
 * Master fragrance catalog. Records are populated in one of two ways:
 *
 *  1. AUTOMATED — `php artisan fragrances:import`
 *     Calls PerfumAPI (a Fragrantica scraper) and upserts records using
 *     `external_source_url` as the deduplication key. The command also
 *     downloads and caches bottle images locally so the app has zero
 *     runtime dependency on the Fragrantica CDN or the PerfumAPI service.
 *
 *  2. MANUAL — Admin panel CRUD
 *     For indie/local fragrances not on Fragrantica. These records have
 *     external_source = 'manual' and no external_source_url.
 *
 * IMPORTANT — Climate metadata lives on note_climate_profiles, NOT here.
 * The Python engine derives suitability by aggregating the note profiles
 * linked via fragrance_notes. This keeps the catalog lean and the "when
 * to wear" logic fully centralized and maintainable.
 *
 * Image serving strategy:
 *  - remote_image_url  : Original Fragrantica CDN URL (preserved for reference)
 *  - cached_image_path : Laravel Storage path of locally downloaded copy
 *  The app always serves from cached_image_path; falls back to remote_image_url
 *  only if the cache is absent (e.g., newly imported before cache job runs).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('fragrances')) {
            return;
        }

        Schema::create('fragrances', function (Blueprint $table) {
            $table->id();

            // ── Core Identity ─────────────────────────────────────────────
            $table->string('name');                          // e.g., "Baccarat Rouge 540"
            $table->string('brand');                         // e.g., "Maison Francis Kurkdjian"
            $table->unsignedSmallInteger('release_year')->nullable();

            // Not returned by PerfumAPI; can be set manually by admin.
            // e.g., "EDP", "EDT", "Parfum", "EDC", "EDP Intense"
            $table->string('concentration')->nullable();

            // Up to 2000 chars (PerfumAPI enforces this cap from Fragrantica).
            $table->text('description')->nullable();

            // ── Gender Positioning ────────────────────────────────────────
            // Mapped from PerfumAPI's "Men" / "Women" / "Unisex" on import.
            $table->enum('gender_target', ['masculine', 'feminine', 'unisex'])
                  ->default('unisex');

            // ── Olfactive Character ───────────────────────────────────────
            // High-level family — not returned by PerfumAPI directly.
            // Derived post-import from the dominant note_family in
            // fragrance_notes, or set manually by admin.
            $table->string('olfactive_family')->nullable();

            // ── Performance Metrics ───────────────────────────────────────
            // Sourced from Fragrantica community votes via PerfumAPI.
            // longevity & sillage arrive as strings (e.g. "7.3") from the API
            // but are stored as decimals after parsing. Scale: 0.00 – 10.00.
            $table->decimal('sillage', 4, 2)->nullable();    // Projection / trail strength
            $table->decimal('longevity', 4, 2)->nullable();  // Duration on skin

            // Community rating (0.00 – 5.00) and its vote count.
            $table->decimal('rating', 3, 2)->nullable();
            $table->unsignedInteger('votes')->nullable();

            // ── Images ────────────────────────────────────────────────────
            $table->string('remote_image_url', 1024)->nullable(); // Original CDN URL
            $table->string('cached_image_path')->nullable();       // Local Storage path

            // ── Import Provenance ─────────────────────────────────────────
            // Source tracking is critical for the upsert logic in the import
            // command. 'perfumapi' records are keyed on external_source_url;
            // 'manual' records have no external key and are never auto-updated.
            $table->enum('external_source', ['perfumapi', 'manual'])->default('manual');

            // Fragrantica permalink — the unique deduplication key for imports.
            // e.g., "https://www.fragrantica.com/perfume/Chanel/Bleu-28562.html"
            // UNIQUE constraint guarantees idempotent re-imports.
            $table->string('external_source_url', 1024)->nullable()->unique();

            // Timestamp of the last successful sync from PerfumAPI.
            // NULL for manual entries. Used to detect stale records.
            $table->timestamp('last_synced_at')->nullable();

            // ── Catalog Management ────────────────────────────────────────
            $table->boolean('is_active')->default(true);   // Soft-disable from catalog

            // FK to users.id — records which admin created or imported this entry.
            $table->foreignId('added_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Composite index for the admin panel's brand/name search filter
            $table->index(['brand', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fragrances');
    }
};
