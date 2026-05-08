<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest: UpdateFragranceRequest
 *
 * Validates PUT /api/v1/admin/fragrances/{fragrance}.
 * All fields are optional (PATCH semantics) — only provided fields are updated.
 */
class UpdateFragranceRequest extends FormRequest
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
            'name'             => ['sometimes', 'string', 'max:255'],
            'brand'            => ['sometimes', 'string', 'max:255'],
            'release_year'     => ['sometimes', 'nullable', 'integer', 'min:1900', 'max:2100'],
            'concentration'    => ['sometimes', 'nullable', 'string', 'max:100'],
            'description'      => ['sometimes', 'nullable', 'string', 'max:2000'],
            'gender_target'    => ['sometimes', 'in:masculine,feminine,unisex'],
            'olfactive_family' => ['sometimes', 'nullable', 'string', 'max:100'],
            'sillage'          => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:10'],
            'longevity'        => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:10'],
            'image'            => ['sometimes', 'nullable', 'image', 'max:4096'],
            'remote_image_url' => ['sometimes', 'nullable', 'url', 'max:1024'],
            'is_active'        => ['sometimes', 'boolean'],
        ];
    }
}
