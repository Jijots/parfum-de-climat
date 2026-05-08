<?php

namespace App\Console\Commands;

use App\Models\Fragrance;
use App\Models\NoteClimateProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Artisan Command: fragrances:migrate-to-pgsql
 *
 * One-shot migration: reads every fragrance + its notes from a source
 * MySQL database (Railway) and upserts them into the default DB connection
 * (Render PostgreSQL).
 *
 * Notes are re-mapped by raw_note_name so the note_profile_id foreign keys
 * are correct for the new database's NoteClimateProfile rows (which may have
 * different auto-increment IDs from the seeder).
 *
 * Usage:
 *   php artisan fragrances:migrate-to-pgsql \
 *       --host=interchange.proxy.rlwy.net \
 *       --port=13072 \
 *       --database=railway \
 *       --username=root \
 *       --password=YOUR_PASSWORD
 *
 * Prerequisites:
 *   1. pdo_mysql extension enabled in XAMPP (already is by default)
 *   2. pdo_pgsql extension enabled in XAMPP (uncomment in php.ini)
 *   3. Local .env default connection pointing at Render PostgreSQL external URL
 *   4. php artisan migrate --force  (schema created)
 *   5. php artisan db:seed --force --class=NoteClimateProfileSeeder  (profiles seeded)
 */
class MigrateFragrancesToPgsqlCommand extends Command
{
    protected $signature = 'fragrances:migrate-to-pgsql
                            {--host=      : Source MySQL host (Railway public proxy)}
                            {--port=3306  : Source MySQL port}
                            {--database=  : Source MySQL database name}
                            {--username=  : Source MySQL username}
                            {--password=  : Source MySQL password}
                            {--chunk=500  : Fragrances to process per batch}
                            {--skip=0     : Skip the first N fragrances (resume after failure)}';

    protected $description = 'Copy fragrances + notes from Railway MySQL to the default (PostgreSQL) database';

