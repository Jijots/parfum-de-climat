<?php

namespace App\Console\Commands;

use App\Models\Fragrance;
use App\Models\FragranceNote;
use App\Models\NoteClimateProfile;
use App\Services\PerfumApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Artisan Command: fragrances:import
 *
 * Populates the local fragrance catalog by pulling data from PerfumAPI
 * (github.com/seccaz/PerfumAPI — a Fragrantica scraper).
 *
 * After this command runs successfully, our MySQL DB is the complete source
 * of truth for all fragrance queries. The app NEVER calls PerfumAPI at runtime.
 *
 * ── What this command does ───────────────────────────────────────────────────
 *  1. Fetches paginated fragrance data from PerfumAPI (search or full list)
 *  2. Upserts each fragrance using `external_source_url` as the dedup key
 *  3. Syncs top/heart/base notes, linking them to NoteClimateProfiles where
 *     a case-insensitive name match exists
 *  4. Downloads and caches bottle images to Laravel Storage
 *  5. Reports progress via a console progress bar
 *  6. Continues processing on individual record failures (non-fatal errors)
 *
 * ── Usage examples ───────────────────────────────────────────────────────────
 *
 *   # Import the first 100 fragrances from the full catalog
 *   php artisan fragrances:import
 *
 *   # Search for a specific brand and import all results
 *   php artisan fragrances:import --search="Maison Francis Kurkdjian"
 *
 *   # Import 500 records starting from offset 200
 *   php artisan fragrances:import --limit=500 --offset=200
 *
 *   # Re-import all records, overwriting existing data
 *   php artisan fragrances:import --force
 *
 *   # Skip image caching (faster for testing)
 *   php artisan fragrances:import --no-images
 *
 * ── Risk mitigations ─────────────────────────────────────────────────────────
 *  - Each fragrance is wrapped in its own DB transaction → failure mid-import
 *    never leaves partial records or orphaned notes
 *  - Upsert dedup prevents duplicates on re-runs
 *  - Existing locally-edited fragrances (last_synced_at != null && manual edits)
 *    are skipped unless --force is passed
 *  - Image downloads time out gracefully; missing images are logged but don't
 *    block the record from being saved
 */
class ImportFragrancesCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'fragrances:import
                            {--search=    : Search term to filter fragrances (e.g. brand name)}
                            {--limit=100  : Number of records to fetch (max 500 for list, 200 for search)}
                            {--offset=0   : Number of records to skip (list mode only)}
                            {--force      : Overwrite existing records even if they have local edits}
                            {--no-images  : Skip downloading and caching bottle images}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import fragrances from PerfumAPI (Fragrantica scraper) into the local database';

    /** Counts for the final summary report */
    private int $created  = 0;
    private int $updated  = 0;
    private int $skipped  = 0;
    private int $failed   = 0;

    public function __construct(
        private readonly PerfumApiService $perfumApi
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('');
        $this->info('╔═══════════════════════════════════════╗');
        $this->info('║   Parfum de Climat — Fragrance Import  ║');
        $this->info('╚═══════════════════════════════════════╝');
        $this->info('');

        $search   = $this->option('search');
        $limit    = (int) $this->option('limit');
        $offset   = (int) $this->option('offset');
        $force    = $this->option('force');
        $noImages = $this->option('no-images');

        // ── Fetch records from PerfumAPI ──────────────────────────────────
        $this->info('Connecting to PerfumAPI...');

        if ($search) {
            $this->line("  Mode: Search — query: \"{$search}\"");
            $records = $this->perfumApi->search($search, min($limit, 200));

            if (empty($records)) {
                $this->warn('No results returned for that search query. The PerfumAPI instance may be unavailable or the database is sparse.');
                return Command::FAILURE;
            }
        } else {
            $this->line("  Mode: List — limit: {$limit}, offset: {$offset}");
            $response = $this->perfumApi->list($limit, $offset);

            if ($response === null) {
                $this->error('Failed to connect to PerfumAPI. Check PERFUMAPI_BASE_URL in .env and ensure the service is reachable.');
                $this->line('  Tip: Run a local PerfumAPI instance for reliable imports: see github.com/seccaz/PerfumAPI');
                return Command::FAILURE;
            }

            $records = $response['perfumes'];
            $total   = $response['total'];
            $this->line("  Total in PerfumAPI database: " . number_format($total));
        }

        $count = count($records);
        $this->info("  Fetched {$count} fragrance(s) to process.");
        $this->info('');

        if ($count === 0) {
            $this->warn('No fragrances returned. Nothing to import.');
            return Command::SUCCESS;
        }

        // ── Pre-load NoteClimateProfile name index ────────────────────────
        // Build a name → id lookup table in memory to avoid N+1 queries
        // when matching each note against our profiles during import.
        $profileIndex = NoteClimateProfile::all()
            ->keyBy(fn ($p) => strtolower(trim($p->name)))
            ->map(fn ($p) => $p->id);

        $this->line("  Loaded {$profileIndex->count()} note climate profiles for mapping.");
        $this->info('');

        // ── Process each record ───────────────────────────────────────────
        $bar = $this->output->createProgressBar($count);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% — %message%');
        $bar->start();

        foreach ($records as $record) {
            $bar->setMessage(Str::limit($record['name'] ?? '...', 30));

            $this->importRecord($record, $profileIndex, $force, $noImages);

            $bar->advance();
        }

        $bar->finish();
        $this->info('');
        $this->info('');

        // ── Summary report ────────────────────────────────────────────────
        $this->table(
            ['Result', 'Count'],
            [
                ['<fg=green>Created</>',  $this->created],
                ['<fg=cyan>Updated</>',   $this->updated],
                ['<fg=yellow>Skipped</>', $this->skipped],
                ['<fg=red>Failed</>',     $this->failed],
            ]
        );

        if ($this->failed > 0) {
            $this->warn("  {$this->failed} record(s) failed. Check storage/logs/laravel.log for details.");
        }

        $unmappedCount = FragranceNote::whereNull('note_profile_id')->count();
        if ($unmappedCount > 0) {
            $this->info('');
            $this->warn("  {$unmappedCount} fragrance note(s) could not be auto-mapped to a climate profile.");
            $this->line("  → Run the admin panel's 'Unmapped Notes' tool to resolve them.");
            $this->line("  → Or: GET /api/v1/admin/fragrances/unmapped-notes");
        }

        $this->info('');
        $this->info('Import complete.');

        return Command::SUCCESS;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Import a single fragrance record from PerfumAPI.
     *
     * Wrapped in a DB transaction — if note insertion fails, the fragrance
     * record is also rolled back, leaving the DB in a consistent state.
     *
     * @param  array<string, mixed>  $record       Normalised PerfumAPI record
     * @param  \Illuminate\Support\Collection  $profileIndex  name → id map
     * @param  bool  $force     Overwrite existing local edits
     * @param  bool  $noImages  Skip image caching
     */
    private function importRecord(
        array $record,
        \Illuminate\Support\Collection $profileIndex,
        bool $force,
        bool $noImages
    ): void {
        // Records without a source URL cannot be deduped — skip them
        if (empty($record['external_source_url'])) {
            $this->skipped++;
            Log::warning('[ImportFragrances] Skipped record with no external_source_url', [
                'name' => $record['name'] ?? 'unknown',
            ]);
            return;
        }

        try {
            DB::transaction(function () use ($record, $profileIndex, $force, $noImages) {

                // ── Upsert the fragrance record ───────────────────────────
                $existing = Fragrance::where('external_source_url', $record['external_source_url'])
                                     ->first();

                if ($existing && ! $force) {
                    // Skip records that already exist and haven't been forced
                    $this->skipped++;
                    return;
                }

                $imageData = $this->resolveImage($record, $existing, $noImages);

                $fragranceData = [
                    'name'                => $record['name'],
                    'brand'               => $record['brand'] ?? 'Unknown',
                    'release_year'        => $record['release_year'],
                    'description'         => $record['description'],
                    'gender_target'       => $record['gender_target'],
                    'sillage'             => $record['sillage'],
                    'longevity'           => $record['longevity'],
                    'rating'              => $record['rating'],
                    'votes'               => $record['votes'],
                    'remote_image_url'    => $record['remote_image_url'],
                    'cached_image_path'   => $imageData,
                    'external_source'     => 'perfumapi',
                    'external_source_url' => $record['external_source_url'],
                    'last_synced_at'      => now(),
                    'is_active'           => true,
                ];

                if ($existing) {
                    $existing->update($fragranceData);
                    $fragrance = $existing;
                    $this->updated++;
                } else {
                    $fragrance = Fragrance::create($fragranceData);
                    $this->created++;
                }

                // ── Sync notes ────────────────────────────────────────────
                // Delete existing notes and re-insert on each sync.
                // This handles cases where Fragrantica updates note data.
                $fragrance->notes()->delete();

                $this->insertNotes($fragrance->id, $record['notes_top'],    'top',   $profileIndex);
                $this->insertNotes($fragrance->id, $record['notes_middle'], 'heart', $profileIndex);
                $this->insertNotes($fragrance->id, $record['notes_base'],   'base',  $profileIndex);
            });

        } catch (\Throwable $e) {
            $this->failed++;
            Log::error('[ImportFragrances] Failed to import record', [
                'name'    => $record['name'] ?? 'unknown',
                'url'     => $record['external_source_url'] ?? 'none',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * Insert a set of notes for one pyramid layer, linking to climate profiles.
     *
     * Uses the pre-loaded $profileIndex for O(1) lookups instead of
     * a DB query per note. Unmapped notes (no profile match) are saved
     * with note_profile_id = null and surfaced in the admin panel.
     *
     * @param  int                              $fragranceId
     * @param  list<string>                     $noteNames     Raw note strings from API
     * @param  string                           $layer         'top'|'heart'|'base'
     * @param  \Illuminate\Support\Collection   $profileIndex  Lower-case name → profile id
     */
    private function insertNotes(
        int $fragranceId,
        array $noteNames,
        string $layer,
        \Illuminate\Support\Collection $profileIndex
    ): void {
        // Deduplicate notes within the same layer (API occasionally sends duplicates)
        $seen = [];
        foreach ($noteNames as $rawName) {
            // Normalize to Title Case for consistent display and matching
            $normalized = Str::title(trim($rawName));
            if (isset($seen[$normalized])) continue;
            $seen[$normalized] = true;

            // Attempt case-insensitive match against our profile index
            $profileId = $profileIndex->get(strtolower($normalized));

            FragranceNote::create([
                'fragrance_id'    => $fragranceId,
                'raw_note_name'   => $normalized,
                'note_profile_id' => $profileId,
                'layer'           => $layer,
            ]);
        }
    }

    /**
     * Download and cache the fragrance bottle image to Laravel Storage.
     *
     * Returns the relative storage path if successful, or the existing
     * cached path if the image was already downloaded, or null if skipped/failed.
     *
     * Images are stored at: storage/app/public/fragrances/{filename}
     * Served at: /storage/fragrances/{filename}
     *
     * @param  array<string, mixed>  $record    Normalised API record
     * @param  Fragrance|null        $existing  Existing DB record (for re-use of cached path)
     * @param  bool                  $noImages  If true, skips download entirely
     * @return string|null  Laravel Storage relative path or null
     */
    private function resolveImage(array $record, ?Fragrance $existing, bool $noImages): ?string
    {
        // Re-use the existing cached image if already downloaded
        if ($existing?->cached_image_path) {
            return $existing->cached_image_path;
        }

        if ($noImages || empty($record['remote_image_url'])) {
            return null;
        }

        try {
            $response = Http::timeout(15)->get($record['remote_image_url']);

            if (! $response->successful()) {
                return null;
            }

            // Derive a safe filename from the source URL
            $originalName = basename(parse_url($record['remote_image_url'], PHP_URL_PATH) ?? '');
            $extension    = pathinfo($originalName, PATHINFO_EXTENSION) ?: 'jpg';
            $filename     = Str::slug($record['name'] . '-' . $record['brand']) . '.' . $extension;
            $path         = 'fragrances/' . $filename;

            Storage::disk('public')->put($path, $response->body());

            return $path;

        } catch (\Throwable $e) {
            Log::warning('[ImportFragrances] Image download failed', [
                'url'   => $record['remote_image_url'],
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
