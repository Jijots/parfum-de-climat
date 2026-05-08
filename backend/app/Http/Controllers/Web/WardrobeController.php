<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Fragrance;
use App\Models\UserCollection;
use App\Services\SessionWardrobeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WardrobeController extends Controller
{
    public function __construct(
        private readonly SessionWardrobeService $sessionWardrobe,
    ) {}

    public function index(Request $request)
    {
        if (! $request->user()) {
            $favorites = $this->sessionWardrobe->favorites($request);
            $collection = $this->sessionWardrobe->paginate($request, 16);
            $isGuestWardrobe = true;

            return view('app.wardrobe', compact('favorites', 'collection', 'isGuestWardrobe'));
        }

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

        $isGuestWardrobe = false;

        return view('app.wardrobe', compact('favorites', 'collection', 'isGuestWardrobe'));
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
