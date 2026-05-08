<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FragranceNote;
use App\Models\NoteClimateProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Controller: Admin\NoteProfileController
 *
 * CRUD for NoteClimateProfile records — the named note definitions that
 * the Python recommendation engine uses to score fragrances against weather.
 *
 * Each profile maps a note name (e.g. "Bergamot") to its climate behaviour:
 * optimal temperature range, humidity preference, ideal seasons, and a
 * weighting factor that controls how strongly that note influences the score.
 *
 * Routes (via Route::resource, 'show' excluded):
 *   GET    /admin/note-profiles           → index()
 *   GET    /admin/note-profiles/create    → create()
 *   POST   /admin/note-profiles           → store()
 *   GET    /admin/note-profiles/{id}/edit → edit()
 *   PUT    /admin/note-profiles/{id}      → update()
 *   DELETE /admin/note-profiles/{id}      → destroy()
 */
class NoteProfileController extends Controller
{
    /**
     * List all note climate profiles with search and family filters.
     *
     * Also passes:
     *   $families      — distinct note_family values for the filter dropdown
     *   $unmappedCount — total FragranceNotes with no profile assigned (health metric)
     */
    public function index(Request $request): View
    {
        $profiles = NoteClimateProfile::query()
            ->withCount('fragranceNotes')
            ->when($request->filled('search'), fn ($q) =>
                $q->where('name', 'like', '%' . $request->search . '%')
            )
            ->when($request->filled('family'), fn ($q) =>
                $q->where('note_family', $request->family)
            )
            ->orderBy('note_family')
            ->orderBy('name')
            ->paginate(40)
            ->withQueryString();

        // Distinct family values for the filter dropdown.
        $families = NoteClimateProfile::distinct()
            ->orderBy('note_family')
            ->pluck('note_family')
            ->filter()
            ->values();

        // Number of unmapped fragrance notes — shown as a health alert when > 0.
        $unmappedCount = FragranceNote::whereNull('note_profile_id')->count();

        return view('admin.note-profiles.index', compact('profiles', 'families', 'unmappedCount'));
    }

    /**
     * Show the create form.
     */
    public function create(): View
    {
        return view('admin.note-profiles.form', ['profile' => null]);
    }

    /**
     * Persist a new note climate profile.
     *
     * POST /admin/note-profiles
     *
     * Note names are title-cased on save to enforce consistency
     * (e.g. "bergamot" → "Bergamot").
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateProfile($request);

        $data['name'] = Str::title($data['name']);

        $profile = NoteClimateProfile::create($data);

        return redirect()
            ->route('admin.note-profiles.edit', $profile->id)
            ->with('success', "Profile \"{$profile->name}\" created.");
    }

    /**
     * Show the edit form for an existing profile.
     *
     * Loads fragranceNotes count for the "N notes linked" badge in the form header.
     */
    public function edit(NoteClimateProfile $noteProfile): View
    {
        $noteProfile->loadCount('fragranceNotes');

        return view('admin.note-profiles.form', ['profile' => $noteProfile]);
    }

    /**
     * Update an existing note climate profile.
     *
     * PUT /admin/note-profiles/{noteProfile}
     */
    public function update(Request $request, NoteClimateProfile $noteProfile): RedirectResponse
    {
        $data = $this->validateProfile($request, $noteProfile->id);

        $data['name'] = Str::title($data['name']);

        $noteProfile->update($data);

        return redirect()
            ->route('admin.note-profiles.edit', $noteProfile->id)
            ->with('success', "Profile \"{$noteProfile->name}\" updated.");
    }

    /**
     * Delete a note climate profile.
     *
     * DELETE /admin/note-profiles/{noteProfile}
     *
     * The FragranceNote.note_profile_id FK is set to CASCADE SET NULL on delete
     * (from the migration), so linked notes become unmapped automatically.
     * The flash message reports how many notes were unlinked.
     */
    public function destroy(NoteClimateProfile $noteProfile): RedirectResponse
    {
        $linkedCount = $noteProfile->fragranceNotes()->count();
        $name        = $noteProfile->name;

        $noteProfile->delete();

        $message = "Profile \"{$name}\" deleted.";
        if ($linkedCount > 0) {
            $message .= " {$linkedCount} note(s) are now unmapped and need reassignment.";
        }

        return redirect()
            ->route('admin.note-profiles.index')
            ->with('success', $message);
    }

    /**
     * Shared validation rules for store() and update().
     *
     * The 'unique' rule on 'name' ignores the current record during updates
     * to prevent false "already exists" errors when saving without changing the name.
     *
     * @param  int|null  $ignoreId  — the profile ID to exclude from unique check
     */
    private function validateProfile(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('note_climate_profiles', 'name')->ignore($ignoreId),
            ],
            'note_family'         => ['nullable', 'string', 'max:100'],
            'min_temp_celsius'    => ['nullable', 'integer', 'min:-20', 'max:50'],
            'max_temp_celsius'    => ['nullable', 'integer', 'min:-20', 'max:50', 'gte:min_temp_celsius'],
            'humidity_preference' => ['required', 'in:low,medium,high,any'],
            'ideal_seasons'       => ['required', 'array', 'min:1'],
            'ideal_seasons.*'     => ['string', 'in:spring,summer,autumn,winter'],
            'climate_weight'      => ['required', 'numeric', 'min:0', 'max:1'],
        ]);
    }
}
