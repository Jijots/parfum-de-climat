<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

/**
 * Shares data with every Inertia response.
 *
 * Anything returned by share() is available as a prop in every page component
 * without the page's controller passing it. Used for the things the shell needs
 * on all pages: who is signed in, and where the nav points.
 *
 * Keep this small. Every key here is serialised into the HTML of every single
 * page load, so putting a large collection in it taxes every request in the app.
 */
class HandleInertiaRequests extends Middleware
{
    /**
     * The root Blade template that hosts the React app.
     *
     * Note this is app.blade.php, NOT layouts/app.blade.php — the latter is the
     * Blade shell still serving the pages that have not been ported yet.
     */
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),

            'auth' => [
                // null rather than a User model: the model would serialise every
                // column into the page, including ones no view needs.
                'user' => $user ? [
                    'name'     => $user->name,
                    'verified' => $user->hasVerifiedEmail(),
                ] : null,
            ],

            // Named routes resolved server-side. This is the trade for not
            // installing Ziggy — the URLs the shell needs are listed once here
            // rather than the whole route table being shipped to the browser.
            'nav' => [
                'landing'  => route('landing'),
                'app'      => route('app'),
                'browse'   => route('browse'),
                'wardrobe' => route('wardrobe'),
                'history'  => route('history'),
                'profile'  => route('profile'),
                'login'    => route('login'),
                'register' => route('register'),
                'logout'   => route('logout'),
                'verify'   => route('verification.notice'),
            ],

            // Which social providers are configured. The UI hides a button
            // rather than rendering one that throws when Socialite cannot
            // build the driver — the visitor would meet that error, not us.
            'oauthProviders' => array_values(array_filter([
                config('services.google.client_id') ? 'google' : null,
            ])),

            // Drives the active-link underline. Sent from the server because the
            // browser only knows the path, and the path does not always map
            // cleanly onto a route name.
            'currentRoute' => $request->route()?->getName(),

            // One-off messages set with ->with('success', ...) on a redirect.
            // Closures are evaluated per request, so an unset key costs nothing.
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
                // Laravel's password broker and the email-verification routes
                // both report through 'status'.
                'status'  => fn () => $request->session()->get('status'),
            ],
        ];
    }
}
