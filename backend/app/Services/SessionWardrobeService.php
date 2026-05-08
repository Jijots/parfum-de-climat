<?php

namespace App\Services;

use App\Models\Fragrance;
use App\Models\User;
use App\Models\UserCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;

class SessionWardrobeService
{
    private const SESSION_KEY = 'guest_wardrobe.items';

    public function fragranceIds(Request $request): array
    {
        return $this->items($request)
            ->pluck('fragrance_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function has(Request $request, int $fragranceId): bool
    {
        return $this->items($request)->contains(fn ($item) => (int) $item['fragrance_id'] === $fragranceId);
    }

    public function isFavorite(Request $request, int $fragranceId): bool
    {
        $item = $this->items($request)->firstWhere('fragrance_id', $fragranceId);
        return (bool) ($item['is_favorite'] ?? false);
    }

    public function toggle(Request $request, int $fragranceId): bool
    {
        $items = $this->items($request)->keyBy('fragrance_id');

        if ($items->has($fragranceId)) {
            $items->forget($fragranceId);
            $this->store($request, $items->values()->all());

            return false;
        }

        $items->put($fragranceId, [
            'fragrance_id' => $fragranceId,
            'is_favorite' => false,
            'added_at' => now()->toIso8601String(),
        ]);

        $this->store($request, $items->values()->all());

        return true;
    }

    public function toggleFavorite(Request $request, int $fragranceId): bool
    {
        $items = $this->items($request)->keyBy('fragrance_id');

        if (! $items->has($fragranceId)) {
            abort(404);
        }

        $item = $items->get($fragranceId);
        $item['is_favorite'] = ! ($item['is_favorite'] ?? false);
        $items->put($fragranceId, $item);

        $this->store($request, $items->values()->all());

        return (bool) $item['is_favorite'];
    }

    public function paginate(Request $request, int $perPage = 16): LengthAwarePaginator
    {
        $collection = $this->collection($request);
        $page = Paginator::resolveCurrentPage();

        return new Paginator(
            $collection->forPage($page, $perPage)->values(),
            $collection->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    public function favorites(Request $request): Collection
    {
        return $this->collection($request)
            ->where('is_favorite', true)
            ->values();
    }

    public function recommendationFragrances(Request $request): Collection
    {
        $items = $this->items($request)->keyBy('fragrance_id');
        $ids = $items->keys()->map(fn ($id) => (int) $id)->all();

        if ($ids === []) {
            return collect();
        }

        $fragrances = Fragrance::where('is_active', true)
            ->whereIn('id', $ids)
            ->has('mappedNotes')
            ->with('mappedNotes.profile')
            ->get()
            ->keyBy('id');

        return collect($ids)
            ->map(fn ($id) => $fragrances->get($id))
            ->filter()
            ->values()
            ->map(function (Fragrance $fragrance) use ($items) {
                $fragrance->session_is_favorite = (bool) ($items->get($fragrance->id)['is_favorite'] ?? false);

                return $fragrance;
            });
    }

    public function mergeIntoUser(Request $request, User $user): void
    {
        $items = $this->items($request)->keyBy('fragrance_id');
        $ids = $items->keys()->map(fn ($id) => (int) $id)->all();

        if ($ids === []) {
            return;
        }

        $activeIds = Fragrance::where('is_active', true)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($activeIds as $fragranceId) {
            $sessionItem = $items->get($fragranceId);

            $entry = UserCollection::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'fragrance_id' => $fragranceId,
                ],
                [
                    'is_favorite' => (bool) ($sessionItem['is_favorite'] ?? false),
                ]
            );

            if (($sessionItem['is_favorite'] ?? false) && ! $entry->is_favorite) {
                $entry->update(['is_favorite' => true]);
            }
        }

        $this->clear($request);
    }

    public function clear(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEY);
    }

    private function collection(Request $request): Collection
    {
        $items = $this->items($request)
            ->sortByDesc(fn ($item) => [
                (bool) ($item['is_favorite'] ?? false),
                $item['added_at'] ?? '',
            ])
            ->values();

        $ids = $items->pluck('fragrance_id')->map(fn ($id) => (int) $id)->all();

        if ($ids === []) {
            return collect();
        }

        $fragrances = Fragrance::where('is_active', true)
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        return $items->map(function (array $item) use ($fragrances) {
            $fragrance = $fragrances->get((int) $item['fragrance_id']);

            if (! $fragrance) {
                return null;
            }

            return (object) [
                'fragrance_id' => (int) $item['fragrance_id'],
                'is_favorite' => (bool) ($item['is_favorite'] ?? false),
                'fragrance' => $fragrance,
            ];
        })->filter()->values();
    }

    private function items(Request $request): Collection
    {
        return collect($request->session()->get(self::SESSION_KEY, []))
            ->filter(fn ($item) => isset($item['fragrance_id']))
            ->map(fn ($item) => [
                'fragrance_id' => (int) $item['fragrance_id'],
                'is_favorite' => (bool) ($item['is_favorite'] ?? false),
                'added_at' => $item['added_at'] ?? null,
            ])
            ->values();
    }

    private function store(Request $request, array $items): void
    {
        $request->session()->put(self::SESSION_KEY, array_values($items));
    }
}
