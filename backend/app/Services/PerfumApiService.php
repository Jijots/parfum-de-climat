<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service: PerfumApiService
 *
 * Wraps all communication with the PerfumAPI (github.com/seccaz/PerfumAPI),
 * a FastAPI service that exposes Fragrantica fragrance data.
 *
 * IMPORTANT — This service is used ONLY by the ImportFragrancesCommand Artisan
 * command and is NEVER called at request time. All runtime fragrance queries
 * go directly to our own MySQL database. This service exists solely to populate
 * that database during the import phase.
 *
 * Risk mitigations implemented here:
 *  1. Configurable base URL (env: PERFUMAPI_BASE_URL) — swap between the
 *     hosted Render.com instance and a local Docker instance with no code change.
 *  2. Cold-start handling — the Render.com free tier spins down after inactivity.
 *     A warm-up GET /health call is made before the first real request, with a
 *     longer timeout to allow the dyno to wake up.
 *  3. Retry logic — transient network errors and 5xx responses are retried
 *     up to 3 times with exponential back-off.
 *  4. Graceful failure — all public methods return null/[] on unrecoverable
 *     errors and log the details. The import command handles these gracefully.
 *
 * PerfumAPI pagination:
 *  - GET /perfumes          → max limit 500, supports offset
 *  - GET /perfumes/search/{query} → max limit 200, NO offset support
 *
 * Data quirk: `longevity` and `sillage` are returned as strings ("7.3")
 * despite being numeric. normalizeRecord() handles the conversion.
 */
class PerfumApiService
{
    /** Base URL from environment, e.g. "https://perfumapi-frontend.onrender.com" */
    private string $baseUrl;

    /** Request timeout in seconds */
    private int $timeout;

    /** Connection timeout in seconds */
    private int $connectTimeout;

    /**
     * Tracks whether a warm-up call has been performed this process lifetime.
     * Avoids redundant warm-up calls when the import command pages through results.
     */
    private bool $warmedUp = false;

