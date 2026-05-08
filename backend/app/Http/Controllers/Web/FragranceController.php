<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Fragrance;
use App\Models\UserCollection;
use App\Services\SessionWardrobeService;
use Illuminate\Http\Request;

class FragranceController extends Controller
{
    public function __construct(
        private readonly SessionWardrobeService $sessionWardrobe,
    ) {}

    public function show(Request $request, int $fragrance)
    {
        $item = Fragrance::where('is_active', true)
            ->with(['notes.profile', 'accords', 'topNotes.profile', 'heartNotes.profile', 'baseNotes.profile'])
            ->findOrFail($fragrance);

        $collection = $request->user()
            ? UserCollection::where('user_id', $request->user()->id)
                ->where('fragrance_id', $item->id)
                ->first()
            : null;

        $inWardrobe = $request->user()
            ? $collection !== null
            : $this->sessionWardrobe->has($request, $item->id);

        $isFavorite = $request->user()
            ? ($inWardrobe && $collection->is_favorite)
            : $this->sessionWardrobe->isFavorite($request, $item->id);

        return view('app.fragrance-detail', compact('item', 'inWardrobe', 'isFavorite'));
    }
}
