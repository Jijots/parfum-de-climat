<?php

namespace App\Console\Commands;

use App\Services\CatalogCache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Artisan Command: notes:map
 *
 * Resolves unmapped fragrance note names onto the NoteClimateProfile taxonomy.
 *
 * Why this matters: the recommendation engine only scores notes that carry a
 * climate profile. Notes with note_profile_id = NULL are invisible to it, so
 * every unmapped name is data the weather scorer silently throws away.
 *
 * ── Two passes, deliberately separate ────────────────────────────────────────
 *
 *   --method=normalize   String normalisation, a curated synonym table, and
 *                        fuzzy matching. NOT machine learning. Handles spelling
 *                        and formatting variants ("Black Currant", "Vanille",
 *                        "Bulgarian Rose") exactly and deterministically.
 *
 *   --method=embed       Transformer embeddings. Genuine ML. Handles the
 *                        semantic cases string matching cannot reach, where two
 *                        names mean the same thing but share no letters.
 *
 * Normalisation runs first because it is faster, more predictable, and more
 * accurate on the cases it covers. The embedding pass only sees what is left.
 *
 * ── Safety ───────────────────────────────────────────────────────────────────
 * Dry run is the DEFAULT. Nothing is written without --apply, so the proposed
 * mapping can always be reviewed first. --min-confidence gates which matches
 * are eligible.
 *
 * Usage:
 *   php artisan notes:map                              # dry run, normalise
 *   php artisan notes:map --apply                      # write the mapping
 *   php artisan notes:map --method=embed --apply       # semantic pass
 *   php artisan notes:map --min-confidence=0.95        # only high-confidence
 */
class MapNotesCommand extends Command
{
    protected $signature = 'notes:map
                            {--method=normalize : normalize (string matching) or embed (ML)}
                            {--apply            : Write the mapping. Without this the command is a dry run.}
                            {--min-confidence=0.90 : Reject matches scoring below this}
                            {--limit=0          : Only consider the N most common unmapped names}
                            {--python=          : Override the Python executable}';

    protected $description = 'Map unmapped fragrance notes onto climate profiles (string matching or embeddings)';

    private const SCRIPTS = [
        'normalize' => 'note_mapper.py',
        'embed'     => 'note_embeddings.py',
    ];

