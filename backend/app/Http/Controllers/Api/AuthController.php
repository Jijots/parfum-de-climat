<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller: AuthController
 *
 * Handles all authentication flows for the Parfum de Climat API.
 * Uses Laravel Sanctum for stateless token-based authentication,
 * which is the correct approach for a Flutter mobile client.
 *
 * Token lifecycle:
 *  - POST /register → issues a token named 'flutter-app'
 *  - POST /login    → issues a token named 'flutter-app'
 *  - POST /logout   → revokes the current token only
 *
 * The Flutter app stores the token in flutter_secure_storage and attaches
 * it to every request as: Authorization: Bearer {token}
 */
class AuthController extends Controller
{
    /**
     * Register a new user account.
     *
     * POST /api/v1/auth/register
     *
     * On success, returns the user object and a Sanctum API token.
     * The Flutter app should persist this token in secure storage immediately.
     *
     * @return JsonResponse  201 Created with user + token, or 422 Unprocessable
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password, // Auto-hashed via 'hashed' cast
            'timezone' => $request->input('timezone', 'UTC'),
            // All self-registered users get the 'user' role, which the column
            // defaults to. 'role' is not mass-assignable — see User::$fillable
            // — so admin has to be granted deliberately by an existing admin.
        ]);

        $token = $user->createToken('flutter-app')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful.',
            'user'    => [
                'id'       => $user->id,
                'name'     => $user->name,
                'email'    => $user->email,
                'role'     => $user->role,
                'timezone' => $user->timezone,
            ],
            'token' => $token,
        ], Response::HTTP_CREATED);
    }

    /**
     * Authenticate a user and issue an API token.
     *
     * POST /api/v1/auth/login
     *
     * Each login issues a new token. Previous tokens remain valid until
     * explicitly revoked. The Flutter app should store the new token and
     * delete any previously stored one.
     *
     * @return JsonResponse  200 OK with user + token, or 401 Unauthorized
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        /** @var User $user */
        $user  = Auth::user();
        $token = $user->createToken('flutter-app')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'user'    => [
                'id'       => $user->id,
                'name'     => $user->name,
                'email'    => $user->email,
                'role'     => $user->role,
                'timezone' => $user->timezone,
                'avatar_url' => $user->avatar_path
                    ? \Storage::url($user->avatar_path)
                    : null,
            ],
            'token' => $token,
        ]);
    }

    /**
     * Revoke the current API token (logout).
     *
     * POST /api/v1/auth/logout
     *
     * Only revokes the token used in this request. Other active tokens
     * (e.g., on other devices) remain valid. This is intentional — a
     * "logout all devices" feature can be added later via deleteTokens().
     *
     * @return JsonResponse  200 OK
     */
    public function logout(Request $request): JsonResponse
    {
        // Delete only the token that authenticated this request
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Return the currently authenticated user's profile.
     *
     * GET /api/v1/auth/me
     *
     * Used by the Flutter app on startup to validate a stored token
     * and refresh the user profile (role, timezone, avatar).
     *
     * @return JsonResponse  200 OK with user profile
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'role'       => $user->role,
            'timezone'   => $user->timezone,
            'avatar_url' => $user->avatar_path
                ? \Storage::url($user->avatar_path)
                : null,
            'created_at' => $user->created_at,
        ]);
    }
}
