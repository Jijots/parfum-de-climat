<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest: StoreNoteClimateProfileRequest
 *
 * Validates POST /api/v1/admin/notes (creating a new note climate profile).
 * Also used by UpdateNoteClimateProfileRequest via inheritance.
 */
class StoreNoteClimateProfileRequest extends FormRequest
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
            // Name must be unique in the table; Title Case is enforced in the controller.
            'name'                => ['required', 'string', 'max:100', 'unique:note_climate_profiles,name'],
            'note_family'         => ['nullable', 'string', 'max:100'],
            // Temperatures in °C — tinyint range is -128 to 127
            'min_temp_celsius'    => ['nullable', 'integer', 'min:-20', 'max:50'],
            'max_temp_celsius'    => ['nullable', 'integer', 'min:-20', 'max:50'],
            // Enforce max >= min when both are provided
            'max_temp_celsius'    => ['nullable', 'integer', 'min:-20', 'max:50', 'gte:min_temp_celsius'],
            'humidity_preference' => ['required', 'in:low,medium,high,any'],
            // Must be an array of valid season strings
            'ideal_seasons'       => ['required', 'array', 'min:1'],
            'ideal_seasons.*'     => ['string', 'in:spring,summer,autumn,winter'],
            'climate_weight'      => ['required', 'numeric', 'min:0', 'max:1'],
        ];
    }
}