    public function handle(): int
    {
        $method        = (string) $this->option('method');
        $apply         = (bool) $this->option('apply');
        $minConfidence = (float) $this->option('min-confidence');
        $limit         = (int) $this->option('limit');
        $pythonBin     = $this->option('python') ?: config('services.engine.python_bin', 'python');

        if (! isset(self::SCRIPTS[$method])) {
            $this->error("Unknown --method '{$method}'. Use: " . implode(', ', array_keys(self::SCRIPTS)));
            return Command::FAILURE;
        }

        $scriptPath = base_path('../engine/' . self::SCRIPTS[$method]);

        if (! file_exists($scriptPath)) {
            $this->error("Script not found: {$scriptPath}");
            return Command::FAILURE;
        }

        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════════╗');
        $this->info('║   Parfum de Climat — Note → Climate Profile Mapping       ║');
        $this->info('╚══════════════════════════════════════════════════════════╝');
        $this->info('');
        $this->line("  Method        : {$method}");
        $this->line("  Min confidence: {$minConfidence}");
        $this->line('  Mode          : ' . ($apply ? '<fg=yellow>APPLY — will write to the database</>' : 'dry run (no writes)'));
        $this->info('');

        // ── Load the taxonomy and the backlog ─────────────────────────────────
        $profiles = DB::table('note_climate_profiles')->orderBy('name')->pluck('name')->all();

        $query = DB::table('fragrance_notes')
            ->whereNull('note_profile_id')
            ->select('raw_note_name', DB::raw('COUNT(*) as c'))
            ->groupBy('raw_note_name')
            ->orderByDesc('c');

        if ($limit > 0) {
            $query->limit($limit);
        }

        $unmapped = $query->get()->map(fn ($r) => [
            'name'  => $r->raw_note_name,
            'count' => (int) $r->c,
        ])->all();

        $totalRows = array_sum(array_column($unmapped, 'count'));

        $this->line('  Climate profiles : ' . count($profiles));
        $this->line('  Unmapped names   : ' . number_format(count($unmapped)) . ' distinct, ' . number_format($totalRows) . ' rows');
        $this->info('');

        if (empty($unmapped)) {
            $this->info('Nothing to map — every note already has a climate profile.');
            return Command::SUCCESS;
        }

        // ── Run the Python matcher ────────────────────────────────────────────
        $result = $this->runMatcher($pythonBin, $scriptPath, [
            'profiles' => $profiles,
            'unmapped' => $unmapped,
        ]);

        if ($result === null) {
            return Command::FAILURE;
        }

        $matches = collect($result['matches'] ?? [])
            ->filter(fn ($m) => (float) $m['confidence'] >= $minConfidence)
            ->values();

        $rejected  = count($result['matches'] ?? []) - $matches->count();
        $matchRows = $matches->sum('count');

        // ── Report ────────────────────────────────────────────────────────────
        $this->line('  <fg=green>Matched</>   : ' . number_format($matches->count()) . ' distinct names, '
                    . number_format($matchRows) . ' note rows');
        if ($rejected > 0) {
            $this->line("  <fg=yellow>Below cut</> : {$rejected} matches under the confidence floor");
        }
        $this->line('  <fg=yellow>Unmatched</> : ' . number_format(count($result['unmatched'] ?? [])) . ' distinct names');
        $this->info('');

        if ($matches->isEmpty()) {
            $this->warn('No matches met the confidence threshold. Nothing to do.');
            return Command::SUCCESS;
        }

        $this->line('  Top matches:');
        $this->table(
            ['Rows', 'Raw note', 'Climate profile', 'Conf', 'Method'],
            $matches->take(20)->map(fn ($m) => [
                number_format($m['count']),
                $m['raw'],
                $m['profile'],
                number_format((float) $m['confidence'], 2),
                $m['method'],
            ])->all()
        );

        // ── Apply ─────────────────────────────────────────────────────────────
        if (! $apply) {
            $this->info('');
            $this->line('  Dry run complete — nothing was written.');
            $this->line('  Re-run with <fg=cyan>--apply</> to commit this mapping.');
            $this->info('');
            $this->reportCoverage();

            return Command::SUCCESS;
        }

        $profileIds = DB::table('note_climate_profiles')->pluck('id', 'name');
        $updated    = 0;

        $bar = $this->output->createProgressBar($matches->count());
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% — %message%');
        $bar->setMessage('writing…');
        $bar->start();

        foreach ($matches as $match) {
            $profileId = $profileIds[$match['profile']] ?? null;

            if (! $profileId) {
                $bar->advance();
                continue;
            }

            // Scoped to rows that are still unmapped, so re-running never
            // overwrites a mapping a human has since corrected by hand.
            $updated += DB::table('fragrance_notes')
                ->where('raw_note_name', $match['raw'])
                ->whereNull('note_profile_id')
                ->update(['note_profile_id' => $profileId, 'updated_at' => now()]);

            $bar->advance();
        }

        $bar->setMessage('done');
        $bar->finish();
        $this->info('');
        $this->info('');

        $version = app(CatalogCache::class)->flush();
        $this->line("  Catalog cache invalidated (namespace v{$version}).");

        Log::info('[MapNotes] Mapping applied', [
            'method'  => $method,
            'names'   => $matches->count(),
            'rows'    => $updated,
        ]);

        $this->info('');
        $this->line('  <fg=green>Updated ' . number_format($updated) . ' note rows.</>');
        $this->info('');
        $this->reportCoverage();

        return Command::SUCCESS;
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Run a Python matcher script and decode its result.
     *
     * Nothing travels through a pipe — not the payload, not the result, and not
     * the child's console output. Every stream is a file.
     *
     * The payload uses a file for the obvious reason: a multi-megabyte JSON body
     * overflows the OS pipe buffer and blocks the writer.
     *
     * stdout and stderr use files for a subtler reason. Reading two pipes
     * sequentially deadlocks whenever the child writes more to the SECOND
     * stream than its buffer holds: the parent blocks reading stdout to EOF
     * while the child blocks writing stderr, and neither can move. That is not
     * hypothetical here — the embedding pass downloads its model through
     * HuggingFace, which streams progress bars to stderr and overflows the
     * buffer within seconds.
     *
     * stream_select() is the textbook fix and is unavailable: on Windows it
     * supports sockets only, not pipes from proc_open. Files sidestep the whole
     * problem and behave identically on every platform.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>|null  null on failure (already reported)
     */
    private function runMatcher(string $pythonBin, string $scriptPath, array $payload): ?array
    {
        $inputFile  = tempnam(sys_get_temp_dir(), 'parfum_notes_in_');
        $outputFile = tempnam(sys_get_temp_dir(), 'parfum_notes_out_');
        $stdoutFile = tempnam(sys_get_temp_dir(), 'parfum_notes_log_');
        $stderrFile = tempnam(sys_get_temp_dir(), 'parfum_notes_err_');

        file_put_contents($inputFile, json_encode($payload, JSON_THROW_ON_ERROR));

        $this->line('  Running matcher…');

        // Keep the child quiet as well as unblockable. These are belt-and-braces
        // alongside the file redirection: HuggingFace progress bars and
        // transformers' chatter are noise in an Artisan table.
        $env = [
            'HF_HUB_DISABLE_PROGRESS_BARS' => '1',
            'TRANSFORMERS_VERBOSITY'       => 'error',
            'TOKENIZERS_PARALLELISM'       => 'false',
            'PYTHONIOENCODING'             => 'utf-8',
        ] + getenv();

        $process = proc_open(
            [$pythonBin, $scriptPath, $inputFile, $outputFile],
            [
                0 => ['pipe', 'r'],
                1 => ['file', $stdoutFile, 'w'],
                2 => ['file', $stderrFile, 'w'],
            ],
            $pipes,
            dirname($scriptPath),
            $env,
        );

        if (! is_resource($process)) {
            @unlink($inputFile);
            @unlink($outputFile);
            @unlink($stdoutFile);
            @unlink($stderrFile);
            $this->error("Could not spawn Python. Is '{$pythonBin}' on your PATH?");

            return null;
        }

        fclose($pipes[0]);
        $exitCode = proc_close($process);

        $stdout = (string) @file_get_contents($stdoutFile);
        $stderr = (string) @file_get_contents($stderrFile);

        @unlink($inputFile);
        @unlink($stdoutFile);
        @unlink($stderrFile);

        if ($exitCode !== 0) {
            @unlink($outputFile);
            $this->error("Matcher exited with code {$exitCode}.");

            if ($stderr) {
                $this->line(trim($stderr));
            }

            if (str_contains($stderr, 'sentence_transformers') || str_contains($stderr, 'sentence-transformers')) {
                $this->line('');
                $this->line('  The embedding pass needs its own dependencies:');
                $this->line('  <fg=cyan>pip install -r engine/requirements-similarity.txt</>');
            }

            return null;
        }

        if (trim($stdout)) {
            $this->line('  Matcher reported: ' . trim($stdout));
        }

        try {
            return json_decode(file_get_contents($outputFile), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->error("Could not parse matcher output: {$e->getMessage()}");

            return null;
        } finally {
            @unlink($outputFile);
        }
    }

    /**
     * Print how much of the catalog the weather engine can actually see.
     */
    private function reportCoverage(): void
    {
        $total  = DB::table('fragrance_notes')->count();
        $mapped = DB::table('fragrance_notes')->whereNotNull('note_profile_id')->count();
        $pct    = $total > 0 ? $mapped / $total * 100 : 0.0;

        $this->line(sprintf(
            '  Note coverage: <fg=green>%s</> of %s rows mapped (<fg=green>%.1f%%</>) — this is the share',
            number_format($mapped),
            number_format($total),
            $pct
        ));
        $this->line('  of note data the weather engine can actually score on.');
    }
}
