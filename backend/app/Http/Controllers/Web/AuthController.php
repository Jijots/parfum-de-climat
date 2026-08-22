<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SessionWardrobeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function __construct(
        private readonly SessionWardrobeService $sessionWardrobe,
    ) {}

    public function showLogin()
    {
        return Inertia::render('Auth/Login', [
            'urls' => [
                'submit'   => route('login.store'),
                'register' => route('register'),
                'forgot'   => route('password.request'),
                'google'   => route('oauth.redirect', 'google'),
            ],
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // An account created through Google has no password. Auth::attempt
        // would simply fail, and "these credentials do not match our records"
        // is actively misleading for someone whose account exists and works —
        // they just signed up a different way. Point them at the right button.
        //
        // Deliberately only reached when a password was submitted for an
        // account that has none: it reveals nothing that the sign-up form does
        // not already reveal by rejecting a duplicate email.
        $passwordless = User::where('email', $credentials['email'])
            ->whereNull('password')
            ->whereNotNull('oauth_provider')
            ->first();

        if ($passwordless) {
            return back()
                ->withErrors([
                    'email' => 'This account signs in with '
                        . ucfirst($passwordless->oauth_provider)
                        . '. Use the button above.',
                ])
                ->withInput($request->only('email'));
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'These credentials do not match our records.'])
                ->withInput($request->only('email'));
        }

        $request->session()->regenerate();

        $this->sessionWardrobe->mergeIntoUser($request, $request->user());

        if (! $request->user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        return redirect()->intended(route('app'));
    }

    public function showRegister()
    {
        return Inertia::render('Auth/Register', [
            'urls' => [
                'submit' => route('register.store'),
                'login'  => route('login'),
                'google' => route('oauth.redirect', 'google'),
            ],
            // Shipped as a list so the select has real options — a few KB, and
            // it avoids a second round-trip just to fill a dropdown.
            'timezones' => \DateTimeZone::listIdentifiers(),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()->symbols()->uncompromised()],
            'timezone' => ['nullable', 'timezone'],
            'gender'   => ['nullable', 'in:masculine,feminine,unisex'],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'timezone' => $data['timezone'] ?? 'UTC',
            'gender'   => $data['gender'] ?? null,
            // 'role' is intentionally absent: it is no longer mass-assignable,
            // and the column defaults to 'user'. Passing it here would be
            // silently discarded, which reads as if it were doing something.
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        $this->sessionWardrobe->mergeIntoUser($request, $user);
        $user->sendEmailVerificationNotification();

        return redirect()->route('verification.notice')
            ->with('status', 'verification-link-sent');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