    public function __construct()
    {
        $this->baseUrl        = rtrim((string) config('services.perfumapi.base_url'), '/');
        $this->timeout        = (int) config('services.perfumapi.timeout', 30);
        $this->connectTimeout = (int) config('services.perfumapi.connect_timeout', 10);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Public API
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Fetch a paginated list of all fragrances from PerfumAPI.
     *
     * @param  int  $limit   Max records per page (1–500, API enforced)
     * @param  int  $offset  Number of records to skip (for pagination)
     * @return array{
     *   total: int,
     *   limit: int,
     *   offset: int,
     *   perfumes: list<array<string, mixed>>
     * }|null  Returns null on unrecoverable error.
     */
    public function list(int $limit = 100, int $offset = 0): ?array
    {
        $this->warmUp();

        $response = $this->get('/perfumes', [
            'limit'  => min($limit, 500),
            'offset' => $offset,
        ]);

        if ($response === null) {
            return null;
        }

        // Normalize each perfume record in the list
        $response['perfumes'] = array_map(
            fn (array $p) => $this->normalizeRecord($p),
            $response['perfumes'] ?? []
        );

        return $response;
    }

    /**
     * Search fragrances by keyword (name or brand — case-insensitive substring).
     *
     * Note: The search endpoint has no offset support and a max limit of 200.
     * For large brand catalogs, use list() + filter instead.
     *
     * @param  string  $query  Search term, e.g., "Chanel" or "Oud"
     * @param  int     $limit  Max results (1–200, API enforced)
     * @return list<array<string, mixed>>  Returns [] on error.
     */
    public function search(string $query, int $limit = 50): array
    {
        $this->warmUp();

        // Query is a PATH parameter, not a query string — encode slashes safely.
        $encodedQuery = rawurlencode(trim($query));
        $result = $this->get("/perfumes/search/{$encodedQuery}", [
            'limit' => min($limit, 200),
        ]);

        if (! is_array($result)) {
            return [];
        }

        // Search returns a bare array (not the envelope that /perfumes uses)
        return array_map(fn (array $p) => $this->normalizeRecord($p), $result);
    }

    /**
     * Fetch the total count of fragrances in the PerfumAPI database.
     * Used by the import command to calculate total pages for the progress bar.
     *
     * @return int|null  Returns null on error.
     */
    public function getTotalCount(): ?int
    {
        $this->warmUp();

        $result = $this->get('/stats');

        // The /stats response shape: { "total_perfumes": N, ... }
        return isset($result['total_perfumes']) ? (int) $result['total_perfumes'] : null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Makes a GET request to the PerfumAPI with retry logic.
     *
     * Retries up to 3 times on connection errors or 5xx responses,
     * using exponential back-off (1s, 2s, 4s delays).
     *
     * @param  string               $path    API path, e.g., "/perfumes"
     * @param  array<string, mixed> $query   Query string parameters
     * @return array<string, mixed>|list<mixed>|null  Decoded JSON or null on failure.
     */
    private function get(string $path, array $query = []): array|null
    {
        $attempts = 0;
        $maxAttempts = 3;

        while ($attempts < $maxAttempts) {
            $attempts++;

            try {
                $response = Http::timeout($this->timeout)
                                ->connectTimeout($this->connectTimeout)
                                ->get($this->baseUrl . $path, $query);

                if ($response->successful()) {
                    return $response->json();
                }

                // Non-5xx client errors (e.g., 404) should not be retried
                if ($response->clientError()) {
                    Log::warning('[PerfumApiService] Client error', [
                        'path'   => $path,
                        'status' => $response->status(),
                        'body'   => $response->body(),
                    ]);
                    return null;
                }

                // Server error — log and retry
                Log::warning("[PerfumApiService] Server error (attempt {$attempts}/{$maxAttempts})", [
                    'path'   => $path,
                    'status' => $response->status(),
                ]);

            } catch (ConnectionException $e) {
                Log::warning("[PerfumApiService] Connection error (attempt {$attempts}/{$maxAttempts}): {$e->getMessage()}", [
                    'path' => $path,
                ]);
            } catch (RequestException $e) {
                Log::error('[PerfumApiService] Request exception', [
                    'path'    => $path,
                    'message' => $e->getMessage(),
                ]);
                return null; // Not retryable
            }

            // Exponential back-off: 1s, 2s, 4s
            if ($attempts < $maxAttempts) {
                sleep(2 ** ($attempts - 1));
            }
        }

        Log::error("[PerfumApiService] All {$maxAttempts} attempts failed for path: {$path}");
        return null;
    }

    /**
     * Performs a warm-up GET /health call to wake the Render.com free-tier dyno.
     *
     * The free-tier instance spins down after 15 minutes of inactivity.
     * The first request to a cold dyno can take 30–60 seconds. By calling
     * /health first with an extended timeout, we ensure subsequent data
     * requests don't time out unexpectedly.
     *
     * The warm-up is skipped if already performed this process lifetime,
     * or if the base URL is localhost (local dev instance never goes cold).
     */
    private function warmUp(): void
    {
        if ($this->warmedUp || str_contains($this->baseUrl, 'localhost')) {
            return;
        }

        Log::info('[PerfumApiService] Warming up remote instance...');

        try {
            // Extended timeout (60s) to accommodate cold-start spin-up time
            Http::timeout(60)
                ->connectTimeout(10)
                ->get($this->baseUrl . '/health');
        } catch (\Exception $e) {
            // Warm-up failure is non-fatal; log and continue
            Log::warning('[PerfumApiService] Warm-up call failed: ' . $e->getMessage());
        }

        $this->warmedUp = true;
        Log::info('[PerfumApiService] Warm-up complete.');
    }

    /**
     * Normalises a raw PerfumAPI perfume record into our expected shape.
     *
     * Handles known API quirks:
     *  - `longevity` and `sillage` arrive as strings ("7.3") despite being
     *    numeric — cast to float or null.
     *  - `notes_*` arrays default to [] (never null per Pydantic model),
     *    but we guard anyway.
     *  - `gender` is title-cased ("Men", "Women", "Unisex") and mapped to
     *    our enum ("masculine", "feminine", "unisex").
     *
     * @param  array<string, mixed>  $raw  Raw perfume object from the API
     * @return array<string, mixed>  Normalised record ready for DB upsert
     */
    private function normalizeRecord(array $raw): array
    {
        return [
            'name'                => $raw['name'] ?? 'Unknown',
            'brand'               => $raw['brand'] ?? null,
            'release_year'        => isset($raw['release_year']) ? (int) $raw['release_year'] : null,
            'description'         => $raw['description'] ?? null,
            'gender_target'       => $this->mapGender($raw['gender'] ?? null),
            // Cast string values to float — API returns "7.3" not 7.3
            'sillage'             => isset($raw['sillage']) ? (float) $raw['sillage'] : null,
            'longevity'           => isset($raw['longevity']) ? (float) $raw['longevity'] : null,
            'rating'              => isset($raw['rating']) ? (float) $raw['rating'] : null,
            'votes'               => isset($raw['votes']) ? (int) $raw['votes'] : null,
            'remote_image_url'    => $raw['image_url'] ?? null,
            'external_source_url' => $raw['perfume_url'] ?? null,
            // Note arrays — guaranteed non-null by Pydantic, but guarded here
            'notes_top'           => $raw['notes_top'] ?? [],
            'notes_middle'        => $raw['notes_middle'] ?? [],
            'notes_base'          => $raw['notes_base'] ?? [],
        ];
    }

    /**
     * Maps PerfumAPI's gender strings to our database enum values.
     *
     * PerfumAPI returns: "Men", "Women", "Unisex", or null.
     * Our enum requires: 'masculine', 'feminine', 'unisex'.
     */
    private function mapGender(?string $gender): string
    {
        return match (strtolower((string) $gender)) {
            'men'   => 'masculine',
            'women' => 'feminine',
            default => 'unisex',
        };
    }
}
