<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The privacy policy and terms of service.
 *
 * Both are required before the app can accept public registrations: they
 * describe what happens to a stranger's data, and Google will not publish an
 * OAuth consent screen without a reachable privacy policy URL.
 *
 * The "last updated" date is a constant rather than a file timestamp — a
 * deploy should not silently claim the policy changed when it did not. Bump it
 * by hand when the text actually changes.
 */
class LegalController extends Controller
{
    private const LAST_UPDATED = '22 August 2026';

    public function privacy(): Response
    {
        return Inertia::render('Legal/Privacy', $this->props());
    }

    public function terms(): Response
    {
        return Inertia::render('Legal/Terms', $this->props());
    }

    /**
     * @return array<string, string>
     */
    private function props(): array
    {
        return [
            'updated' => self::LAST_UPDATED,
            // Falls back to the from-address so the pages never render a blank
            // contact, which would make the data-request route unusable.
            'contactEmail' => config('mail.support_address')
                ?: config('mail.from.address', 'noreply@parfumdeclimat.app'),
        ];
    }
}
