<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fragrance;
use App\Models\NoteClimateProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Controller: Admin\FragranceController
 *
 * Web CRUD for the fragrance catalogue. Runs alongside (and is separate from)
 * Api\Admin\FragranceAdminController which serves the JSON API. Both target the
 * same Eloquent models but render different response types.
 *
 * Soft-delete strategy:
 *   destroy() — soft-deletes (deactivates) a live fragrance
 *   destroy() with ?force=1 — permanently deletes an already-trashed fragrance
 *   destroy() on a trashed fragrance without force — restores it
 *
 * Routes (via Route::resource, 'show' excluded):
 *   GET    /admin/fragrances           → index()
 *   GET    /admin/fragrances/create    → create()
 *   POST   /admin/fragrances           → store()
 *   GET    /admin/fragrances/{id}/edit → edit()
 *   PUT    /admin/fragrances/{id}      → update()
 *   DELETE /admin/fragrances/{id}      → destroy()
 */
class FragranceController extends Controller
{
    /**
     * List all fragrances with search, source, and status filters.
     *
     * Calls withTrashed() so soft-deleted records are visible and can be
     * restored. withCount() supplies data for UI badges without N+1 load.
     */
    public function index(Request $request): View
    {
        $fragrances = Fragrance::withTrashed()
            // ── Search ────────────────────────────────────────────────────
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%' . $request->search . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('brand', 'like', $term);
                });
            })
            // ── Source filter ─────────────────────────────────────────────
            ->when($request->filled('source'), fn ($q) =>
                $q->where('external_source', $request->source)
            )
            // ── Status filter ─────────────────────────────────────────────
            ->when($request->filled('status'), function ($q) use ($request) {
                match ($request->status) {
                    'active'   => $q->where('is_active', true)->whereNull('deleted_at'),
                    'inactive' => $q->where('is_active', false)->whereNull('deleted_at'),
                    'deleted'  => $q->onlyTrashed(),
                    default    => null,
                };
            })
            // Supplies mapped_notes_count + notes_count for the "unscored" badge in the table
            ->withCount(['mappedNotes', 'notes'])
            ->orderBy('brand')
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('admin.fragrances.index', compact('fragrances'));
    }

    /**
     * Show the create form.
     *
     * Passes note profiles so the add-fragrance form can optionally pre-map
     * notes if the admin adds them manually at creation time.
     */
    public function create(): View
    {
        $profiles = NoteClimateProfile::orderBy('note_family')
            ->orderBy('name')
            ->get(['id', 'name', 'note_family']);

        return view('admin.fragrances.create', compact('profiles'));
    }

    /**
     * Persist a new fragrance to the database.
     *
     * POST /admin/fragrances
     *
     * Uploaded images are stored in the public disk under storage/fragrances/
     * and served via the /storage symlink. The path is saved to cached_image_path.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'brand'            => ['required', 'string', 'max:255'],
            'release_year'     => ['nullable', 'integer', 'min:1800', 'max:' . date('Y')],
            'concentration'    => ['nullable', 'string', 'max:50'],
            'olfactive_family' => ['nullable', 'string', 'max:100'],
            'gender_target'    => ['required', 'in:masculine,feminine,unisex'],
            'description'      => ['nullable', 'string', 'max:2000'],
            'sillage'          => ['nullable', 'numeric', 'min:0', 'max:10'],
            'longevity'        => ['nullable', 'numeric', 'min:0', 'max:10'],
            'rating'           => ['nullable', 'numeric', 'min:0', 'max:5'],
            'votes'            => ['nullable', 'integer', 'min:0'],
            'remote_image_url' => ['nullable', 'url', 'max:1024'],
            'image'            => ['nullable', 'image', 'max:4096'],
            'is_active'        => ['sometimes', 'boolean'],
        ]);

        // Handle uploaded image — store to public disk.
        if ($request->hasFile('image')) {
            $data['cached_image_path'] = $request->file('image')
                ->store('fragrances', 'public');
        }

        $data['external_source'] = 'manual';
        $data['added_by']        = $request->user()->id;
        $data['is_active']       = $request->boolean('is_active', true);

        $fragrance = Fragrance::create($data);

        return redirect()
            ->route('admin.fragrances.edit', $fragrance->id)
            ->with('success', "Fragrance \"{$fragrance->name}\" created. You can now map its notes.");
    }

    /**
     * Show the edit form for a fragrance.
     *
     * Uses withTrashed() to allow editing soft-deleted (trashed) fragrances
     * and to expose the restore / force-delete actions.
     */
    public function edit(int $id): View
    {
        $fragrance = Fragrance::withTrashed()
            ->with([
                'notes.profile',   // note pyramid with mapped profiles
                'accords',         // accord bars
                'addedBy:id,name', // attribution
            ])
            ->findOrFail($id);

        $profiles = NoteClimateProfile::orderBy('note_family')
            ->orderBy('name')
            ->get(['id', 'name', 'note_family']);

        return view('admin.fragrances.edit', compact('fragrance', 'profiles'));
    }

    /**
     * Update an existing fragrance.
     *
     * PUT /admin/fragrances/{id}
     *
     * If a new image is uploaded, the old cached file is deleted from storage
     * before the new one is saved.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $fragrance = Fragrance::withTrashed()->findOrFail($id);

        $data = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'brand'            => ['required', 'string', 'max:255'],
            'release_year'     => ['nullable', 'integer', 'min:1800', 'max:' . date('Y')],
            'concentration'    => ['nullable', 'string', 'max:50'],
            'olfactive_family' => ['nullable', 'string', 'max:100'],
            'gender_target'    => ['required', 'in:masculine,feminine,unisex'],
            'description'      => ['nullable', 'string', 'max:2000'],
            'sillage'          => ['nullable', 'numeric', 'min:0', 'max:10'],
            'longevity'        => ['nullable', 'numeric', 'min:0', 'max:10'],
            'rating'           => ['nullable', 'numeric', 'min:0', 'max:5'],
            'votes'            => ['nullable', 'integer', 'min:0'],
            'remote_image_url' => ['nullable', 'url', 'max:1024'],
            'image'            => ['nullable', 'image', 'max:4096'],
            'is_active'        => ['sometimes', 'boolean'],
        ]);

        // Replace cached image if a new file was uploaded.
        if ($request->hasFile('image')) {
            if ($fragrance->cached_image_path) {
                Storage::disk('public')->delete($fragrance->cached_image_path);
            }
            $data['cached_image_path'] = $request->file('image')
                ->store('fragrances', 'public');
        }

        $data['is_active'] = $request->boolean('is_active');

        $fragrance->update($data);

        return redirect()
            ->route('admin.fragrances.edit', $fragrance->id)
            ->with('success', "Fragrance \"{$fragrance->name}\" updated.");
    }

    /**
     * Soft-delete, restore, or force-delete a fragrance.
     *
     * DELETE /admin/fragrances/{id}
     *
     * Behaviour depends on the fragrance state and query parameters:
     *   Live fragrance              → soft-delete (deactivate, recoverable)
     *   Trashed + ?force=1          → permanent delete (removes image from disk)
     *   Trashed without force=1     → restore fragrance to active state
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        $fragrance = Fragrance::withTrashed()->findOrFail($id);

        // ── Already soft-deleted ──────────────────────────────────────────
        if ($fragrance->trashed()) {
            if ($request->boolean('force')) {
                // Permanent delete — clean up stored image first.
                if ($fragrance->cached_image_path) {
                    Storage::disk('public')->delete($fragrance->cached_image_path);
                }
                $fragrance->forceDelete();

                return redirect()
                    ->route('admin.fragrances.index')
                    ->with('success', "\"{$fragrance->name}\" permanently deleted.");
            }

            // No force flag — restore instead.
            $fragrance->restore();

            return back()->with('success', "\"{$fragrance->name}\" restored to the catalogue.");
        }

        // ── Live fragrance → soft-delete ─────────────────────────────────
        $fragrance->delete();

        return back()->with('success', "\"{$fragrance->name}\" deactivated. Restore it from the Deleted filter.");
    }
}