    public function handle(): int
    {
        // ── Validate required options ─────────────────────────────────────────
        foreach (['host', 'database', 'username'] as $opt) {
            if (! $this->option($opt)) {
                $this->error("Missing required option: --{$opt}");
                return Command::FAILURE;
            }
        }

        // ── Register source connection at runtime ─────────────────────────────
        config(['database.connections.mysql_source' => [
            'driver'    => 'mysql',
            'host'      => $this->option('host'),
            'port'      => (int) $this->option('port'),
            'database'  => $this->option('database'),
            'username'  => $this->option('username'),
            'password'  => $this->option('password') ?? '',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'strict'    => false,
        ]]);

        $source = DB::connection('mysql_source');

        // ── Quick connectivity check ──────────────────────────────────────────
        try {
            $source->getPdo();
        } catch (\Throwable $e) {
            $this->error('Cannot connect to source MySQL: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════╗');
        $this->info('║   Parfum de Climat — MySQL → PostgreSQL Migration     ║');
        $this->info('╚══════════════════════════════════════════════════════╝');
        $this->info('');

        // ── Load note climate profiles from the TARGET DB ─────────────────────
        // Keyed by lowercase name so we can remap note_profile_id correctly
        // (IDs in the new DB may differ from the source DB).
        $profileIndex = NoteClimateProfile::all()
            ->keyBy(fn ($p) => strtolower(trim($p->name)))
            ->map(fn ($p) => $p->id);

        $this->line("  Target DB: " . config('database.default'));
        $this->line("  Note climate profiles loaded: {$profileIndex->count()}");

        if ($profileIndex->isEmpty()) {
            $this->error('No note climate profiles found in the target DB.');
            $this->error('Run: php artisan db:seed --force --class=NoteClimateProfileSeeder first.');
            return Command::FAILURE;
        }

        // ── Count source rows ─────────────────────────────────────────────────
        $total = $source->table('fragrances')->count();
        $skip  = (int) $this->option('skip');
        $chunk = (int) $this->option('chunk');
        $toProcess = max(0, $total - $skip);

        $this->line("  Source fragrances: " . number_format($total));
        $this->line("  Skipping first:    " . number_format($skip));
        $this->line("  Will process:      " . number_format($toProcess));
        $this->info('');

        if ($toProcess === 0) {
            $this->warn('Nothing to process. Lower --skip or check the source DB.');
            return Command::SUCCESS;
        }

        // ── Progress bar ──────────────────────────────────────────────────────
        $bar = $this->output->createProgressBar($toProcess);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% — %message%');
        $bar->setMessage('starting…');
        $bar->start();

        $created      = 0;
        $skippedExist = 0;
        $failed       = 0;
        $notesCopied  = 0;
        $notesSkipped = 0;

        // ── Stream fragrances in chunks ───────────────────────────────────────
        //
        // Bulk strategy: the entire chunk is processed in ~6 DB queries total,
        // regardless of chunk size. This eliminates per-row round trips to
        // Render PostgreSQL (which has ~200ms latency from the Philippines).
        //
        //   Query 1 — source MySQL:  fetch all notes for chunk (1 × whereIn)
        //   Query 2 — target PgSQL:  find already-existing fragrances (1 × whereIn)
        //   Query 3 — target PgSQL:  bulk insert new fragrances (1 × insert)
        //   Query 4 — target PgSQL:  fetch target IDs for new fragrances (1 × whereIn)
        //   Query 5 — target PgSQL:  bulk insert all notes (1 × insertOrIgnore)
        //
        $source->table('fragrances')
            ->orderBy('id')
            ->skip($skip)
            ->chunk($chunk, function ($rows) use (
                $source, $profileIndex, $bar,
                &$created, &$skippedExist, &$failed, &$notesCopied, &$notesSkipped
            ) {
                $now       = now();
                $sourceIds = $rows->pluck('id')->all();

                $bar->setMessage('fetching chunk ' . $sourceIds[0] . '–' . end($sourceIds));

                try {
                    DB::transaction(function () use (
                        $rows, $source, $profileIndex, $sourceIds, $now,
                        &$created, &$skippedExist, &$notesCopied, &$notesSkipped
                    ) {
                        // ── Q1: fetch ALL notes for this chunk from source MySQL ──
                        $allSourceNotes = $source->table('fragrance_notes')
                            ->whereIn('fragrance_id', $sourceIds)
                            ->get(['fragrance_id', 'raw_note_name', 'layer'])
                            ->groupBy('fragrance_id');

                        // ── Q2: which fragrances already exist in target? ─────────
                        $sourceUrls     = $rows->pluck('external_source_url')->filter()->values()->all();
                        $existingByUrl  = Fragrance::whereIn('external_source_url', $sourceUrls)
                            ->pluck('id', 'external_source_url'); // [url => target_id]

                        // ── Q3: bulk insert only the NEW fragrances ───────────────
                        $toInsert   = [];
                        $nullUrlRows = []; // rows without a URL — handled individually below

                        foreach ($rows as $row) {
                            $url = $row->external_source_url ?: null;

                            if (! $url) {
                                $nullUrlRows[] = $row;
                                continue;
                            }

                            if (isset($existingByUrl[$url])) {
                                $skippedExist++;
                                continue;
                            }

                            $toInsert[] = [
                                'name'                => $row->name,
                                'brand'               => $row->brand,
                                'release_year'        => $row->release_year,
                                'gender_target'       => $row->gender_target,
                                'concentration'       => $row->concentration ?? null,
                                'sillage'             => $row->sillage ?? null,
                                'longevity'           => $row->longevity ?? null,
                                'rating'              => $row->rating ?? null,
                                'votes'               => $row->votes ?? null,
                                'description'         => $row->description ?? null,
                                'external_source'     => $row->external_source ?? null,
                                'external_source_url' => $url,
                                'is_active'           => (bool) $row->is_active,
                                'created_at'          => $now,
                                'updated_at'          => $now,
                            ];
                        }

                        if (! empty($toInsert)) {
                            DB::table('fragrances')->insertOrIgnore($toInsert);
                            $created += count($toInsert);
                        }

                        // Handle null-URL rows individually (rare — admin-created fragrances)
                        $nullUrlTargetIds = []; // source_id => target_id
                        foreach ($nullUrlRows as $row) {
                            $existing = Fragrance::where('name', $row->name)
                                ->where('brand', $row->brand)
                                ->first();
                            if ($existing) {
                                $nullUrlTargetIds[$row->id] = $existing->id;
                                $skippedExist++;
                            } else {
                                $f = Fragrance::create([
                                    'name'          => $row->name,
                                    'brand'         => $row->brand,
                                    'release_year'  => $row->release_year,
                                    'gender_target' => $row->gender_target,
                                    'sillage'       => $row->sillage ?? null,
                                    'longevity'     => $row->longevity ?? null,
                                    'rating'        => $row->rating ?? null,
                                    'votes'         => $row->votes ?? null,
                                    'is_active'     => (bool) $row->is_active,
                                ]);
                                $nullUrlTargetIds[$row->id] = $f->id;
                                $created++;
                            }
                        }

                        // ── Q4: fetch target IDs for all URL-based rows ───────────
                        $targetByUrl = Fragrance::whereIn('external_source_url', $sourceUrls)
                            ->pluck('id', 'external_source_url');

                        // ── Build source_id → target_id map ──────────────────────
                        $idMap = $nullUrlTargetIds;
                        foreach ($rows as $row) {
                            $url = $row->external_source_url ?: null;
                            if ($url && isset($targetByUrl[$url])) {
                                $idMap[$row->id] = $targetByUrl[$url];
                            }
                        }

                        // ── Q5: bulk insert ALL notes for this chunk ──────────────
                        $allNoteRows = [];
                        foreach ($rows as $row) {
                            $targetId = $idMap[$row->id] ?? null;
                            if (! $targetId) continue;

                            foreach ($allSourceNotes->get($row->id, collect()) as $note) {
                                $allNoteRows[] = [
                                    'fragrance_id'    => $targetId,
                                    'raw_note_name'   => $note->raw_note_name,
                                    'note_profile_id' => $profileIndex->get(strtolower(trim((string) $note->raw_note_name))),
                                    'layer'           => $note->layer,
                                    'created_at'      => $now,
                                    'updated_at'      => $now,
                                ];
                            }
                        }

                        if (! empty($allNoteRows)) {
                            $inserted      = DB::table('fragrance_notes')->insertOrIgnore($allNoteRows);
                            $notesCopied  += $inserted;
                            $notesSkipped += count($allNoteRows) - $inserted;
                        }
                    });
                } catch (\Throwable $e) {
                    $failed += count($rows);
                    \Illuminate\Support\Facades\Log::error('[MigrateFragrances] Chunk failed', [
                        'first_source_id' => $sourceIds[0] ?? null,
                        'error'           => $e->getMessage(),
                    ]);
                }

                $bar->advance(count($rows));
            });

        $bar->setMessage('done');
        $bar->finish();
        $this->newLine(2);

        // ── Summary table ─────────────────────────────────────────────────────
        $this->table(
            ['Result', 'Count'],
            [
                ['<fg=green>Fragrances created</>',        number_format($created)],
                ['<fg=cyan>Fragrances already existed</>', number_format($skippedExist)],
                ['<fg=red>Fragrances failed</>',           number_format($failed)],
                ['<fg=green>Notes copied</>',              number_format($notesCopied)],
                ['<fg=yellow>Notes duplicate (ignored)</>', number_format($notesSkipped)],
            ]
        );

        if ($failed > 0) {
            $this->newLine();
            $this->warn("{$failed} fragrance(s) failed — check storage/logs/laravel.log");
            $this->line('Re-run with --skip=' . ($skip + $created + $skippedExist) . ' to resume from where it left off.');
        }

        $this->info('');
        $this->info('Migration complete!');

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
