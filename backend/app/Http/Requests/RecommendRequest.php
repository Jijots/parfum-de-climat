<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest: RecommendRequest
 *
 * Validates POST /api/v1/recommend
 *
 * The Flutter app sends the user's GPS coordinates; the Laravel backend
 * fetches weather data from OpenWeatherMap using those coordinates.
 * Coordinates are validated to their legal geographic ranges.
 */
class RecommendRequest extends FormRequest
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
            // WGS84 coordinate bounds
            'latitude'  => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'latitude.between'  => 'Latitude must be between -90 and 90 degrees.',
            'longitude.between' => 'Longitude must be between -180 and 180 degrees.',
        ];
    }
}
