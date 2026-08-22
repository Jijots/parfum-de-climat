<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Validates a Cloudflare Turnstile token server-side.
 *
 * ── Why server-side ──────────────────────────────────────────────────────────
 * The widget in the browser produces a token; anyone can skip the widget and
 * post whatever they like. The token only means something once Cloudflare has
 * confirmed it, which can only happen from the server holding the secret. A
 * front-end-only challenge stops nobody who is worth stopping.
 *
 * ── Why Turnstile ────────────────────────────────────────────────────────────
 * The site already sits behind Cloudflare, it is free with no request cap, and
 * it usually resolves without making a human solve anything.
 *
 * ── Behaviour when unconfigured ──────────────────────────────────────────────
 * With no secret set the rule PASSES. That is deliberate: the alternative is a
 * local or preview environment where nobody can register at all. It means the
 * protection is only real once TURNSTILE_SECRET_KEY exists in the environment,
 * so that variable is part of deploying, not an optional extra.
 */
class Turnstile implements ValidationRule
{
    /**
     * Run this rule even when the field is missing or empty.
     *
     * Laravel skips a non-implicit rule when the value is empty, which for a
     * bot-protection check is precisely backwards: omitting the token entirely
     * is the easiest possible bypass, and without this the rule never fires on
     * exactly the request it exists to stop. Verified — before this flag, a
     * submission with no token passed while a bogus token was correctly
     * rejected, so the protection looked like it worked.
     */
    public bool $implicit = true;

    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $secret = config('services.turnstile.secret');

        if (! $secret) {
            return;   // not configured — see the note above
        }

        if (! is_string($value) || $value === '') {
            $fail('Please complete the verification challenge.');

            return;
        }

        try {
            $response = Http::asForm()
                ->timeout(10)
                ->post(self::VERIFY_URL, [
                    'secret'   => $secret,
                    'response' => $value,
                    'remoteip' => request()->ip(),
                ]);
        } catch (\Throwable $e) {
            // Cloudflare unreachable. Fail CLOSED — treating an outage as a
            // pass would turn any network blip into an open registration form,
            // which is exactly what the rule exists to prevent.
            Log::warning('[Turnstile] Verification request failed', ['error' => $e->getMessage()]);
            $fail('Could not verify you are human right now. Please try again in a moment.');

            return;
        }

        if (! $response->successful() || ! ($response->json('success') === true)) {
            Log::info('[Turnstile] Token rejected', [
                'codes' => $response->json('error-codes'),
                'ip'    => request()->ip(),
            ]);

            $fail('Verification failed. Please try again.');
        }
    }
}
