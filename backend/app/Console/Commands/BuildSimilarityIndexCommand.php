<?php

namespace App\Console\Commands;

use App\Models\Fragrance;
use App\Models\FragranceSimilarity;
use App\Services\CatalogCache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Artisan Command: fragrances:build-similarity-index
 *
 * Populates the fragrance_similarities table by running the Python
 * TF-IDF + cosine similarity engine (engine/similarity.py).
 *
 * How the ML works (in plain terms):
 *   1. Every fragrance's note names are joined into a "document"
 *      (e.g., "bergamot_rose oud_musk sandalwood").
 *   2. TF-IDF turns each document into a vector of numbers — notes that
 *      appear in only a few fragrances get high values (distinctive),
 *      notes in almost every fragrance (like Musk) get low values.
 *   3. Cosine similarity measures how much two vectors "point the same way".
 *      High score = similar note composition. Low score = very different.
 *   4. We keep only the top-10 similar fragrances per fragrance and store
 *      them in the DB, so the web request is just a cheap DB lookup.
 *
 * Prerequisites:
 *   pip install -r engine/requirements-similarity.txt
 *
 * Usage:
 *   php artisan fragrances:build-similarity-index
 *   php artisan fragrances:build-similarity-index --limit=1000   (test run)
 */
class BuildSimilarityIndexCommand extends Command
{
    protected $signature = 'fragrances:build-similarity-index
                            {--limit=0 : Process only N fragrances (0 = all; for testing)}
                            {--python=  : Override the Python executable path}';

    protected $description = 'Build the TF-IDF cosine similarity index for "similar fragrances" recommendations';

