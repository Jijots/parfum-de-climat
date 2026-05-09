<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migration: add_search_indexes_to_fragrances
 *
 * Adds GIN trigram indexes on fragrances.name and fragrances.brand for
 * PostgreSQL so that ILIKE '%term%' queries are index-backed instead of
 * doing a full-table sequential scan.
 *
 * pg_trgm must be available on the PostgreSQL server.  Render's managed
 * PostgreSQL includes it by default (no superuser required for CREATE EXTENSION).
 *
 * Safe to run on MySQL — the entire migration is skipped for non-pgsql drivers.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return; // MySQL / SQLite: LIKE is already fast enough at this scale
        }

        try {
            // Enable the trigram extension (idempotent — safe to re-run).
            // Render's managed PostgreSQL includes pg_trgm; some self-hosted
            // instances may require superuser. Wrapped in try/catch so a
            // permission failure degrades gracefully instead of crashing the deploy.
            DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

            // GIN indexes — optimal for ILIKE '%...%' substring searches
            DB::statement('
                CREATE INDEX IF NOT EXISTS fragrances_name_trgm_idx
                ON fragrances USING GIN (name gin_trgm_ops)
            ');

            DB::statement('
                CREATE INDEX IF NOT EXISTS fragrances_brand_trgm_idx
                ON fragrances USING GIN (brand gin_trgm_ops)
            ');
        } catch (\Throwable $e) {
            // pg_trgm unavailable — ILIKE still works, just without an index.
            // Log the warning and continue so the deploy is not blocked.
            \Illuminate\Support\Facades\Log::warning(
                '[Migration] pg_trgm indexes skipped: ' . $e->getMessage()
            );
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP INDEX IF EXISTS fragrances_name_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS fragrances_brand_trgm_idx');
    }
};
