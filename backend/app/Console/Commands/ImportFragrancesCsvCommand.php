<?php

namespace App\Console\Commands;

use App\Models\Fragrance;
use App\Models\FragranceNote;
use App\Models\NoteClimateProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Artisan Command: fragrances:import-csv
 *
 * Imports fragrance data from the community-compiled Fragrantica dataset CSV
 * (fra_cleaned.csv). Uses bulk DB operations — one INSERT per chunk instead of
 * one query per row — so the full ~100k dataset completes in 20–40 minutes
 * rather than several hours.
 *
 * CSV format (semicolon-delimited):
 *   url ; Perfume (slug) ; Brand ; Country ; Gender ; Rating Value ; Rating Count ;
 *   Year ; Top (comma-sep) ; Middle (comma-sep) ; Base (comma-sep) ;
 *   Perfumer1 ; Perfumer2 ; mainaccord1 … mainaccord5
 *
 * Usage:
 *   # Import everything
 *   php artisan fragrances:import-csv --file=/path/to/fra_cleaned.csv
 *
 *   # Dry run — shows stats without writing to DB
 *   php artisan fragrances:import-csv --file=/path/to/fra_cleaned.csv --dry-run
 *
 *   # Import first 5000 rows only
 *   php artisan fragrances:import-csv --file=/path/to/fra_cleaned.csv --limit=5000
 *
 *   # Overwrite records that already exist
 *   php artisan fragrances:import-csv --file=/path/to/fra_cleaned.csv --force
 */
class ImportFragrancesCsvCommand extends Command
{
    protected $signature = 'fragrances:import-csv
                            {--file=        : Absolute path to fra_cleaned.csv}
                            {--limit=0      : Stop after N rows (0 = all)}
                            {--force        : Overwrite existing records}
                            {--dry-run      : Parse and report without writing to DB}
                            {--chunk=200    : Rows per database batch (lower if you hit timeouts)}';

    protected $description = 'Import fragrances from the Fragrantica community CSV dataset (bulk-optimised)';

    private int $created       = 0;
    private int $updated       = 0;
    private int $skipped       = 0;
    private int $failed        = 0;
    private int $notesMapped   = 0;
    private int $notesUnmapped = 0;

    /**
     * Fragrantica perfume ids already present in the database, as a set.
     *
     * Loaded once up front rather than queried per chunk: identity is the
     * trailing id in the source URL, and matching on that in SQL would need a
     * LIKE per candidate, which no index can serve. Roughly 24k short strings,
     * so a few MB of memory buys an O(1) check for every row in the file.
     *
     * @var array<string, true>
     */
    private array $existingPerfumeIds = [];

    public function handle(): int
    {
        $file      = $this->option('file');
        $limit     = (int) $this->option('limit');
        $force     = (bool) $this->option('force');
        $dryRun    = (bool) $this->option('dry-run');
        $chunkSize = max(1, (int) $this->option('chunk'));

        // ── Validate ──────────────────────────────────────────────────────────
        if (! $file) {
            $this->error('Please provide --file=/path/to/fra_cleaned.csv');
            return Command::FAILURE;
        }

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");
            return Command::FAILURE;
        }

        $this->info('');
        $this->info('╔══════════════════════════════════════════════════╗');
        $this->info('║   Parfum de Climat — CSV Fragrance Import (Bulk)  ║');
        $this->info('╚══════════════════════════════════════════════════╝');
        $this->info('');
        $this->line("  File      : {$file}");
        $this->line("  Limit     : " . ($limit > 0 ? number_format($limit) : 'all rows'));
        $this->line("  Chunk     : {$chunkSize} rows per batch");
        $this->line("  Force     : " . ($force  ? 'yes — overwrite existing' : 'no — skip existing'));
        $this->line("  Dry run   : " . ($dryRun ? 'yes — no DB writes' : 'no'));
        $this->info('');

        // ── Pre-load note climate profiles ────────────────────────────────────
        $profileIndex = NoteClimateProfile::all()
            ->keyBy(fn ($p) => strtolower(trim($p->name)))
            ->map(fn ($p) => $p->id);

        $this->line("  Loaded {$profileIndex->count()} note climate profile(s) for mapping.");
        $this->info('');

