<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest: UpdateUserCollectionRequest
 *
 * Validates PUT /api/v1/collection/{fragrance} (update personal notes / favorite flag).
 */
class UpdateUserCollectionRequest extends FormRequest
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
            'personal_notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'is_favorite'    => ['sometimes', 'boolean'],
            'acquired_date'  => ['sometimes', 'nullable', 'date_format:Y-m-d'],
        ];
    }
}
