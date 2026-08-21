<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Fragrance;
use App\Models\UserCollection;
use App\Services\SessionWardrobeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WardrobeController extends Controller
{
    public function __construct(
        private readonly SessionWardrobeService $sessionWardrobe,
    ) {}

    public function index(Request $request)
    {
        if (! $request->user()) {
            $favorites  = $this->sessionWardrobe->favorites($request);
            $collection = $this->sessionWardrobe->paginate($request, 16);
            $isGuest    = true;
        } else {
            $userId = $request->user()->id;

            $favorites = UserCollection::where('user_id', $userId)
                ->where('is_favorite', true)
                ->whereHas('fragrance')
                ->with('fragrance')
                ->orderByDesc('updated_at')
                ->get();

            $collection = UserCollection::where('user_id', $userId)
                ->whereHas('fragrance')
                ->with('fragrance')
                ->orderByDesc('is_favorite')
                ->orderByDesc('created_at')
                ->paginate(16)
                ->withQueryString();

            $isGuest = false;
        }

        // Guests and signed-in users are backed by different stores, but both
        // expose a ->fragrance, so one mapper flattens either into the shape the
        // React page expects. The page never learns which store it came from.
        return Inertia::render('Wardrobe', [
            'favorites'  => $favorites->map(fn ($i) => $this->presentEntry($i))->values(),
            'collection' => collect($collection->items())->map(fn ($i) => $this->presentEntry($i))->values(),
            'pagination' => [
                'current_page' => $collection->currentPage(),
                'last_page'    => $collection->lastPage(),
                'total'        => $collection->total(),
            ],
            'isGuest' => $isGuest,
            'urls'    => [
                'wardrobe'  => route('wardrobe'),
                'browse'    => route('browse'),
                'fragrance' => url('/fragrances'),
            ],
            'csrf' => csrf_token(),
        ]);
    }

    /**
     * Flatten one wardrobe entry — a UserCollection row or a session item —
     * into the flat array the React page renders.
     *
     * @param  object  $entry  anything exposing ->fragrance and ->is_favorite
     * @return array<string, mixed>
     */
    private function presentEntry(object $entry): array
    {
        $fragrance = $entry->fragrance;

        return [
            'id'          => $fragrance->id,
            'name'        => $fragrance->name,
            'brand'       => $fragrance->brand,
            'gender'      => $fragrance->gender_target,
            'image_url'   => $fragrance->getImageUrl(),
            'rating'      => $fragrance->rating,
            'is_favorite' => (bool) $entry->is_favorite,
        ];
    }

    public function toggle(Request $request, int $fragrance): JsonResponse
    {
        $frag = Fragrance::where('is_active', true)->findOrFail($fragrance);

        if (! $request->user()) {
            return response()->json([
                'in_wardrobe' => $this->sessionWardrobe->toggle($request, $frag->id),
                'guest' => true,
            ]);
        }

        $existing = UserCollection::where('user_id', $request->user()->id)
            ->where('fragrance_id', $frag->id)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['in_wardrobe' => false]);
        }

        UserCollection::create([
            'user_id'      => $request->user()->id,
            'fragrance_id' => $frag->id,
            'is_favorite'  => false,
        ]);

        return response()->json(['in_wardrobe' => true]);
    }

    public function toggleFavorite(Request $request, int $fragrance): JsonResponse
    {
        if (! $request->user()) {
            return response()->json([
                'is_favorite' => $this->sessionWardrobe->toggleFavorite($request, $fragrance),
                'guest' => true,
            ]);
        }

        $item = UserCollection::where('user_id', $request->user()->id)
            ->where('fragrance_id', $fragrance)
            ->firstOrFail();

        $item->update(['is_favorite' => ! $item->is_favorite]);

        return response()->json(['is_favorite' => $item->is_favorite]);
    }
}
