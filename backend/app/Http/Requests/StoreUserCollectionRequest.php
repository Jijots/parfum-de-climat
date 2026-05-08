<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest: StoreUserCollectionRequest
 *
 * Validates POST /api/v1/collection (adding a fragrance to the user's wardrobe).
 * The `fragrance_id` uniqueness per user is enforced at the DB level (unique
 * constraint on user_collections) and caught by the controller as a 409.
 */
class StoreUserCollectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string|object>>
     */
    public function rules(): array
    {
        return [
            'fragrance_id'   => ['required', 'integer', 'exists:fragrances,id'],
            'personal_notes' => ['nullable', 'string', 'max:1000'],
            'is_favorite'    => ['boolean'],
            'acquired_date'  => ['nullable', 'date_format:Y-m-d'],
        ];
    }
}
