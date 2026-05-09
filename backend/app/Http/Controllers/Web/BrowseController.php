<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Fragrance;
use App\Models\UserCollection;
use App\Services\SessionWardrobeService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BrowseController extends Controller
{
    /**
     * Common brand abbreviations → full brand name fragment.
     *
     * When a user searches one of these short codes, the query is expanded to
     * ALSO match fragrances whose brand contains the mapped string — so "YSL"
     * finds "Yves Saint Laurent" even though the substring "YSL" is not present
     * in the stored brand name.
     *
     * Keys must be lowercase. Values are matched case-insensitively (ILIKE).
     */
    private const BRAND_ALIASES = [
        'ysl'  => 'yves saint laurent',
        'tf'   => 'tom ford',
        'mfk'  => 'maison francis kurkdjian',
        'ck'   => 'calvin klein',
        'jpg'  => 'jean paul gaultier',
        'jpm'  => 'jean paul gaultier',
        'pdm'  => 'parfums de marly',
        'cdg'  => 'comme des garcons',
        'dkny' => 'donna karan',
        'lv'   => 'louis vuitton',
        'creed'=> 'creed',          // handles "CREED" uppercase
        'bdk'  => 'bdk parfums',
        'mkk'  => 'mizensir',
    ];

    public function __construct(
        private readonly SessionWardrobeService $sessionWardrobe,
    ) {}

    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Fragrance::query()->where('is_active', true)->withCount('mappedNotes');

        $this->applySearch($query, $request->input('search', ''));

        if ($request->filled('gender') && $request->gender !== 'all') {
            $query->where('gender_target', $request->gender);
        }

        $fragrances = $query->orderBy('brand')->orderBy('name')->paginate(24)->withQueryString();

        $collectionIds = $user
            ? UserCollection::where('user_id', $user->id)->pluck('fragrance_id')->toArray()
            : $this->sessionWardrobe->fragranceIds($request);

        $initialResults = $fragrances->getCollection()->map(fn ($f) => [
            'id'          => $f->id,
            'name'        => $f->name,
            'brand'       => $f->brand,
            'gender'      => $f->gender_target,
            'image_url'   => $f->getImageUrl(),
            'rating'      => $f->rating,
            'in_wardrobe' => in_array($f->id, $collectionIds),
            'has_profile' => $f->mapped_notes_count > 0,
        ])->values();

        return view('app.browse', compact('fragrances', 'collectionIds', 'initialResults'));
    }

    public function search(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate(['q' => ['nullable', 'string', 'max:100']]);

        $query = Fragrance::query()->where('is_active', true)->withCount('mappedNotes');

        $this->applySearch($query, $request->input('q', ''));

        if ($request->filled('gender') && $request->gender !== 'all') {
            $query->where('gender_target', $request->gender);
        }

        $fragrances    = $query->orderBy('brand')->orderBy('name')->paginate(24);
        $collectionIds = $user
            ? UserCollection::where('user_id', $user->id)->pluck('fragrance_id')->toArray()
            : $this->sessionWardrobe->fragranceIds($request);

        return response()->json([
            'fragrances'    => $fragrances->map(fn ($f) => [
                'id'          => $f->id,
                'name'        => $f->name,
                'brand'       => $f->brand,
                'gender'      => $f->gender_target,
                'image_url'   => $f->getImageUrl(),
                'rating'      => $f->rating,
                'in_wardrobe' => in_array($f->id, $collectionIds),
                'has_profile' => $f->mapped_notes_count > 0,
            ])->values(),
            'next_page_url' => $fragrances->nextPageUrl(),
            'total'         => $fragrances->total(),
            'current_page'  => $fragrances->currentPage(),
            'last_page'     => $fragrances->lastPage(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Apply the search term to a fragrance query.
     *
     * Uses ILIKE on PostgreSQL (case-insensitive) and LIKE on MySQL
     * (MySQL LIKE is already case-insensitive by default).
     *
     * Also expands known brand abbreviations: searching "YSL" will match
     * fragrances whose brand contains "yves saint laurent".
     */
    private function applySearch(Builder $query, string $term): void
    {
        $term = trim($term);
        if ($term === '') {
            return;
        }

        $op        = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $pattern   = '%' . $term . '%';
        $aliasName = self::BRAND_ALIASES[strtolower($term)] ?? null;

        // Hyphen-normalised term: collapse "-" → " " so that "jean paul" finds
        // "Jean-Paul Gaultier" and "jean-paul" also finds "Jean Paul Gaultier".
        $stripped        = str_replace('-', ' ', $term);
        $strippedPattern = '%' . $stripped . '%';

        $query->where(function (Builder $q) use ($op, $pattern, $stripped, $strippedPattern, $aliasName) {
            // Standard substring match on stored values
            $q->where('name', $op, $pattern)
              ->orWhere('brand', $op, $pattern);

            // Hyphen-normalised match: strip hyphens from the column before
            // comparing so either form ("jean paul" / "jean-paul") finds brands
            // stored with or without hyphens.
            $q->orWhereRaw("REPLACE(name,  '-', ' ') {$op} ?", [$strippedPattern])
              ->orWhereRaw("REPLACE(brand, '-', ' ') {$op} ?", [$strippedPattern]);

            // Expand brand abbreviation: "YSL" → "yves saint laurent"
            if ($aliasName) {
                $q->orWhere('brand', $op, '%' . $aliasName . '%');
            }
        });
    }
}
