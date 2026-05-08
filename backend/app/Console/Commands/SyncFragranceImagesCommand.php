<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Artisan Command: fragrances:sync-images
 *
 * Derives and stores the remote_image_url for every Fragrantica fragrance
 * by extracting the numeric ID from its external_source_url.
 *
 * Fragrantica URL pattern:
 *   https://www.fragrantica.com/perfume/Brand/Name-12345.html
 *                                                       ↑ ID
 * Image CDN pattern:
 *   https://fimgs.net/mdimg/perfume/375x500.12345.jpg
 *
 * This runs entirely against the default DB connection — no MySQL source
 * needed.  Safe to run on Render directly after the initial migration.
 *
 * Usage:
 *   php artisan fragrances:sync-images
 *   php artisan fragrances:sync-images --force   # overwrite existing URLs too
 *   php artisan fragrances:sync-images --dry-run # preview without writing
 */
class SyncFragranceImagesCommand extends Command
{
    protected $signature = 'fragrances:sync-images
                            {--force   : Also overwrite rows that already have a remote_image_url}
                            {--dry-run : Preview only — do not write to the database}
                            {--chunk=500 : Rows per batch}';

    protected $description = 'Derive and store remote_image_url for all Fragrantica fragrances';

    public function handle(): int
    {
        $force  = $this->option('force');
        $dryRun = $this->option('dry-run');
        $chunk  = (int) $this->option('chunk');

        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════╗');
        $this->info('║   Parfum de Climat — Derive Fragrance Image URLs      ║');
        $this->info('╚══════════════════════════════════════════════════════╝');
        $this->info('');

        $query = DB::table('fragrances')
            ->whereNotNull('external_source_url')
            ->where('external_source_url', 'like', '%fragrantica.com%');

        if (! $force) {
            $query->whereNull('remote_image_url');
        }

        $total = $query->count();

        $this->line("  Fragrances to process: " . number_format($total));
        $this->line("  Mode: " . ($dryRun ? 'dry-run (no writes)' : ($force ? 'overwrite all' : 'fill nulls only')));
        $this->info('');

        if ($total === 0) {
            $this->info('Nothing to update — all Fragrantica fragrances already have image URLs.');
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% — %message%');
        $bar->setMessage('starting…');
        $bar->start();

        $updated   = 0;
        $skipped   = 0;   // URL could not be derived (no numeric ID in URL)
        $noChange  = 0;   // dry-run or already had URL

        (clone $query)
            ->orderBy('id')
            ->select(['id', 'external_source_url'])
            ->chunk($chunk, function ($rows) use ($dryRun, &$updated, &$skipped, &$noChange, $bar) {

                $pairs = []; // [id => image_url]

                foreach ($rows as $row) {
                    $imageUrl = $this->deriveImageUrl($row->external_source_url);

                    if (! $imageUrl) {
                        $skipped++;
                        continue;
                    }

                    $pairs[$row->id] = $imageUrl;
                }

                if (! $dryRun && ! empty($pairs)) {
                    // Batch update via CASE WHEN for efficiency
                    $ids = array_keys($pairs);

                    DB::transaction(function () use ($pairs, $ids, &$updated) {
                        // Build a CASE expression: CASE id WHEN 1 THEN 'url1' WHEN 2 THEN 'url2' ... END
                        $cases = implode(' ', array_map(
                            fn ($id, $url) => "WHEN {$id} THEN " . DB::getPdo()->quote($url),
                            $ids,
                            array_values($pairs)
                        ));

                        $inList = implode(',', $ids);

                        DB::statement("
                            UPDATE fragrances
                            SET remote_image_url = CASE id {$cases} END,
                                updated_at = NOW()
                            WHERE id IN ({$inList})
                        ");

                        $updated += count($pairs);
                    });
                } elseif ($dryRun) {
                    $noChange += count($pairs);
                    // Show a sample
                    if ($noChange <= 5 && ! empty($pairs)) {
                        $this->newLine();
                        foreach (array_slice($pairs, 0, 2, true) as $id => $url) {
                            $this->line("  [dry-run] id={$id} → {$url}");
                        }
                    }
                }

                $bar->advance(count($rows));
            });

        $bar->setMessage('done');
        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Result', 'Count'],
            [
                [$dryRun ? '<fg=cyan>Would update</>' : '<fg=green>Image URLs set</>',
                    number_format($dryRun ? $noChange : $updated)],
                ['<fg=yellow>Skipped (no ID in URL)</>', number_format($skipped)],
            ]
        );

        $this->info('');
        if (! $dryRun) {
            $this->info('Done! Fragrance images are now linked in the database.');
            $this->line('  Images served from: https://fimgs.net/mdimg/perfume/375x500.{ID}.jpg');
        }

        return Command::SUCCESS;
    }

    /**
     * Derive the Fragrantica CDN image URL from a Fragrantica page URL.
     *
     * Input:  https://www.fragrantica.com/perfume/Chanel/Bleu-de-Chanel-28562.html
     * Output: https://fimgs.net/mdimg/perfume/375x500.28562.jpg
     *
     * Returns null if no numeric ID can be found in the URL.
     */
    private function deriveImageUrl(string $sourceUrl): ?string
    {
        // Extract the last numeric segment before .html
        // Pattern: -<digits>.html at the end of the path
        if (preg_match('/-(\d+)\.html$/i', $sourceUrl, $matches)) {
            $id = $matches[1];
            return "https://fimgs.net/mdimg/perfume/375x500.{$id}.jpg";
        }

        return null;
    }
}
