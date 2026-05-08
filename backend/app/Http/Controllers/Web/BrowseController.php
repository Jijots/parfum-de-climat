<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Fragrance;
use App\Models\UserCollection;
use App\Services\SessionWardrobeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BrowseController extends Controller
{
    public function __construct(
        private readonly SessionWardrobeService $sessionWardrobe,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $query = Fragrance::query()->where('is_active', true)->withCount('mappedNotes');

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $op   = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(fn ($q) => $q->where('name', $op, $term)->orWhere('brand', $op, $term));
        }

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

        if ($request->filled('q')) {
            $term = '%' . $request->q . '%';
            $query->where(fn ($sub) => $sub->where('name', 'like', $term)->orWhere('brand', 'like', $term));
        }

        if ($request->filled('gender') && $request->gender !== 'all') {
            $query->where('gender_target', $request->gender);
        }

        $fragrances = $query->orderBy('brand')->orderBy('name')->paginate(24);

        $collectionIds = $user
            ? UserCollection::where('user_id', $user->id)->pluck('fragrance_id')->toArray()
            : $this->sessionWardrobe->fragranceIds($request);

        return response()->json([
            'fragrances'    => $fragrances->map(fn ($f) => [
                'id'           => $f->id,
                'name'         => $f->name,
                'brand'        => $f->brand,
                'gender'       => $f->gender_target,
                'image_url'    => $f->getImageUrl(),
                'rating'       => $f->rating,
                'in_wardrobe'  => in_array($f->id, $collectionIds),
                'has_profile'  => $f->mapped_notes_count > 0,
            ])->values(),
            'next_page_url' => $fragrances->nextPageUrl(),
            'total'         => $fragrances->total(),
            'current_page'  => $fragrances->currentPage(),
            'last_page'     => $fragrances->lastPage(),
        ]);
    }
}
