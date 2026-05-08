<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserCollectionRequest;
use App\Http\Requests\UpdateUserCollectionRequest;
use App\Http\Resources\UserCollectionResource;
use App\Models\Fragrance;
use App\Models\UserCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller: UserCollectionController
 *
 * Manages the authenticated user's personal fragrance wardrobe.
 * All operations are scoped to the current user — users cannot access
 * or modify each other's collections.
 *
 * Routes:
 *   GET    /api/v1/collection              → index()   (list user's wardrobe)
 *   POST   /api/v1/collection              → store()   (add fragrance)
 *   PUT    /api/v1/collection/{fragrance}  → update()  (edit notes/favorite)
 *   DELETE /api/v1/collection/{fragrance}  → destroy() (remove from wardrobe)
 */
class UserCollectionController extends Controller
{
    /**
     * List all fragrances in the authenticated user's collection.
     *
     * GET /api/v1/collection
     *
     * Eager-loads the fragrance with its notes and accords so the Flutter
     * wardrobe screen can render full cards without additional requests.
     * Favorites are sorted first for display priority.
     *
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $collection = UserCollection::where('user_id', $request->user()->id)
            ->with([
                'fragrance.notes',
                'fragrance.accords',
            ])
            ->orderByDesc('is_favorite')
            ->orderByDesc('created_at')
            ->get();

        return UserCollectionResource::collection($collection);
    }

    /**
     * Add a fragrance to the user's personal collection.
     *
     * POST /api/v1/collection
     * Body: { fragrance_id, personal_notes?, is_favorite?, acquired_date? }
     *
     * Returns 409 Conflict if the fragrance is already in the collection.
     * Returns 404 if the fragrance_id doesn't exist or is inactive.
     *
     * @return JsonResponse  201 Created or 409 Conflict
     */
    public function store(StoreUserCollectionRequest $request): JsonResponse
    {
        // Verify the fragrance exists and is active in the catalog
        $fragrance = Fragrance::where('is_active', true)->find($request->fragrance_id);

        if (! $fragrance) {
            return response()->json([
                'message' => 'Fragrance not found or is not available in the catalog.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Check for duplicate — the DB unique constraint would also catch this,
        // but we return a friendlier 409 response here.
        $exists = UserCollection::where('user_id', $request->user()->id)
                                ->where('fragrance_id', $request->fragrance_id)
                                ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This fragrance is already in your collection.',
            ], Response::HTTP_CONFLICT);
        }

        $entry = UserCollection::create([
            'user_id'        => $request->user()->id,
            'fragrance_id'   => $request->fragrance_id,
            'personal_notes' => $request->personal_notes,
            'is_favorite'    => $request->boolean('is_favorite', false),
            'acquired_date'  => $request->acquired_date,
        ]);

        $entry->load('fragrance.notes', 'fragrance.accords');

        return response()->json([
            'message' => 'Fragrance added to your collection.',
            'data'    => new UserCollectionResource($entry),
        ], Response::HTTP_CREATED);
    }

    /**
     * Update a collection entry's personal notes, favorite status, or acquired date.
     *
     * PUT /api/v1/collection/{fragrance}
     *
     * The {fragrance} route parameter is the Fragrance ID (not the collection entry ID),
     * keeping the URL semantically clear: "update this fragrance in my collection."
     *
     * @return JsonResponse  200 OK or 404 Not Found
     */
    public function update(UpdateUserCollectionRequest $request, int $fragranceId): JsonResponse
    {
        $entry = UserCollection::where('user_id', $request->user()->id)
                               ->where('fragrance_id', $fragranceId)
                               ->first();

        if (! $entry) {
            return response()->json([
                'message' => 'This fragrance is not in your collection.',
            ], Response::HTTP_NOT_FOUND);
        }

        $entry->update($request->only([
            'personal_notes',
            'is_favorite',
            'acquired_date',
        ]));

        $entry->load('fragrance.notes', 'fragrance.accords');

        return response()->json([
            'message' => 'Collection entry updated.',
            'data'    => new UserCollectionResource($entry),
        ]);
    }

    /**
     * Remove a fragrance from the user's collection.
     *
     * DELETE /api/v1/collection/{fragrance}
     *
     * @return JsonResponse  200 OK or 404 Not Found
     */
    public function destroy(Request $request, int $fragranceId): JsonResponse
    {
        $deleted = UserCollection::where('user_id', $request->user()->id)
                                 ->where('fragrance_id', $fragranceId)
                                 ->delete();

        if (! $deleted) {
            return response()->json([
                'message' => 'This fragrance is not in your collection.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Fragrance removed from your collection.',
        ]);
    }
}
