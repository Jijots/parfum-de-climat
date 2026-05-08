<?php

namespace App\Console\Commands;

use App\Models\Fragrance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CacheFragranceImagesCommand extends Command
{
    protected $signature = 'fragrances:cache-images
                            {--limit=500 : Max number of images to download in one run}
                            {--force : Re-download even if the DB path is set (file may not exist)}';

    protected $description = 'Download missing fragrance bottle images to R2 (or local public disk as fallback)';

    public function handle(): int
    {
        $limit  = (int) $this->option('limit');
        $force  = (bool) $this->option('force');
        $disk   = config('filesystems.disks.r2.key') ? 'r2' : 'public';

        $this->info("Using disk: {$disk}");

        $fragrances = Fragrance::whereNotNull('remote_image_url')
            ->select(['id', 'name', 'brand', 'remote_image_url', 'cached_image_path'])
            ->limit($limit * 3)
            ->get();

        $downloaded = 0;
        $skipped    = 0;
        $failed     = 0;

        foreach ($fragrances as $fragrance) {
            if ($downloaded >= $limit) {
                break;
            }

            if ($fragrance->cached_image_path && Storage::disk($disk)->exists($fragrance->cached_image_path) && ! $force) {
                $skipped++;
                continue;
            }

            try {
                $response = Http::timeout(15)->get($fragrance->remote_image_url);

                if (! $response->successful()) {
                    $failed++;
                    continue;
                }

                $originalName = basename(parse_url($fragrance->remote_image_url, PHP_URL_PATH) ?? '');
                $extension    = pathinfo($originalName, PATHINFO_EXTENSION) ?: 'jpg';
                $filename     = Str::slug($fragrance->name . '-' . $fragrance->brand) . '.' . $extension;
                $path         = 'fragrances/' . $filename;

                Storage::disk($disk)->put($path, $response->body(), 'public');

                $fragrance->update(['cached_image_path' => $path]);

                $downloaded++;

                if ($downloaded % 50 === 0) {
                    $this->info("Downloaded {$downloaded} images...");
                }

            } catch (\Throwable $e) {
                $failed++;
            }
        }

        $this->info("Done. Downloaded: {$downloaded}, Skipped (already cached): {$skipped}, Failed: {$failed}");

        return self::SUCCESS;
    }
}
