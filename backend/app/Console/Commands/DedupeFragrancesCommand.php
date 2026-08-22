<?php

namespace App\Console\Commands;

use App\Models\Fragrance;
use App\Services\CatalogCache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Artisan Command: fragrances:dedupe
 *
 * Retires duplicate fragrance rows and cleans up the naming artefacts left by
 * the imports.
 *
 * ── How duplicates got in ────────────────────────────────────────────────────
 * The importer deduped on external_source_url, which is a case-SENSITIVE
 * comparison. Two runs emitted different casing for the same page:
 *
 *   .../perfume/Zoologist-Perfumes/Snowy-Owl-64381.html
 *   .../perfume/zoologist-perfumes/snowy-owl-64381.html
 *
 * so the second run saw a URL it had never stored and inserted a second row.
 * Every one of the 24,310 URLs is technically distinct, which is exactly why
 * this was invisible to a URL-uniqueness check.
 *
 * ── Why the identity key is the perfume id, not the name ─────────────────────
 * A Fragrantica URL ends in -<perfumeId>.html, and that id is the real
 * identity. Matching on brand + name instead looks reasonable and is wrong:
 * Commodity has five separate products all called "Paper" (ids 71609, 29557,
 * 70020, 70013, 29541 — different years, different ratings). A name-based key
 * flags 392 rows and would destroy those; the id key flags 225 and leaves them
 * alone.
 *
 * ── Why rows are deactivated, not deleted ────────────────────────────────────
 * Losers get is_active = false. That is reversible, keeps foreign keys intact,
 * and means a mistake costs a flag flip rather than a restore. Any wardrobe or
 * weather-log reference pointing at a loser is repointed at the winner first,
 * so nobody loses an entry.
 *
 * Usage:
 *   php artisan fragrances:dedupe                 # dry run — the default
 *   php artisan fragrances:dedupe --apply
 *   php artisan fragrances:dedupe --apply --fix-names
 */
class DedupeFragrancesCommand extends Command
{
    protected $signature = 'fragrances:dedupe
                            {--apply      : Write the changes. Without this the command only reports.}
                            {--fix-names  : Also correct names against their source URL and normalise brand casing}
                            {--deslug-brands : Rewrite hyphenated brand slugs as readable names}';

    protected $description = 'Retire duplicate fragrances and clean up import naming artefacts';

