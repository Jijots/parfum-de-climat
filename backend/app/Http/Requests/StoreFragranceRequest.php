<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest: StoreFragranceRequest
 *
 * Validates POST /api/v1/admin/fragrances (manual fragrance creation).
 * Import-created records bypass this request; they are validated within
 * the ImportFragrancesCommand itself.
 */
class StoreFragranceRequest extends FormRequest
{
    /**
     * Only admins can create fragrances; enforced by the 'admin' middleware
     * at the route level, so we simply return true here.
     */
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
            'name'             => ['required', 'string', 'max:255'],
            'brand'            => ['required', 'string', 'max:255'],
            'release_year'     => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'concentration'    => ['nullable', 'string', 'max:100'],
            'description'      => ['nullable', 'string', 'max:2000'],
            'gender_target'    => ['required', 'in:masculine,feminine,unisex'],
            'olfactive_family' => ['nullable', 'string', 'max:100'],
            'sillage'          => ['nullable', 'numeric', 'min:0', 'max:10'],
            'longevity'        => ['nullable', 'numeric', 'min:0', 'max:10'],
            // Image can be uploaded as a file OR provided as a remote URL.
            'image'            => ['nullable', 'image', 'max:4096'], // 4MB max
            'remote_image_url' => ['nullable', 'url', 'max:1024'],
            'is_active'        => ['boolean'],
        ];
    }
}