        // ── Count rows for the progress bar ───────────────────────────────────
        $totalRows = 0;
        $fh = fopen($file, 'r');
        while (fgets($fh) !== false) {
            $totalRows++;
        }
        fclose($fh);
        $totalRows--; // subtract header row

        $rowsToProcess = ($limit > 0) ? min($limit, $totalRows) : $totalRows;
        $this->line("  Rows in file    : " . number_format($totalRows));
        $this->line("  Rows to process : " . number_format($rowsToProcess));
        $this->line("  Estimated chunks: " . number_format((int) ceil($rowsToProcess / $chunkSize)));
        $this->info('');

        // ── Open CSV ──────────────────────────────────────────────────────────
        $fh     = fopen($file, 'r');
        $header = fgetcsv($fh, 0, ';');

        if (! $header || strtolower(trim($header[0])) !== 'url') {
            $this->error('Unexpected CSV format. First column must be "url". Is this fra_cleaned.csv?');
            fclose($fh);
            return Command::FAILURE;
        }

        $col = array_flip(array_map(fn ($h) => strtolower(trim($h)), $header));

        // ── Preload existing identities ───────────────────────────────────────
        if (! $dryRun) {
            $this->line('  Indexing existing fragrances for deduplication…');

            $this->existingPerfumeIds = Fragrance::query()
                ->whereNotNull('external_source_url')
                ->pluck('external_source_url')
                ->mapWithKeys(function ($url) {
                    $pid = self::perfumeIdFromUrl($url);

                    return $pid === null ? [] : [$pid => true];
                })
                ->all();

            $this->line('  Known perfume ids: ' . number_format(count($this->existingPerfumeIds)));
            $this->info('');
        }

        // ── Progress bar ──────────────────────────────────────────────────────
        $bar = $this->output->createProgressBar($rowsToProcess);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% — %message%');
        $bar->setMessage('starting…');
        $bar->start();

        // ── Stream CSV in chunks ──────────────────────────────────────────────
        $chunk     = [];
        $processed = 0;

        while (($fields = fgetcsv($fh, 0, ';')) !== false) {
            if ($limit > 0 && $processed >= $limit) {
                break;
            }

            $chunk[] = $fields;
            $processed++;

            if (count($chunk) >= $chunkSize) {
                $bar->setMessage('writing batch…');
                $this->processChunk($chunk, $col, $profileIndex, $force, $dryRun);
                $bar->advance(count($chunk));
                $chunk = [];
            }
        }

        if ($chunk) {
            $bar->setMessage('writing final batch…');
            $this->processChunk($chunk, $col, $profileIndex, $force, $dryRun);
            $bar->advance(count($chunk));
        }

        fclose($fh);

        $bar->setMessage('done');
        $bar->finish();

        $this->info('');
        $this->info('');

        // ── Summary ───────────────────────────────────────────────────────────
        $this->table(
            ['Result', 'Count'],
            [
                ['<fg=green>Created</>',        number_format($this->created)],
                ['<fg=cyan>Updated</>',          number_format($this->updated)],
                ['<fg=yellow>Skipped</>',        number_format($this->skipped)],
                ['<fg=red>Failed (chunks)</>',   number_format($this->failed)],
                ['Notes mapped',                 number_format($this->notesMapped)],
                ['<fg=yellow>Notes unmapped</>', number_format($this->notesUnmapped)],
            ]
        );

        if (! $dryRun && $this->notesUnmapped > 0) {
            $this->info('');
            $this->warn("  {$this->notesUnmapped} note(s) could not be matched to a climate profile.");
            $this->line('  → Admin panel surfaces these so you can map or create profiles for them.');
        }

        if ($this->failed > 0) {
            $this->warn("  {$this->failed} chunk(s) failed — see storage/logs/laravel.log for details.");
        }

        $this->info('');
        $this->info($dryRun ? 'Dry run complete — no data written.' : 'Import complete.');

        return Command::SUCCESS;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Chunk processor
    //
    // One chunk = one round-trip to the DB for fragrances + one for notes.
    // Each chunk is wrapped in a transaction for atomicity: if the notes insert
    // fails, the fragrance inserts for that chunk are rolled back too so there
    // are no orphaned records.
    // ─────────────────────────────────────────────────────────────────────────