    public function handle(): int
    {
        $apply    = (bool) $this->option('apply');
        $fixNames = (bool) $this->option('fix-names');
        $deslug   = (bool) $this->option('deslug-brands');

        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════════╗');
        $this->info('║   Parfum de Climat — Fragrance Deduplication              ║');
        $this->info('╚══════════════════════════════════════════════════════════╝');
        $this->info('');
        $this->line('  Mode: ' . ($apply ? '<fg=yellow>APPLY — will write to the database</>' : 'dry run (no writes)'));
        $this->info('');

        $rows = DB::table('fragrances')
            ->where('is_active', true)
            ->select('id', 'name', 'brand', 'external_source_url', 'description', 'remote_image_url', 'cached_image_path', 'rating', 'votes', 'release_year')
            ->get();

        $this->line('  Active fragrances: ' . number_format($rows->count()));

        // ── Group by Fragrantica perfume id ───────────────────────────────────
        $byPerfumeId = [];
        $unparsed    = 0;

        foreach ($rows as $row) {
            if (preg_match('/-(\d+)\.html?$/i', (string) $row->external_source_url, $m)) {
                $byPerfumeId[$m[1]][] = $row;
            } else {
                $unparsed++;
            }
        }

        $groups = array_filter($byPerfumeId, fn ($g) => count($g) > 1);

        $this->line('  Distinct perfume ids: ' . number_format(count($byPerfumeId)));
        if ($unparsed > 0) {
            $this->line("  <fg=yellow>Rows with no parseable id: {$unparsed}</> (left untouched)");
        }
        $this->line('  <fg=yellow>Duplicate groups: ' . number_format(count($groups)) . '</>');
        $this->info('');

        // ── Choose a winner per group ─────────────────────────────────────────
        $retire = [];
        $sample = [];

        foreach ($groups as $perfumeId => $group) {
            usort($group, fn ($a, $b) => $this->completeness($b) <=> $this->completeness($a));

            $winner = array_shift($group);

            foreach ($group as $loser) {
                $retire[] = ['winner' => $winner, 'loser' => $loser];

                if (count($sample) < 10) {
                    $sample[] = [
                        $perfumeId,
                        "[{$winner->id}] " . mb_substr($winner->name, 0, 26),
                        "[{$loser->id}] " . mb_substr($loser->name, 0, 26),
                        $this->completeness($winner) . ' vs ' . $this->completeness($loser),
                    ];
                }
            }
        }

        if (empty($retire)) {
            $this->info('  No duplicates found.');

            // Deliberately NOT returning here. The cleanup flags are
            // independent of deduplication, and an early return made them
            // unusable once the duplicates were already cleared.
            if (! $fixNames && ! $deslug) {
                return Command::SUCCESS;
            }
        } else {
            $this->line('  Rows to retire: ' . number_format(count($retire)));
            $this->info('');
            $this->table(['Perfume id', 'Keep', 'Retire', 'Completeness'], $sample);
        }

        // ── References that must move before a row is retired ─────────────────
        $loserIds = array_map(fn ($r) => $r['loser']->id, $retire);

        $collectionRefs = DB::table('user_collections')->whereIn('fragrance_id', $loserIds)->count();
        $logRefs        = DB::table('weather_logs')->whereIn('chosen_fragrance_id', $loserIds)->count();

        $this->info('');
        $this->line('  References pointing at a row being retired:');
        $this->line("    user_collections : {$collectionRefs}");
        $this->line("    weather_logs     : {$logRefs}");

        // ── Naming artefacts ──────────────────────────────────────────────────
        $nameFixes  = $fixNames ? $this->planNameFixes($rows) : [];
        $brandFixes = $fixNames ? $this->planBrandFixes($rows) : [];
        $deslugs    = $deslug   ? $this->planBrandDeslug($rows) : [];

        if ($deslug) {
            $affected = $rows->filter(fn ($r) => isset($deslugs[(string) $r->brand]))->count();

            $this->info('');
            $this->line('  Brand slugs to rewrite: ' . number_format(count($deslugs))
                        . ' (' . number_format($affected) . ' fragrances)');

            foreach (array_slice($deslugs, 0, 8, true) as $from => $to) {
                $this->line("    <fg=gray>{$from}</>  ->  {$to}");
            }
        }

        if ($fixNames) {
            $this->info('');
            $this->line('  Names disagreeing with their source URL: ' . number_format(count($nameFixes)));
            $this->line('  Brand spellings to normalise: ' . number_format(count($brandFixes)));

            foreach (array_slice($nameFixes, 0, 4) as $fix) {
                $this->line("    <fg=gray>{$fix['from']}</>  ->  {$fix['to']}");
            }
            foreach (array_slice($brandFixes, 0, 4, true) as $from => $to) {
                $this->line("    <fg=gray>{$from}</>  ->  {$to}");
            }
        }

        if (! $apply) {
            $this->info('');
            $this->line('  Dry run complete — nothing was written.');
            $this->line('  Re-run with <fg=cyan>--apply</> to commit.');
            $this->info('');

            return Command::SUCCESS;
        }

        // ── Apply ─────────────────────────────────────────────────────────────
        $this->info('');
        $this->line('  Writing…');

        $movedCollections = 0;
        $movedLogs        = 0;
        $retired          = 0;

        DB::transaction(function () use ($retire, &$movedCollections, &$movedLogs, &$retired) {
            foreach ($retire as $pair) {
                $winnerId = $pair['winner']->id;
                $loserId  = $pair['loser']->id;

                // Repoint references BEFORE retiring, so nothing is ever left
                // pointing at an inactive row.
                //
                // A user could already hold both rows. user_collections has a
                // unique (user_id, fragrance_id), so blindly repointing would
                // violate it — drop the redundant row in that case instead.
                $clash = DB::table('user_collections as dup')
                    ->where('dup.fragrance_id', $loserId)
                    ->whereExists(function ($q) use ($winnerId) {
                        $q->select(DB::raw(1))
                          ->from('user_collections as keep')
                          ->whereColumn('keep.user_id', 'dup.user_id')
                          ->where('keep.fragrance_id', $winnerId);
                    })
                    ->delete();

                $movedCollections += DB::table('user_collections')
                    ->where('fragrance_id', $loserId)
                    ->update(['fragrance_id' => $winnerId]);

                $movedLogs += DB::table('weather_logs')
                    ->where('chosen_fragrance_id', $loserId)
                    ->update(['chosen_fragrance_id' => $winnerId]);

                DB::table('fragrances')->where('id', $loserId)->update([
                    'is_active'  => false,
                    'updated_at' => now(),
                ]);

                $retired++;
            }
        });

        $this->line("    retired: {$retired}  |  collections moved: {$movedCollections}  |  logs moved: {$movedLogs}");

        if ($fixNames) {
            $renamed = 0;

            DB::transaction(function () use ($nameFixes, $brandFixes, &$renamed) {
                foreach ($nameFixes as $fix) {
                    $renamed += DB::table('fragrances')
                        ->where('id', $fix['id'])
                        ->update(['name' => $fix['to'], 'updated_at' => now()]);
                }

                foreach ($brandFixes as $from => $to) {
                    DB::table('fragrances')->where('brand', $from)->update(['brand' => $to, 'updated_at' => now()]);
                }
            });

            $this->line("    names cleaned: {$renamed}  |  brand spellings merged: " . count($brandFixes));
        }

        if ($deslug && $deslugs) {
            $touched = 0;

            DB::transaction(function () use ($deslugs, &$touched) {
                foreach ($deslugs as $from => $to) {
                    $touched += DB::table('fragrances')
                        ->where('brand', $from)
                        ->update(['brand' => $to, 'updated_at' => now()]);
                }
            });

            $this->line("    brand slugs rewritten: " . count($deslugs) . " ({$touched} fragrances)");
        }

        $version = app(CatalogCache::class)->flush();
        $this->line("    catalog cache invalidated (namespace v{$version})");

        Log::info('[DedupeFragrances] Applied', [
            'retired'            => $retired,
            'collections_moved'  => $movedCollections,
            'logs_moved'         => $movedLogs,
            'names_fixed'        => count($nameFixes),
            'brands_normalised'  => count($brandFixes),
        ]);

        $this->info('');
        $this->line('  <fg=green>Done.</> The similarity index still references the retired rows —');
        $this->line('  re-run <fg=cyan>php artisan fragrances:build-similarity-index</> to drop them.');
        $this->info('');

        return Command::SUCCESS;
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Score how complete a row is, to decide which of a pair to keep.
     *
     * Weighted by how hard the field is to recover: a description was scraped
     * once and will not come back on its own, whereas a rating is refreshed on
     * every import. Vote count breaks ties, on the assumption the row with more
     * votes was captured later and is closer to current.
     */
    private function completeness(object $row): int
    {
        $score = 0;

        if (! empty($row->description))       $score += 8;
        if (! empty($row->cached_image_path)) $score += 4;
        if (! empty($row->remote_image_url))  $score += 2;
        if ($row->rating !== null)            $score += 1;
        if ($row->release_year !== null)      $score += 1;
        if (($row->votes ?? 0) > 0)           $score += 1;

        return $score;
    }

    /**
     * Rows whose stored name disagrees with the name in their source URL.
     *
     * A Fragrantica URL is /perfume/<brand-slug>/<name-slug>-<id>.html, and the
     * name slug is authoritative — it is what the page itself calls the
     * fragrance. Two separate import artefacts corrupted the stored name:
     *
     *   "Snowy Owl Zoologist Perfumes"   brand appended after a space
     *   "LondonBurberry"                 brand concatenated with no space
     *   "Armani CodeGiorgio Armani"      the same, mid-string
     *
     * An earlier version of this method did string surgery instead: strip the
     * brand off the end of the name. That was wrong twice over. It could not
     * see the no-space form at all, and it could not tell an artefact from a
     * name that legitimately contains its house — it would have rewritten
     * "Bleu de Chanel" to "Bleu de" and "Rouge Hermes" to "Rouge". Comparing
     * against the URL avoids guessing: the slug for those reads
     * "Bleu De Chanel", so they are left alone.
     *
     * The comparison is accent- and punctuation-insensitive, and the stored
     * name wins when both forms agree. Slugs cannot carry accents, so taking
     * the slug wherever it differed would quietly downgrade "Rouge Hermès" to
     * "Rouge Hermes".
     *
     * @return list<array{id: int, from: string, to: string}>
     */
    private function planNameFixes(\Illuminate\Support\Collection $rows): array
    {
        $fixes = [];

        foreach ($rows as $row) {
            $fromUrl = $this->nameFromUrl((string) $row->external_source_url);

            if ($fromUrl === null || $fromUrl === '') {
                continue;
            }

            // Same name, different typography — keep what is stored.
            if ($this->normalise((string) $row->name) === $this->normalise($fromUrl)) {
                continue;
            }

            $fixes[] = ['id' => $row->id, 'from' => (string) $row->name, 'to' => $fromUrl];
        }

        return $fixes;
    }

    /**
     * Extract the fragrance name from a Fragrantica URL.
     *
     * /perfume/Zoologist-Perfumes/Snowy-Owl-64381.html  ->  "Snowy Owl"
     *
     * Returns null when the URL does not match the expected shape, so callers
     * leave the row alone rather than inventing a name.
     */
    private function nameFromUrl(string $url): ?string
    {
        if (! preg_match('#/perfume/[^/]+/(.+)-(\d+)\.html?$#i', $url, $m)) {
            return null;
        }

        return ucwords(strtolower(str_replace('-', ' ', $m[1])));
    }

    /**
     * Rewrite hyphenated brand slugs as readable names.
     *
     * Half the catalog arrived with the brand stored as the URL slug —
     * "Calvin-klein", "O-boticario", "Victoria-s-secret" — which is what the
     * interface then displays.
     *
     * Splitting on hyphens and title-casing is right for the overwhelming
     * majority (Calvin Klein, Hugo Boss, Marc Jacobs). Four patterns are not,
     * and are handled explicitly:
     *
     *   -s- / trailing -s   a possessive: Victoria-s-secret -> Victoria's Secret
     *   leading L-          elided article: L-occitane -> L'Occitane
     *   small words         Boadicea-the-victorious -> Boadicea the Victorious
     *   lost punctuation    the source dropped "&" and "." entirely, so
     *                       "Dolce-gabbana" cannot be recovered by rule —
     *                       those need the override table below
     *
     * @return array<string, string>  slug => readable
     */
    private function planBrandDeslug(\Illuminate\Support\Collection $rows): array
    {
        // Punctuation the source discarded. Only the highest-volume houses are
        // listed; the rule handles everything else acceptably.
        $overrides = [
            'bath-body-works'        => 'Bath & Body Works',
            'dolce-gabbana'          => 'Dolce & Gabbana',
            'abercrombie-fitch'      => 'Abercrombie & Fitch',
            'dsquared2'              => 'DSQUARED²',
            'm-micallef'             => 'M. Micallef',
            'bond-no-9'              => 'Bond No. 9',
            'l-occitane-en-provence' => "L'Occitane en Provence",
            'victoria-s-secret'      => "Victoria's Secret",
            'penhaligon-s'           => "Penhaligon's",
            'e-coudray'              => 'E. Coudray',
            'j-del-pozo'             => 'J. del Pozo',
            's-oliver'               => "s.Oliver",
            'l-t-piver'              => 'L.T. Piver',
            'o-driu'                 => "O'Driù",
        ];

        // Kept lowercase unless they lead the name.
        $small = ['the', 'of', 'de', 'del', 'des', 'du', 'di', 'da', 'la', 'le',
                  'les', 'en', 'et', 'and', 'by', 'for', 'y', 'a', 'al'];

        $fixes = [];

        foreach ($rows->pluck('brand')->unique() as $brand) {
            $brand = (string) $brand;

            if (! str_contains($brand, '-')) {
                continue;
            }

            $key = strtolower($brand);

            if (isset($overrides[$key])) {
                if ($overrides[$key] !== $brand) {
                    $fixes[$brand] = $overrides[$key];
                }

                continue;
            }

            // Possessives: "victoria-s-secret" -> "victoria's secret".
            $working = preg_replace('/-s-/', "'s-", $key);
            $working = preg_replace('/-s$/', "'s", $working);

            // Elided article: "l-erbolario" -> "l'erbolario".
            //
            // Requires at least two letters after the L. A single letter means
            // an initialism, not an article — "l-t-piver" is L.T. Piver, and
            // the looser rule turned it into "L'T Piver".
            $working = preg_replace("/^l-(?=[a-z]{2,})/", "l'", $working);

            $words = explode('-', $working);

            $titled = [];
            foreach ($words as $i => $word) {
                if ($word === '') {
                    continue;
                }

                $titled[] = ($i > 0 && in_array($word, $small, true))
                    ? $word
                    : $this->titleWord($word);
            }

            $readable = implode(' ', $titled);

            if ($readable !== '' && $readable !== $brand) {
                $fixes[$brand] = $readable;
            }
        }

        return $fixes;
    }

    /**
     * Upper-case a word's first letter, and the letter after an apostrophe.
     *
     * ucfirst alone leaves "l\'occitane" and "victoria\'s" with a lower-case
     * letter after the apostrophe.
     */
    private function titleWord(string $word): string
    {
        $word = ucfirst($word);

        return preg_replace_callback(
            "/'([a-z])/",
            fn ($m) => "'" . strtoupper($m[1]),
            $word
        );
    }

    /**
     * Brand spellings that differ only by casing or hyphenation, mapped to the
     * variant that reads best. "Zoologist-perfumes" and "Zoologist Perfumes"
     * are the same house.
     *
     * The winner is whichever spelling contains a space — the slug forms are an
     * import artefact and look wrong in the interface.
     *
     * @return array<string, string>  variant => canonical
     */
    private function planBrandFixes(\Illuminate\Support\Collection $rows): array
    {
        $byNormalised = [];

        foreach ($rows as $row) {
            $byNormalised[$this->normalise((string) $row->brand)][(string) $row->brand] = true;
        }

        $fixes = [];

        foreach ($byNormalised as $variants) {
            $spellings = array_keys($variants);

            if (count($spellings) < 2) {
                continue;
            }

            usort($spellings, function ($a, $b) {
                $aSlug = str_contains($a, '-') ? 1 : 0;
                $bSlug = str_contains($b, '-') ? 1 : 0;

                return $aSlug <=> $bSlug;   // non-slug spellings sort first
            });

            $canonical = array_shift($spellings);

            foreach ($spellings as $variant) {
                $fixes[$variant] = $canonical;
            }
        }

        return $fixes;
    }

    private function normalise(string $value): string
    {
        $value = strtolower(trim($value));
        $value = str_replace(['-', '_'], ' ', $value);
        $value = preg_replace('/[^a-z0-9 ]/', '', $value);

        return preg_replace('/\s+/', ' ', trim($value));
    }
}
