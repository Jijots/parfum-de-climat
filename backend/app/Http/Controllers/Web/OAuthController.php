<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SessionWardrobeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

/**
 * Sign-in through an external identity provider.
 *
 * ── Why this is the primary registration path ────────────────────────────────
 * The transactional email provider allows one verified sending domain per
 * account, and that slot is used elsewhere. Verification links therefore never
 * reach a stranger who registers with a password. Google has already proven the
 * address belongs to the person, so accounts created this way are marked
 * verified on the spot and need no email from us at all.
 *
 * Password registration still works and is untouched; it is simply the path
 * that depends on email delivery being configured.
 */
class OAuthController extends Controller
{
    /**
     * Providers this app accepts. Anything else 404s rather than being handed
     * to Socialite, which would otherwise throw a driver exception on a
     * user-supplied string.
     */
    private const SUPPORTED = ['google'];

    public function __construct(
        private readonly SessionWardrobeService $sessionWardrobe,
    ) {}

    public function redirect(string $provider)
    {
        abort_unless(in_array($provider, self::SUPPORTED, true), 404);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(Request $request, string $provider)
    {
        abort_unless(in_array($provider, self::SUPPORTED, true), 404);

        try {
            $social = Socialite::driver($provider)->user();
        } catch (InvalidStateException) {
            // Stale or replayed callback — the session state no longer matches.
            // Common and benign: a bookmarked callback URL, or the flow left
            // open long enough for the session to roll.
            return redirect()->route('login')
                ->withErrors(['email' => 'That sign-in link expired. Please try again.']);
        } catch (\Throwable $e) {
            Log::warning('[OAuth] Provider handshake failed', [
                'provider' => $provider,
                'error'    => $e->getMessage(),
            ]);

            return redirect()->route('login')
                ->withErrors(['email' => 'Could not sign you in with ' . ucfirst($provider) . '. Please try again.']);
        }

        $email = $social->getEmail();

        // Google can withhold the address if the account has none verified.
        // Without it there is nothing to key an account on.
        if (! $email) {
            return redirect()->route('login')
                ->withErrors(['email' => ucfirst($provider) . ' did not share an email address, so an account cannot be created.']);
        }

        $user = $this->resolveUser($provider, $social, $email);

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        // Carry over anything added to the wardrobe before signing in.
        $this->sessionWardrobe->mergeIntoUser($request, $user);

        return redirect()->intended(route('app'));
    }

    /**
     * Find or create the local account behind a provider identity.
     *
     * Three cases, and the middle one is the one worth being careful about:
     *
     *  1. Already linked        — matched on (provider, provider id)
     *  2. Same email, no link   — an existing PASSWORD account owns this address
     *  3. Brand new             — create it, pre-verified
     *
     * Case 2 links the provider to the existing account rather than creating a
     * duplicate. That is safe here specifically because Google verifies the
     * address before releasing it, so possession of the Google account proves
     * possession of the email that the password account was registered with.
     * With a provider that did NOT verify addresses this would be an account
     * takeover, and the correct behaviour would be to refuse and ask the person
     * to sign in with their password first.
     */
    private function resolveUser(string $provider, $social, string $email): User
    {
        $providerId = (string) $social->getId();

        $linked = User::where('oauth_provider', $provider)
            ->where('oauth_id', $providerId)
            ->first();

        if ($linked) {
            // Refresh the avatar; people change them.
            $linked->avatar_url = $social->getAvatar();
            $linked->save();

            return $linked;
        }

        $existing = User::where('email', $email)->first();

        if ($existing) {
            $existing->oauth_provider = $provider;
            $existing->oauth_id       = $providerId;
            $existing->avatar_url     = $social->getAvatar();

            // The provider vouched for the address, so an account that had
            // never completed email verification is now verified.
            if (! $existing->hasVerifiedEmail()) {
                $existing->email_verified_at = now();
            }

            $existing->save();

            return $existing;
        }

        $user = new User();
        $user->name              = $social->getName() ?: strtok($email, '@');
        $user->email             = $email;
        $user->password          = null;   // no password: this account signs in through the provider
        $user->oauth_provider    = $provider;
        $user->oauth_id          = $providerId;
        $user->avatar_url        = $social->getAvatar();
        $user->email_verified_at = now();
        $user->timezone          = 'UTC';  // corrected on the profile page
        // 'role' is set by the column default, and is not mass-assignable.
        $user->save();

        return $user;
    }
}
