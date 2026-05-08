<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNoteClimateProfileRequest;
use App\Http\Resources\NoteClimateProfileResource;
use App\Models\NoteClimateProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller: NoteClimateProfileController
 *
 * Admin CRUD for the note climate profiles — the "when to wear" rules engine.
 *
 * These profiles are the most important configuration in the entire system.
 * Each profile defines a canonical note name (e.g., "Bergamot") and its
 * ideal climate parameters. The Python engine reads these at scoring time.
 *
 * Routes (all under /api/v1/admin/notes):
 *   GET    /           → index()   (list all profiles with fragrance counts)
 *   POST   /           → store()   (create new profile)
 *   GET    /{profile}  → show()    (single profile with linked fragrances)
 *   PUT    /{profile}  → update()  (edit climate parameters)
 *   DELETE /{profile}  → destroy() (delete — unlinks all fragrance notes first)
 */
class NoteClimateProfileController extends Controller
{
    /**
     * List all note climate profiles.
     *
     * GET /api/v1/admin/notes
     *
     * Includes a fragrance_count (how many fragrance notes reference each profile)
     * so the admin can identify well-mapped vs. sparsely-mapped notes.
     * Supports filtering by note_family for the admin panel's sidebar navigation.
     *
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $profiles = NoteClimateProfile::query()
            ->withCount('fragranceNotes')
            ->when($request->filled('family'), fn ($q) =>
                $q->where('note_family', $request->family)
            )
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            })
            ->orderBy('note_family')
            ->orderBy('name')
            ->paginate(50);

        return NoteClimateProfileResource::collection($profiles);
    }

    /**
     * Create a new note climate profile.
     *
     * POST /api/v1/admin/notes
     *
     * After creation, the admin should use the mapNote endpoint on
     * FragranceAdminController to link existing unmapped notes to this profile.
     *
     * @return JsonResponse  201 Created
     */
    public function store(StoreNoteClimateProfileRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Normalize note name to Title Case — "bergamot" → "Bergamot"
        $data['name'] = Str::title($data['name']);

        $profile = NoteClimateProfile::create($data);

        return response()->json([
            'message' => 'Note climate profile created.',
            'data'    => new NoteClimateProfileResource($profile),
        ], Response::HTTP_CREATED);
    }

    /**
     * Return a single note climate profile with all linked fragrance notes.
     *
     * GET /api/v1/admin/notes/{profile}
     *
     * @return NoteClimateProfileResource|JsonResponse
     */
    public function show(int $id): NoteClimateProfileResource|JsonResponse
    {
        $profile = NoteClimateProfile::withCount('fragranceNotes')
            ->find($id);

        if (! $profile) {
            return response()->json(['message' => 'Profile not found.'], 404);
        }

        return new NoteClimateProfileResource($profile);
    }

    /**
     * Update a note climate profile's parameters.
     *
     * PUT /api/v1/admin/notes/{profile}
     *
     * Changes take effect immediately — the next recommendation request will
     * use the updated values. No cache invalidation needed since the engine
     * reads profiles fresh on every invocation.
     *
     * @return JsonResponse  200 OK
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $profile = NoteClimateProfile::findOrFail($id);

        $data = $request->validate([
            'note_family'         => ['sometimes', 'nullable', 'string', 'max:100'],
            'min_temp_celsius'    => ['sometimes', 'nullable', 'integer', 'min:-20', 'max:50'],
            'max_temp_celsius'    => ['sometimes', 'nullable', 'integer', 'min:-20', 'max:50'],
            'humidity_preference' => ['sometimes', 'in:low,medium,high,any'],
            'ideal_seasons'       => ['sometimes', 'array', 'min:1'],
            'ideal_seasons.*'     => ['string', 'in:spring,summer,autumn,winter'],
            'climate_weight'      => ['sometimes', 'numeric', 'min:0', 'max:1'],
        ]);

        $profile->update($data);

        return response()->json([
            'message' => 'Note climate profile updated.',
            'data'    => new NoteClimateProfileResource($profile),
        ]);
    }

    /**
     * Delete a note climate profile.
     *
     * DELETE /api/v1/admin/notes/{profile}
     *
     * Before deleting, all FragranceNotes linked to this profile have their
     * note_profile_id set to NULL (via nullOnDelete DB constraint — handled
     * automatically). They will re-appear in the unmapped notes panel.
     *
     * This prevents orphaned FKs without cascading score data loss.
     *
     * @return JsonResponse  200 OK
     */
    public function destroy(int $id): JsonResponse
    {
        $profile = NoteClimateProfile::findOrFail($id);
        $count   = $profile->fragranceNotes()->count();

        $profile->delete();

        return response()->json([
            'message' => "Profile deleted. {$count} fragrance note(s) have been unlinked and will appear in the unmapped notes queue.",
        ]);
    }
}
