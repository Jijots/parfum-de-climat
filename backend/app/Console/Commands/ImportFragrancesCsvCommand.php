<?php

namespace App\Console\Commands;

use App\Models\Fragrance;
use App\Models\FragranceNote;
use App\Models\NoteClimateProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Artisan Command: fragrances:import-csv
 *
 * Imports fragrance data from the community-compiled Fragrantica dataset CSV
 * (fra_cleaned.csv). Each row is upserted using the Fragrantica URL as the
 * deduplication key, so the command is safe to re-run.
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
 *   # Import first 500 rows only (useful for testing)
 *   php artisan fragrances:import-csv --file=/path/to/fra_cleaned.csv --limit=500
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
                            {--dry-run      : Parse and report without writing to DB}';

    protected $description = 'Import fragrances from the Fragrantica community CSV dataset';

    // ── Counters ──────────────────────────────────────────────────────────────
    private int $created   = 0;
    private int $updated   = 0;
    private int $skipped   = 0;
    private int $failed    = 0;
    private int $notesMapped   = 0;
    private int $notesUnmapped = 0;

    public function handle(): int
    {
        $file   = $this->option('file');
        $limit  = (int) $this->option('limit');
        $force  = (bool) $this->option('force');
        $dryRun = (bool) $this->option('dry-run');

        // ── Validate file path ────────────────────────────────────────────────
        if (! $file) {
            $this->error('Please provide --file=/path/to/fra_cleaned.csv');
            return Command::FAILURE;
        }

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");
            return Command::FAILURE;
        }

        $this->info('');
        $this->info('╔══════════════════════════════════════════════╗');
        $this->info('║   Parfum de Climat — CSV Fragrance Import     ║');
        $this->info('╚══════════════════════════════════════════════╝');
        $this->info('');
        $this->line("  File   : {$file}");
        $this->line("  Limit  : " . ($limit > 0 ? $limit : 'all'));
        $this->line("  Force  : " . ($force  ? 'yes' : 'no'));
        $this->line("  Dry run: " . ($dryRun ? 'yes — no DB writes' : 'no'));
        $this->info('');

        // ── Pre-load note climate profiles into memory ────────────────────────
        // Keyed by lowercase name for O(1) lookups per note.
        $profileIndex = NoteClimateProfile::all()
            ->keyBy(fn ($p) => strtolower(trim($p->name)))
            ->map(fn ($p) => $p->id);

        $this->line("  Loaded {$profileIndex->count()} note climate profile(s) for mapping.");
        $this->info('');

        // ── Count rows for progress bar ───────────────────────────────────────
        $totalRows = 0;
        $fh = fopen($file, 'r');
        while (fgets($fh) !== false) $totalRows++;
        fclose($fh);
        $totalRows--; // subtract header row

        $rowsToProcess = ($limit > 0) ? min($limit, $totalRows) : $totalRows;
        $this->line("  Rows in file : " . number_format($totalRows));
        $this->line("  Rows to import: " . number_format($rowsToProcess));
        $this->info('');

        // ── Open CSV and process ──────────────────────────────────────────────
        $fh = fopen($file, 'r');

        // Read and validate header row
        $header = fgetcsv($fh, 0, ';');
        if (! $header || strtolower(trim($header[0])) !== 'url') {
            $this->error('Unexpected CSV format. First column should be "url". Is this fra_cleaned.csv?');
            fclose($fh);
            return Command::FAILURE;
        }

        // Map column names to indexes for resilience against column reordering
        $cols = array_map(fn ($h) => strtolower(trim($h)), $header);
        $col  = array_flip($cols);

        $bar = $this->output->createProgressBar($rowsToProcess);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% — %message%');
        $bar->start();

        $row = 0;
        while (($fields = fgetcsv($fh, 0, ';')) !== false) {
            if ($limit > 0 && $row >= $limit) break;
            $row++;

            $name = $this->slugToName($fields[$col['perfume']] ?? '');
            $bar->setMessage(Str::limit($name ?: '...', 40));

            if (! $dryRun) {
                $this->importRow($fields, $col, $profileIndex, $force);
            } else {
                // Dry-run: just count mapped/unmapped notes
                foreach (['top', 'middle', 'base'] as $layer) {
                    foreach ($this->parseNotes($fields[$col[$layer]] ?? '') as $note) {
                        $profileIndex->has(strtolower($note))
                            ? $this->notesMapped++
                            : $this->notesUnmapped++;
                    }
                }
                $this->created++; // count as "would create"
            }

            $bar->advance();
        }

        fclose($fh);

        $bar->finish();
        $this->info('');
        $this->info('');

        // ── Summary ───────────────────────────────────────────────────────────
        $this->table(
            ['Result', 'Count'],
            [
                ['<fg=green>Created</>',         $this->created],
                ['<fg=cyan>Updated</>',           $this->updated],
                ['<fg=yellow>Skipped</>',         $this->skipped],
                ['<fg=red>Failed</>',             $this->failed],
                ['Notes mapped',                  $this->notesMapped],
                ['<fg=yellow>Notes unmapped</>', $this->notesUnmapped],
            ]
        );

        if (! $dryRun && $this->notesUnmapped > 0) {
            $this->info('');
            $this->warn("  {$this->notesUnmapped} note(s) could not be matched to a climate profile.");
            $this->line('  → Admin panel surfaces these so you can map or create profiles for them.');
        }

        if ($this->failed > 0) {
            $this->warn("  {$this->failed} row(s) failed — see storage/logs/laravel.log for details.");
        }

        $this->info('');
        $this->info($dryRun ? 'Dry run complete — no data written.' : 'Import complete.');

        return Command::SUCCESS;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Per-row import
    // ─────────────────────────────────────────────────────────────────────────

    private function importRow(
        array $fields,
        array $col,
        \Illuminate\Support\Collection $profileIndex,
        bool $force
    ): void {
        $url = trim($fields[$col['url']] ?? '');

        if (! $url) {
            $this->skipped++;
            return;
        }

        try {
            DB::transaction(function () use ($fields, $col, $url, $profileIndex, $force) {

                $existing = Fragrance::where('external_source_url', $url)->first();

                if ($existing && ! $force) {
                    $this->skipped++;
                    return;
                }

                $data = [
                    'name'                => $this->slugToName($fields[$col['perfume']] ?? ''),
                    'brand'               => $this->titleCase($fields[$col['brand']] ?? 'Unknown'),
                    'release_year'        => $this->parseInt($fields[$col['year']] ?? ''),
                    'gender_target'       => $this->mapGender($fields[$col['gender']] ?? ''),
                    'rating'              => $this->parseDecimal($fields[$col['rating value']] ?? ''),
                    'votes'               => $this->parseInt(str_replace(',', '', $fields[$col['rating count']] ?? '')),
                    'external_source'     => 'perfumapi',
                    'external_source_url' => $url,
                    'last_synced_at'      => now(),
                    'is_active'           => true,
                ];

                if ($existing) {
                    $existing->update($data);
                    $fragrance = $existing;
                    $this->updated++;
                } else {
                    $fragrance = Fragrance::create($data);
                    $this->created++;
                }

                // Sync notes: delete old, re-insert fresh
                $fragrance->notes()->delete();

                $this->insertNotes($fragrance->id, $this->parseNotes($fields[$col['top']]    ?? ''), 'top',   $profileIndex);
                $this->insertNotes($fragrance->id, $this->parseNotes($fields[$col['middle']] ?? ''), 'heart', $profileIndex);
                $this->insertNotes($fragrance->id, $this->parseNotes($fields[$col['base']]   ?? ''), 'base',  $profileIndex);
            });

        } catch (\Throwable $e) {
            $this->failed++;
            Log::error('[ImportFragrancesCsv] Failed row', [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function insertNotes(
        int $fragranceId,
        array $noteNames,
        string $layer,
        \Illuminate\Support\Collection $profileIndex
    ): void {
        $seen = [];
        foreach ($noteNames as $raw) {
            $normalized = $this->titleCase($raw);
            if (! $normalized || isset($seen[$normalized])) continue;
            $seen[$normalized] = true;

            $profileId = $profileIndex->get(strtolower($normalized));

            $profileId ? $this->notesMapped++ : $this->notesUnmapped++;

            try {
                FragranceNote::create([
                    'fragrance_id'    => $fragranceId,
                    'raw_note_name'   => $normalized,
                    'note_profile_id' => $profileId,
                    'layer'           => $layer,
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                // Unique constraint: same note already inserted — skip silently
                if (str_contains($e->getMessage(), 'uq_frag_note_layer')) return;
                throw $e;
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Data normalisation helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Convert a Fragrantica URL slug to a display name.
     * "accento-overdose-pride-edition" → "Accento Overdose Pride Edition"
     */
    private function slugToName(string $slug): string
    {
        return ucwords(str_replace('-', ' ', trim($slug)));
    }

    /** Title-case a string, preserving existing mixed-case words. */
    private function titleCase(string $value): string
    {
        return ucwords(strtolower(trim($value)));
    }

    /**
     * Split a comma-separated note string into a trimmed array.
     * "rose, jasmine, lily-of-the-valley" → ["Rose", "Jasmine", "Lily-Of-The-Valley"]
     */
    private function parseNotes(string $raw): array
    {
        if (! $raw || strtolower(trim($raw)) === 'unknown') return [];

        return array_values(array_filter(
            array_map(fn ($n) => ucwords(strtolower(trim($n))), explode(',', $raw))
        ));
    }

    /**
     * Parse a European-format decimal (comma as separator) to float.
     * "1,42" → 1.42 | "7.3" → 7.3 | "" → null
     */
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

    /**
     * Map CSV gender strings to the fragrance table enum.
     * "men" → "masculine" | "women" → "feminine" | * → "unisex"
     */
    private function mapGender(string $gender): string
    {
        return match (strtolower(trim($gender))) {
            'men'   => 'masculine',
            'women' => 'feminine',
            default => 'unisex',
        };
    }
}
