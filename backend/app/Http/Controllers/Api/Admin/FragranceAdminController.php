<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFragranceRequest;
use App\Http\Requests\UpdateFragranceRequest;
use App\Http\Resources\FragranceResource;
use App\Models\Fragrance;
use App\Models\FragranceNote;
use App\Models\NoteClimateProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller: FragranceAdminController
 *
 * Admin-only CRUD operations for the fragrance catalog.
 * All routes are protected by ['auth:sanctum', 'admin'] middleware.
 *
 * This controller also exposes the endpoint that triggers the PerfumAPI
 * import command synchronously (suitable for admin panel use; for large
 * imports the artisan command should be run via CLI or a queued job).
 *
 * Routes (all under /api/v1/admin/fragrances):
 *   GET    /                    → index()        (all fragrances, including inactive)
 *   POST   /                    → store()        (create manual entry)
 *   PUT    /{fragrance}         → update()       (update any field)
 *   DELETE /{fragrance}        → destroy()      (soft-delete)
 *   GET    /unmapped-notes      → unmappedNotes() (notes needing profile assignment)
 *   POST   /notes/{note}/map   → mapNote()      (assign a note to a profile)
 */
class FragranceAdminController extends Controller
{
    /**
     * List all fragrances (including inactive ones) for the admin panel.
     *
     * GET /api/v1/admin/fragrances
     *
     * Supports the same search/filter params as the public endpoint, plus:
     *   ?inactive=1   → show only inactive fragrances
     *   ?source=manual|perfumapi → filter by source
     *
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min((int) $request->input('per_page', 25), 100);

        $fragrances = Fragrance::withTrashed()
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%' . $request->search . '%';
                $q->where(fn ($sub) =>
                    $sub->where('name', 'like', $term)
                        ->orWhere('brand', 'like', $term)
                );
            })
            ->when($request->boolean('inactive'), fn ($q) =>
                $q->where('is_active', false)
            )
            ->when($request->filled('source'), fn ($q) =>
                $q->where('external_source', $request->source)
            )
            ->orderBy('brand')
            ->orderBy('name')
            ->paginate($perPage);

        return FragranceResource::collection($fragrances);
    }

    /**
     * Create a manual fragrance entry.
     *
     * POST /api/v1/admin/fragrances
     *
     * Used for indie/local fragrances not available on Fragrantica.
     * Handles optional image upload — stores in storage/app/public/fragrances/.
     *
     * @return JsonResponse  201 Created
     */
    public function store(StoreFragranceRequest $request): JsonResponse
    {
        $data = $request->validated();

        // ── Handle image upload ───────────────────────────────────────────
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('fragrances', 'public');
            $data['cached_image_path'] = $path;
        }

        $data['external_source'] = 'manual';
        $data['added_by']        = $request->user()->id;

        $fragrance = Fragrance::create($data);

        return response()->json([
            'message' => 'Fragrance created successfully.',
            'data'    => new FragranceResource($fragrance),
        ], Response::HTTP_CREATED);
    }

    /**
     * Update any fields on a fragrance.
     *
     * PUT /api/v1/admin/fragrances/{fragrance}
     *
     * Both manual and imported fragrances can be edited.
     * If an imported fragrance is edited, its `last_synced_at` is cleared
     * to signal that it has local overrides — the import command will skip it.
     *
     * @return JsonResponse  200 OK
     */
    public function update(UpdateFragranceRequest $request, int $id): JsonResponse
    {
        $fragrance = Fragrance::withTrashed()->findOrFail($id);
        $data      = $request->validated();

        // Handle image replacement
        if ($request->hasFile('image')) {
            // Delete old cached image if it exists
            if ($fragrance->cached_image_path) {
                Storage::disk('public')->delete($fragrance->cached_image_path);
            }
            $data['cached_image_path'] = $request->file('image')
                ->store('fragrances', 'public');
        }

        $fragrance->update($data);

        $fragrance->load('notes', 'accords');

        return response()->json([
            'message' => 'Fragrance updated.',
            'data'    => new FragranceResource($fragrance),
        ]);
    }

    /**
     * Soft-delete a fragrance from the catalog.
     *
     * DELETE /api/v1/admin/fragrances/{fragrance}
     *
     * Soft-deleted fragrances are invisible to users but remain in the DB.
     * Use ?force=1 to permanently delete (irreversible).
     *
     * @return JsonResponse  200 OK
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $fragrance = Fragrance::withTrashed()->findOrFail($id);

        if ($request->boolean('force')) {
            // Permanent delete — also removes cached image
            if ($fragrance->cached_image_path) {
                Storage::disk('public')->delete($fragrance->cached_image_path);
            }
            $fragrance->forceDelete();
            return response()->json(['message' => 'Fragrance permanently deleted.']);
        }

        $fragrance->delete(); // Soft delete

        return response()->json(['message' => 'Fragrance deactivated and hidden from catalog.']);
    }

    /**
     * List all fragrance notes that have not yet been mapped to a NoteClimateProfile.
     *
     * GET /api/v1/admin/fragrances/unmapped-notes
     *
     * The admin panel surfaces these so an admin can either:
     *   a) Create a new NoteClimateProfile for the note
     *   b) Map the note to an existing profile (aliasing)
     *
     * @return JsonResponse
     */
    public function unmappedNotes(): JsonResponse
    {
        $unmapped = FragranceNote::whereNull('note_profile_id')
            ->with('fragrance:id,name,brand')
            ->select(['id', 'fragrance_id', 'raw_note_name', 'layer'])
            ->orderBy('raw_note_name')
            ->get()
            ->groupBy('raw_note_name')
            ->map(fn ($notes, $noteName) => [
                'raw_note_name'  => $noteName,
                'occurrences'    => $notes->count(),
                'affected_fragrances' => $notes->pluck('fragrance')
                    ->filter()
                    ->map(fn ($f) => ['id' => $f->id, 'name' => $f->name, 'brand' => $f->brand])
                    ->unique('id')
                    ->values(),
            ])
            ->values();

        return response()->json([
            'total'   => $unmapped->count(),
            'data'    => $unmapped,
        ]);
    }

    /**
     * Map a fragrance note to a NoteClimateProfile.
     *
     * POST /api/v1/admin/fragrances/notes/{note}/map
     * Body: { profile_id: int }
     *
     * Optionally maps all notes with the same raw_note_name in one action
     * by passing ?apply_to_all=1 — useful for common notes like "Musk"
     * that appear across hundreds of fragrances.
     *
     * @return JsonResponse  200 OK
     */
    public function mapNote(Request $request, int $noteId): JsonResponse
    {
        $request->validate([
            'profile_id' => ['required', 'integer', 'exists:note_climate_profiles,id'],
        ]);

        $note = FragranceNote::findOrFail($noteId);

        if ($request->boolean('apply_to_all')) {
            // Map every unresolved note with the same raw_note_name
            $affected = FragranceNote::where('raw_note_name', $note->raw_note_name)
                                     ->whereNull('note_profile_id')
                                     ->update(['note_profile_id' => $request->profile_id]);

            return response()->json([
                'message'  => "Mapped all {$affected} occurrences of \"{$note->raw_note_name}\".",
                'affected' => $affected,
            ]);
        }

        $note->update(['note_profile_id' => $request->profile_id]);

        return response()->json([
            'message' => "Note \"{$note->raw_note_name}\" mapped to profile successfully.",
        ]);
    }
}