    private function processChunk(
        array $rows,
        array $col,
        \Illuminate\Support\Collection $profileIndex,
        bool $force,
        bool $dryRun
    ): void {
        $fragranceData = [];
        $notesByUrl    = [];
        $now           = now()->toDateTimeString();

        // ── Parse rows ────────────────────────────────────────────────────────
        foreach ($rows as $fields) {
            $url = trim($fields[$col['url']] ?? '');
            if (! $url) {
                $this->skipped++;
                continue;
            }

            $top   = $this->parseNotes($fields[$col['top']]    ?? '');
            $heart = $this->parseNotes($fields[$col['middle']] ?? '');
            $base  = $this->parseNotes($fields[$col['base']]   ?? '');

            if ($dryRun) {
                foreach ([...$top, ...$heart, ...$base] as $note) {
                    $profileIndex->has(strtolower($note))
                        ? $this->notesMapped++
                        : $this->notesUnmapped++;
                }
                $this->created++;
                continue;
            }

            $fragranceData[] = [
                'name'                => $this->slugToName($fields[$col['perfume']] ?? ''),
                'brand'               => $this->titleCase($fields[$col['brand']] ?? 'Unknown'),
                'release_year'        => $this->parseInt($fields[$col['year']] ?? ''),
                'gender_target'       => $this->mapGender($fields[$col['gender']] ?? ''),
                'rating'              => $this->parseDecimal($fields[$col['rating value']] ?? ''),
                'votes'               => $this->parseInt(str_replace(',', '', $fields[$col['rating count']] ?? '')),
                'external_source'     => 'perfumapi',
                'external_source_url' => $url,
                'last_synced_at'      => $now,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ];

            $notesByUrl[$url] = ['top' => $top, 'heart' => $heart, 'base' => $base];
        }

        if (empty($fragranceData)) {
            return;
        }

        // ── Write to DB ───────────────────────────────────────────────────────
        try {
            DB::transaction(function () use ($fragranceData, $notesByUrl, $profileIndex, $force, $now) {

                $allUrls = array_column($fragranceData, 'external_source_url');

                // Identity is the Fragrantica perfume id, NOT the URL string.
                //
                // Matching on the raw URL is case-sensitive, and the source
                // emits both casings for the same page:
                //
                //   .../perfume/Zoologist-Perfumes/Snowy-Owl-64381.html
                //   .../perfume/zoologist-perfumes/snowy-owl-64381.html
                //
                // A second run therefore saw a URL it had never stored and
                // inserted a duplicate row. That produced 225 duplicate pairs
                // before it was noticed, and was invisible to a uniqueness
                // check because every URL string genuinely was distinct.
                //
                // The trailing id is stable across casing, so keying on it
                // makes the import idempotent no matter how the source
                // capitalises its paths.
                // $this->existingPerfumeIds is loaded once before the import
                // begins, so matching costs nothing per chunk. Querying per
                // chunk would mean a 200-clause OR of ILIKE patterns, none of
                // which can use an index.
                $isExisting = function (array $f): bool {
                    $pid = self::perfumeIdFromUrl($f['external_source_url']);

                    // No id in the URL means it cannot be matched this way.
                    // Treat it as new; the unique index on external_source_url
                    // is the backstop.
                    return $pid !== null && isset($this->existingPerfumeIds[$pid]);
                };

                $newRows      = array_values(array_filter($fragranceData, fn ($f) => ! $isExisting($f)));
                $existingRows = array_values(array_filter($fragranceData, fn ($f) => $isExisting($f)));

                // Register the newcomers so a later chunk — or a duplicate row
                // inside the SAME file — does not insert them a second time.
                foreach ($newRows as $row) {
                    $pid = self::perfumeIdFromUrl($row['external_source_url']);
                    if ($pid !== null) {
                        $this->existingPerfumeIds[$pid] = true;
                    }
                }

                // Bulk-insert new fragrances
                if ($newRows) {
                    Fragrance::insert($newRows);
                    $this->created += count($newRows);
                }

                // Upsert existing fragrances only when --force
                if ($force && $existingRows) {
                    Fragrance::upsert(
                        $existingRows,
                        ['external_source_url'],
                        ['name', 'brand', 'release_year', 'gender_target', 'rating', 'votes', 'last_synced_at', 'updated_at']
                    );
                    $this->updated += count($existingRows);
                } else {
                    $this->skipped += count($existingRows);
                }

                // Decide which URLs get their notes written
                $urlsForNotes = $force
                    ? $allUrls
                    : array_column($newRows, 'external_source_url');

                if (empty($urlsForNotes)) {
                    return;
                }

                // Fetch IDs for the fragrances we'll insert notes for
                $fragrances = Fragrance::whereIn('external_source_url', $urlsForNotes)
                    ->get()
                    ->keyBy('external_source_url');

                // Delete stale notes (covers force-update re-sync and any prior partial run)
                FragranceNote::whereIn('fragrance_id', $fragrances->pluck('id'))->delete();

                // Build the flat notes array
                $notesData = [];
                foreach ($fragrances as $url => $fragrance) {
                    $groups = $notesByUrl[$url] ?? [];

                    foreach (['top', 'heart', 'base'] as $layer) {
                        $seen = [];
                        foreach ($groups[$layer] ?? [] as $note) {
                            if (! $note || isset($seen[$note])) {
                                continue;
                            }
                            $seen[$note] = true;

                            $profileId = $profileIndex->get(strtolower($note));
                            $profileId ? $this->notesMapped++ : $this->notesUnmapped++;

                            $notesData[] = [
                                'fragrance_id'    => $fragrance->id,
                                'raw_note_name'   => $note,
                                'note_profile_id' => $profileId,
                                'layer'           => $layer,
                                'created_at'      => $now,
                                'updated_at'      => $now,
                            ];
                        }
                    }
                }

                // Sub-chunk to stay well under PostgreSQL's 65535 parameter limit
                foreach (array_chunk($notesData, 500) as $batch) {
                    FragranceNote::insert($batch);
                }
            });

        } catch (\Throwable $e) {
            $this->failed++;
            Log::error('[ImportFragrancesCsv] Chunk failed', [
                'first_url' => $fragranceData[0]['external_source_url'] ?? 'unknown',
                'count'     => count($fragranceData),
                'error'     => $e->getMessage(),
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Normalisation helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Pull the Fragrantica perfume id out of a source URL.
     *
     * .../perfume/Zoologist-Perfumes/Snowy-Owl-64381.html  ->  "64381"
     *
     * That id is the fragrance's real identity: it survives the casing and
     * slug differences that make the full URL an unreliable dedup key.
     * Returns null when the URL does not end in an id, so callers can fall
     * back rather than guess.
     */
    private static function perfumeIdFromUrl(?string $url): ?string
    {
        if ($url === null || ! preg_match('/-(\d+)\.html?$/i', $url, $m)) {
            return null;
        }

        return $m[1];
    }

    /** "accento-overdose-pride-edition" → "Accento Overdose Pride Edition" */
    private function slugToName(string $slug): string
    {
        return ucwords(str_replace('-', ' ', trim($slug)));
    }

    private function titleCase(string $value): string
    {
        return ucwords(strtolower(trim($value)));
    }

    /** "rose, jasmine, lily of the valley" → ["Rose", "Jasmine", "Lily Of The Valley"] */
    private function parseNotes(string $raw): array
    {
        if (! $raw || strtolower(trim($raw)) === 'unknown') {
            return [];
        }

        // Some rows in fra_cleaned.csv contain non-UTF-8 byte sequences
        // (Windows-1252 trademark symbols, Latin-1 accented chars from Portuguese/
        // Spanish brands). PostgreSQL rejects them; mb_scrub replaces invalid
        // sequences with U+FFFD before we write the name.
        $raw = mb_scrub($raw, 'UTF-8');

        return array_values(array_filter(
            array_map(fn ($n) => ucwords(strtolower(trim($n))), explode(',', $raw))
        ));
    }

    /** "1,42" → 1.42 | "7.3" → 7.3 | "" → null */
    private function parseDecimal(string $value): ?float
    {
        $clean = trim(str_replace(',', '.', $value));
        return is_numeric($clean) ? (float) $clean : null;
    }

    private function parseInt(string $value): ?int
    {
        $clean = trim($value);
        return is_numeric($clean) ? (int) $clean : null;
    }

    private function mapGender(string $gender): string
    {
        return match (strtolower(trim($gender))) {
            'men'   => 'masculine',
            'women' => 'feminine',
            default => 'unisex',
        };
    }
}