    public function handle(): int
    {
        $limit      = (int) $this->option('limit');
        $pythonBin  = $this->option('python') ?: config('services.engine.python_bin', 'python');
        $scriptPath = base_path('../engine/similarity.py');

        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════════╗');
        $this->info('║   Parfum de Climat — Fragrance Similarity Index Builder   ║');
        $this->info('╚══════════════════════════════════════════════════════════╝');
        $this->info('');

        // ── Validate environment ──────────────────────────────────────────────
        if (! file_exists($scriptPath)) {
            $this->error("Python script not found at: {$scriptPath}");
            return Command::FAILURE;
        }

        // ── Load fragrance corpus ─────────────────────────────────────────────
        $this->line('  Loading fragrances and their notes from the database…');

        $query = Fragrance::query()
            ->where('is_active', true)
            ->with(['notes:fragrance_id,raw_note_name']);

        if ($limit > 0) {
            $query->limit($limit);
            $this->line("  ⚠  Limit mode: processing {$limit} fragrances only.");
        }

        $fragrances = $query->get(['id'])->map(fn ($f) => [
            'id'    => $f->id,
            'notes' => $f->notes->pluck('raw_note_name')->filter()->values()->toArray(),
        ])->filter(fn ($f) => count($f['notes']) > 0)->values();

        $count = $fragrances->count();
        $this->line("  Fragrances with notes: " . number_format($count));

        if ($count < 2) {
            $this->error('Need at least 2 fragrances with notes to build similarity index.');
            return Command::FAILURE;
        }

        // ── Write corpus to temp file ─────────────────────────────────────────
        // We use a file instead of stdin to avoid pipe buffer deadlocks with
        // large payloads. The Python script reads from the file path argument.
        $corpusFile = tempnam(sys_get_temp_dir(), 'parfum_corpus_');
        $outputFile = tempnam(sys_get_temp_dir(), 'parfum_pairs_');
        file_put_contents($corpusFile, json_encode($fragrances->all(), JSON_THROW_ON_ERROR));

        $corpusMb = round(filesize($corpusFile) / 1024 / 1024, 1);
        $this->line("  Corpus size: {$corpusMb} MB");
        $this->info('');

        // ── Spawn Python subprocess ───────────────────────────────────────────
        $this->line('  Running TF-IDF + cosine similarity engine…');
        $this->line("  (This may take 2–5 minutes for {$count} fragrances.)");
        $this->info('');

        // Results travel via $outputFile, NOT through a pipe.
        //
        // Piping the payload through stdout deadlocks: the OS pipe buffer is only
        // a few KB, so Python blocks mid-write while PHP waits for output that
        // never arrives. stream_select() cannot rescue it either — on Windows it
        // supports sockets only, not pipes from proc_open, so the read loop never
        // fires. Files sidestep the problem entirely and behave the same on every
        // platform. The pipes below carry only a short status line.
        $descriptors = [
            0 => ['pipe', 'r'],   // stdin  — unused
            1 => ['pipe', 'w'],   // stdout — short status line ("OK <count>")
            2 => ['pipe', 'w'],   // stderr — short error text
        ];

        $process = proc_open(
            [$pythonBin, $scriptPath, $corpusFile, $outputFile],
            $descriptors,
            $pipes,
            dirname($scriptPath),
        );

        if (! is_resource($process)) {
            @unlink($corpusFile);
            @unlink($outputFile);
            $this->error("Failed to spawn Python process. Is '{$pythonBin}' in your PATH?");
            return Command::FAILURE;
        }

        fclose($pipes[0]);

        // Safe to read sequentially — both streams carry only a few bytes.
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        @unlink($corpusFile);

        // ── Check result ──────────────────────────────────────────────────────
        if ($exitCode !== 0) {
            @unlink($outputFile);
            $this->error("Python engine exited with code {$exitCode}.");

            if ($stderr) {
                $this->line(trim($stderr));
            }

            if (str_contains($stderr, 'scikit-learn')) {
                $this->line('');
                $this->line('  Install the similarity dependencies:');
                $this->line('  <fg=cyan>pip install -r engine/requirements-similarity.txt</>');
            }

            return Command::FAILURE;
        }

        if (trim($stdout)) {
            $this->line('  Engine reported: ' . trim($stdout));
        }

        try {
            $result = json_decode(
                file_get_contents($outputFile), true, 512, JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $e) {
            @unlink($outputFile);
            $this->error("Could not parse the engine's output file: {$e->getMessage()}");
            return Command::FAILURE;
        } finally {
            @unlink($outputFile);
        }

        $pairs = $result['pairs'] ?? [];

        $this->line('  Engine finished. Similarity pairs: ' . number_format(count($pairs)));

        if (empty($pairs)) {
            $this->warn('No similarity pairs generated. Check that fragrances have varied note profiles.');
            return Command::SUCCESS;
        }

        // ── Persist to database ───────────────────────────────────────────────
        $this->line('');
        $this->line('  Storing results in fragrance_similarities…');

        DB::table('fragrance_similarities')->truncate();

        $now       = now()->toDateTimeString();
        $chunkSize = 1000;
        $inserted  = 0;

        $rows = array_map(fn ($pair) => [
            'fragrance_id'          => $pair['fragrance_id'],
            'similar_fragrance_id'  => $pair['similar_id'],
            'score'                 => $pair['score'],
            'rank'                  => $pair['rank'],
            'created_at'            => $now,
            'updated_at'            => $now,
        ], $pairs);

        $bar = $this->output->createProgressBar(count($rows));
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%%');
        $bar->start();

        foreach (array_chunk($rows, $chunkSize) as $chunk) {
            DB::table('fragrance_similarities')->insert($chunk);
            $inserted += count($chunk);
            $bar->advance(count($chunk));
        }

        $bar->finish();
        $this->info('');
        $this->info('');

        // The old similar-lists are now wrong, so retire the whole cache
        // namespace in one write. See CatalogCache::flush().
        $version = app(CatalogCache::class)->flush();
        $this->line("  Catalog cache invalidated (namespace v{$version}).");
        $this->info('');

        Log::info('[BuildSimilarityIndex] Index rebuilt', [
            'fragrances' => $count,
            'pairs'      => count($pairs),
        ]);

        $this->table(
            ['Metric', 'Value'],
            [
                ['Fragrances indexed',   number_format($count)],
                ['Similarity pairs',     number_format(count($pairs))],
                ['Avg neighbors/fragrance', round(count($pairs) / $count, 1)],
            ]
        );

        $this->info('Index built successfully. Run this command whenever you import new fragrances.');

        return Command::SUCCESS;
    }
}
