<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FragranceResource;
use App\Models\Fragrance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Controller: FragranceController
 *
 * Public read-only endpoints for the fragrance catalog.
 * Accessible by any authenticated user (both 'admin' and 'user' roles).
 *
 * Admin write operations (create, update, delete, import) live in
 * Admin\FragranceAdminController to keep concerns separated.
 *
 * Routes:
 *   GET /api/v1/fragrances          → index()  (paginated catalog)
 *   GET /api/v1/fragrances/{id}     → show()   (single fragrance with full detail)
 */
class FragranceController extends Controller
{
    /**
     * Return a paginated list of active fragrances from the catalog.
     *
     * GET /api/v1/fragrances
     *
     * Supported query parameters:
     *   ?search=chanel         Full-text search across name and brand
     *   ?gender=unisex         Filter by gender_target enum
     *   ?family=Citrus         Filter by olfactive_family
     *   ?per_page=20           Items per page (default: 20, max: 100)
     *
     * Notes and accords are NOT eager-loaded on list endpoints to keep
     * payloads lean. Use the detail endpoint (show) for full note data.
     *
     * @return AnonymousResourceCollection  Paginated FragranceResource collection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min((int) $request->input('per_page', 20), 100);

        $fragrances = Fragrance::query()
            ->where('is_active', true)
            // ── Search ────────────────────────────────────────────────────
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%' . $request->search . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('brand', 'like', $term);
                });
            })
            // ── Filters ───────────────────────────────────────────────────
            ->when($request->filled('gender'), fn ($q) =>
                $q->where('gender_target', $request->gender)
            )
            ->when($request->filled('family'), fn ($q) =>
                $q->where('olfactive_family', $request->family)
            )
            // Supplies `mapped_notes_count` to FragranceResource so it can
            // set `has_climate_profile` — used in the Flutter browse list
            // to show an "unscored" badge on fragrances the engine can't rank.
            ->withCount('mappedNotes')
            ->orderBy('brand')
            ->orderBy('name')
            ->paginate($perPage);

        return FragranceResource::collection($fragrances);
    }

    /**
     * Return the full detail of a single fragrance.
     *
     * GET /api/v1/fragrances/{fragrance}
     *
     * Eager-loads notes (with their climate profiles) and accords so the
     * Flutter detail screen has everything it needs in a single request.
     *
     * Returns 404 if the fragrance is inactive or soft-deleted.
     *
     * @return FragranceResource|JsonResponse
     */
    public function show(int $id): FragranceResource|JsonResponse
    {
        $fragrance = Fragrance::where('is_active', true)
            ->with([
                // Load notes with their resolved climate profiles
                'notes.profile',
                // Load accords for the detail page bar chart
                'accords',
            ])
            ->find($id);

        if (! $fragrance) {
            return response()->json([
                'message' => 'Fragrance not found.',
            ], 404);
        }

        return new FragranceResource($fragrance);
    }
}
