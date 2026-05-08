<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * FormRequest: RegisterRequest
 *
 * Validates the POST /api/v1/auth/register payload.
 * All users self-register as 'user' role; admin role is assigned manually.
 */
class RegisterRequest extends FormRequest
{
    /**
     * All registration endpoints are publicly accessible (no auth required).
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
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()->symbols()->uncompromised()],
            // IANA timezone string — validated against PHP's known timezone IDs.
            // Flutter should pass the device timezone on register.
            'timezone' => ['sometimes', 'string', 'timezone:all'],
        ];
    }
}
