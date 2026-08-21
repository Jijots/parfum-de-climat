<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Fragrance;
use App\Models\UserCollection;
use App\Services\CatalogCache;
use App\Services\SessionWardrobeService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class FragranceController extends Controller
{
    public function __construct(
        private readonly SessionWardrobeService $sessionWardrobe,
        private readonly CatalogCache $cache,
    ) {}

    public function show(Request $request, int $fragrance)
    {
        // The record itself is the same for everyone, so it is cached. Only
        // 'notes.profile' and 'accords' are eager-loaded here: topNotes,
        // heartNotes and baseNotes are filtered subsets of the same table, and
        // loading them separately meant four round-trips for one fragrance.
        // The view derives the three layers from $item->notes instead.
        $item = $this->cache->detail($fragrance, function () use ($fragrance) {
            return Fragrance::where('is_active', true)
                ->with(['notes.profile', 'accords'])
                ->findOrFail($fragrance);
        });

        $collection = $request->user()
            ? UserCollection::where('user_id', $request->user()->id)
                ->where('fragrance_id', $item->id)
                ->first()
            : null;

        $inWardrobe = $request->user()
            ? $collection !== null
            : $this->sessionWardrobe->has($request, $item->id);

        $isFavorite = $request->user()
            ? ($inWardrobe && $collection->is_favorite)
            : $this->sessionWardrobe->isFavorite($request, $item->id);

        $similar = $this->similarFragrances($request, $item);

        // Notes are grouped here rather than in the view. The three layers are
        // filtered subsets of one relation, so grouping the loaded collection
        // avoids the extra queries the topNotes/heartNotes/baseNotes relations
        // would trigger — which would also bypass the cached $item entirely.
        $byLayer = $item->notes->groupBy('layer');

        return Inertia::render('FragranceDetail', [
            'item' => [
                'id'                  => $item->id,
                'name'                => $item->name,
                'brand'               => $item->brand,
                'gender_target'       => $item->gender_target,
                'release_year'        => $item->release_year,
                'concentration'       => $item->concentration,
                'description'         => $item->description ? Str::limit($item->description, 300) : null,
                'rating'              => $item->rating,
                'sillage'             => $item->sillage,
                'longevity'           => $item->longevity,
                'image_url'           => $item->getImageUrl(),
                'external_source_url' => $item->external_source_url,
                'notes' => [
                    'top'   => $byLayer->get('top',   collect())->pluck('raw_note_name')->values(),
                    'heart' => $byLayer->get('heart', collect())->pluck('raw_note_name')->values(),
                    'base'  => $byLayer->get('base',  collect())->pluck('raw_note_name')->values(),
                ],
                'accords' => $item->accords->sortByDesc('strength')->take(8)
                    ->map(fn ($a) => ['accord' => $a->accord, 'strength' => (float) $a->strength])
                    ->values(),
            ],
            'similar'  => $similar,
            'wardrobe' => [
                'in_wardrobe' => $inWardrobe,
                'is_favorite' => $isFavorite,
            ],
            'urls' => [
                'browse'    => route('browse'),
                'fragrance' => url('/fragrances'),
                'toggle'    => route('wardrobe.toggle', $item->id),
                'favorite'  => route('wardrobe.favorite', $item->id),
            ],
            'csrf' => csrf_token(),
        ]);
    }

    /**
     * The "smells like this" list for a fragrance.
     *
     * Reads pre-computed neighbours from the fragrance_similarities table —
     * see Fragrance::similar(). No scoring happens here; the TF-IDF + cosine
     * work was done offline by `fragrances:build-similarity-index`.
     *
     * Returns an empty collection when the index has not been built yet, so the
     * detail page degrades gracefully instead of erroring.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function similarFragrances(Request $request, Fragrance $item): \Illuminate\Support\Collection
    {
        // Shared payload — identical for every visitor, so it is safe to cache.
        // Deliberately excludes wardrobe state; see CatalogCache for the rule.
        $neighbours = $this->cache->similar($item->id, function () use ($item) {
            return $item->similar()
                ->where('fragrances.is_active', true)
                ->limit(8)
                ->get()
                ->map(fn (Fragrance $f) => [
                    'id'        => $f->id,
                    'name'      => $f->name,
                    'brand'     => $f->brand,
                    'gender'    => $f->gender_target,
                    'image_url' => $f->getImageUrl(),
                    'rating'    => $f->rating,
                    // 0-100 for display. The raw cosine score is 0-1.
                    'match'     => (int) round(((float) $f->pivot->score) * 100),
                ])
                ->values()
                ->all();
        });

        if (empty($neighbours)) {
            return collect();
        }

        // Per-user state is resolved fresh on every request, never cached.
        $ids = array_column($neighbours, 'id');

        $collectionIds = $request->user()
            ? UserCollection::where('user_id', $request->user()->id)
                ->whereIn('fragrance_id', $ids)
                ->pluck('fragrance_id')
                ->all()
            : $this->sessionWardrobe->fragranceIds($request);

        return collect($neighbours)->map(function (array $row) use ($collectionIds) {
            $row['in_wardrobe'] = in_array($row['id'], $collectionIds);

            return $row;
        })->values();
    }
}
