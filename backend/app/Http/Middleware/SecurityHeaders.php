<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Attaches the standard security headers to every response.
 *
 * The app previously sent none of these. Each one closes a specific hole:
 *
 *   Content-Security-Policy    restricts where scripts, styles, fonts and
 *                              images may load from, which is the main
 *                              structural defence against XSS
 *   Strict-Transport-Security  tells browsers to refuse plain HTTP for this
 *                              host, closing the redirect window an attacker
 *                              on the same network could exploit
 *   X-Frame-Options            stops the site being framed inside a phishing
 *                              page and clickjacked
 *   X-Content-Type-Options     stops browsers guessing a response is script
 *                              when it was served as something else
 *   Referrer-Policy            keeps full URLs out of the Referer header sent
 *                              to third parties
 *   Permissions-Policy         withholds device APIs the app never uses
 *
 * ── On the CSP nonce ─────────────────────────────────────────────────────────
 * The root template carries one inline script — the theme guard that has to run
 * before first paint to avoid a flash. Allowing that with 'unsafe-inline' would
 * gut the policy, because it would equally permit any script an attacker
 * managed to inject. Instead a fresh nonce is generated per request, shared
 * with the view, and named in the policy, so exactly that one script runs.
 */
class SecurityHeaders
{
    /**
     * Origins the app genuinely depends on. Anything absent here is blocked,
     * which is the point — the list is deliberately short.
     */
    private const FONT_CSS = 'https://api.fontshare.com';
    private const FONT_FILES = 'https://cdn.fontshare.com';
    private const IMAGE_CDN = 'https://fimgs.net';   // Fragrantica bottle images
    // Bot protection. Needs to load a script AND embed its own iframe, so it
    // has to appear in script-src and frame-src both.
    private const TURNSTILE = 'https://challenges.cloudflare.com';

    public function handle(Request $request, Closure $next): Response
    {
        // Vite owns the nonce (see AppServiceProvider::boot) so the policy and
        // the tags Vite injects always agree. The fallback keeps the policy
        // strict in the unlikely case Vite has not been touched this request.
        $nonce = Vite::cspNonce() ?: bin2hex(random_bytes(12));
        $request->attributes->set('csp_nonce', $nonce);

        $response = $next($request);

        // Only decorate real page responses. Streamed and binary downloads
        // gain nothing from a CSP and can be harmed by extra headers.
        if (! $this->shouldDecorate($response)) {
            return $response;
        }

        $csp = [
            "default-src 'self'",
            "base-uri 'self'",
            // No plugins, and nothing may frame this site.
            "object-src 'none'",
            "frame-ancestors 'none'",
            // Forms may only post back to the app itself.
            "form-action 'self'",
            "script-src 'self' 'nonce-{$nonce}' " . self::TURNSTILE,
            // 'unsafe-inline' is unavoidable for styles: React sets inline
            // style attributes and Tailwind emits them too. Inline STYLE is a
            // far smaller risk than inline script — it cannot execute.
            "style-src 'self' 'unsafe-inline' " . self::FONT_CSS,
            'font-src ' . implode(' ', ["'self'", self::FONT_FILES, 'data:']),
            // data: for inline SVG placeholders; fimgs.net serves every bottle.
            'img-src ' . implode(' ', ["'self'", 'data:', self::IMAGE_CDN]),
            'connect-src ' . implode(' ', ["'self'", self::TURNSTILE]),
            // frame-src, not frame-ancestors: the widget embeds an iframe
            // FROM Cloudflare. frame-ancestors still denies anyone framing us.
            'frame-src ' . self::TURNSTILE,
            // Upgrade any stray http:// subresource rather than blocking it.
            'upgrade-insecure-requests',
        ];

        $response->headers->set('Content-Security-Policy', implode('; ', $csp));
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set(
            'Permissions-Policy',
            // Geolocation stays enabled for this origin — the recommendation
            // page asks for it. Everything else the app never touches.
            'geolocation=(self), camera=(), microphone=(), payment=(), usb=(), magnetometer=()'
        );

        // HSTS only over a secure connection. Sending it over plain HTTP is
        // ignored by browsers anyway, and setting it in local development
        // would pin localhost to HTTPS in the developer's browser — a
        // genuinely annoying thing to undo.
        if ($request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        // Advertising the exact PHP build only helps someone matching it to a
        // known CVE.
        $response->headers->remove('X-Powered-By');

        return $response;
    }

    private function shouldDecorate(Response $response): bool
    {
        if ($response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse
            || $response instanceof \Symfony\Component\HttpFoundation\StreamedResponse) {
            return false;
        }

        $type = (string) $response->headers->get('Content-Type');

        return $type === ''
            || str_contains($type, 'text/html')
            || str_contains($type, 'application/json');
    }
}
