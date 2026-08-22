<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use App\Rules\Turnstile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class PasswordResetController extends Controller
{
    public function showForgotForm()
    {
        return Inertia::render('Auth/ForgotPassword', [
            'urls' => [
                'submit' => route('password.email'),
                'login'  => route('login'),
            ],
        ]);
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            // A reset request sends mail to an address the requester may not
            // own, so it is worth a challenge as well as a rate limit.
            'cf-turnstile-response' => [new Turnstile()],
        ]);

        // We always return the same success message to prevent user enumeration.
        Password::sendResetLink($request->only('email'));

        return back()->with('status', __('passwords.sent'));
    }

    public function showResetForm(Request $request, string $token)
    {
        return Inertia::render('Auth/ResetPassword', [
            'token' => $token,
            'email' => $request->email,
            'urls'  => [
                'submit' => route('password.update'),
                'login'  => route('login'),
            ],
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __($status));
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
