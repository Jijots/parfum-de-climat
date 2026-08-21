<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Central cache layer for catalog reads.
 *
 * ── Why this exists ──────────────────────────────────────────────────────────
 * The database lives in Neon's ap-southeast-1 region, so every query is a
 * network round-trip (measured: ~200 ms for a similarity lookup, ~1,000 ms for
 * a browse page). The work itself is trivial; the latency is the cost. Caching
 * removes the round-trip entirely for repeat reads.
 *
 * ── The rule that governs what may be cached ─────────────────────────────────
 * Only cache data that is IDENTICAL FOR EVERY VISITOR.
 *
 * A fragrance's similar-list is the same for everyone, so it caches safely.
 * Whether a fragrance is in YOUR wardrobe is not — that is resolved per request,
 * after the cached payload is read. Mixing the two would leak one user's
 * wardrobe state to another, so the callers here deliberately fetch the shared
 * payload from cache and layer personal state on top.
 *
 * ── Driver independence ──────────────────────────────────────────────────────
 * Everything goes through Laravel's Cache facade, so the driver is a deployment
 * decision, not a code decision:
 *
 *   local dev   CACHE_STORE=file      (zero setup, no service to run)
 *   production  CACHE_STORE=redis     (Upstash free tier; see .env.example)
 *
 * The code below is identical either way.
 *
 * ── Invalidation without cache tags ──────────────────────────────────────────
 * Cache tags (Cache::tags()->flush()) are only supported by redis/memcached, so
 * relying on them would break the file driver used locally. Instead every key is
 * namespaced with a version number held in the cache itself. Bumping the version
 * makes every old key unreachable in one write; the orphaned entries then expire
 * on their own. This works on every driver.
 */
class CatalogCache
{
    /** Similar-fragrance lists change only when the index is rebuilt. */
    private const TTL_SIMILAR = 60 * 60 * 24 * 7;   // 7 days

    /** Browse results change when the catalog changes — rare, but keep it short. */
    private const TTL_BROWSE = 60 * 10;             // 10 minutes

    private const VERSION_KEY = 'catalog:similar:version';

    /**
     * Cache a fragrance's pre-computed similar list.
     *
     * The payload MUST NOT contain per-user fields (in_wardrobe, is_favorite).
     * Callers resolve those separately after reading from here.
     */
    public function similar(int $fragranceId, Closure $resolver): mixed
    {
        $key = sprintf('catalog:v%d:similar:%d', $this->version(), $fragranceId);

        return Cache::remember($key, self::TTL_SIMILAR, $resolver);
    }

    /**
     * Cache one page of browse results.
     *
     * The signature is hashed so that long search terms cannot produce keys that
     * exceed a driver's key-length limit, and so punctuation in a user's query
     * never has to be escaped.
     */
    public function browse(string $term, string $gender, int $page, Closure $resolver): mixed
    {
        $signature = md5(strtolower(trim($term)) . '|' . $gender . '|' . $page);
        $key       = sprintf('catalog:v%d:browse:%s', $this->version(), $signature);

        return Cache::remember($key, self::TTL_BROWSE, $resolver);
    }

    /**
     * Cache a fragrance detail record (the model plus its eager-loaded notes,
     * accords and profiles).
     *
     * The record is user-independent. Wardrobe and favourite state are resolved
     * per request by the controller and never stored here.
     */
    public function detail(int $fragranceId, Closure $resolver): mixed
    {
        $key = sprintf('catalog:v%d:detail:%d', $this->version(), $fragranceId);

        return Cache::remember($key, self::TTL_SIMILAR, $resolver);
    }

    /**
     * Invalidate every cached catalog read in a single write.
     *
     * Called by `fragrances:build-similarity-index` after the index is replaced,
     * and worth calling after any bulk import.
     */
    public function flush(): int
    {
        $next = $this->version() + 1;
        Cache::forever(self::VERSION_KEY, $next);

        return $next;
    }

    /**
     * Current namespace version. Defaults to 1 on a cold cache.
     *
     * Stored with forever() rather than a TTL: if this key expired on its own,
     * the version would reset to 1 and resurrect stale entries still sitting
     * under the v1 namespace.
     */
    public function version(): int
    {
        return (int) Cache::rememberForever(self::VERSION_KEY, fn () => 1);
    }
}
