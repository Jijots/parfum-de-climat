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
                            {--chunk=100  : Fragrances to process per batch}
                            {--skip=0     : Skip the first N fragrances (resume after failure)}';

    protected $description = 'Copy fragrances + notes from Railway MySQL to the default (PostgreSQL) database';

    public function handle(): int
    {
        // ── Validate required options ─────────────────────────────────────────
        foreach (['host', 'database', 'username', 'password'] as $opt) {
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
            'password'  => $this->option('password'),
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
        $source->table('fragrances')
            ->orderBy('id')
            ->skip($skip)
            ->chunk($chunk, function ($rows) use (
                $source, $profileIndex, $bar,
                &$created, &$skippedExist, &$failed, &$notesCopied, &$notesSkipped
            ) {
                foreach ($rows as $row) {
                    $bar->setMessage(mb_strimwidth((string) $row->name, 0, 40, '…'));

                    try {
                        DB::transaction(function () use (
                            $row, $source, $profileIndex,
                            &$created, &$skippedExist, &$notesCopied, &$notesSkipped
                        ) {
                            // ── Upsert fragrance ──────────────────────────────
                            $key = $row->external_source_url ?: null;

                            if ($key) {
                                $existing = Fragrance::where('external_source_url', $key)->first();
                            } else {
                                // No URL key — match by name + brand
                                $existing = Fragrance::where('name', $row->name)
                                    ->where('brand', $row->brand)
                                    ->first();
                            }

                            if ($existing) {
                                $newFragrance = $existing;
                                $skippedExist++;
                            } else {
                                $newFragrance = Fragrance::create([
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
                                    'external_source_url' => $key,
                                    'image_path'          => $row->image_path ?? null,
                                    'is_active'           => (bool) $row->is_active,
                                ]);
                                $created++;
                            }

                            // ── Copy notes (re-mapped by raw_note_name) ───────
                            $sourceNotes = $source->table('fragrance_notes')
                                ->where('fragrance_id', $row->id)
                                ->get(['raw_note_name', 'layer']);

                            foreach ($sourceNotes as $note) {
                                $profileId = $profileIndex->get(
                                    strtolower(trim((string) $note->raw_note_name))
                                );

                                $inserted = DB::table('fragrance_notes')->insertOrIgnore([[
                                    'fragrance_id'    => $newFragrance->id,
                                    'raw_note_name'   => $note->raw_note_name,
                                    'note_profile_id' => $profileId,  // null if unmapped
                                    'layer'           => $note->layer,
                                    'created_at'      => now(),
                                    'updated_at'      => now(),
                                ]]);

                                $inserted ? $notesCopied++ : $notesSkipped++;
                            }
                        });
                    } catch (\Throwable $e) {
                        $failed++;
                        \Illuminate\Support\Facades\Log::error('[MigrateFragrances] Failed', [
                            'fragrance_id' => $row->id ?? null,
                            'name'         => $row->name ?? null,
                            'error'        => $e->getMessage(),
                        ]);
                    }

                    $bar->advance();
                }
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
