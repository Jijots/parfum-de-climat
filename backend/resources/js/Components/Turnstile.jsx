import { useEffect, useRef } from 'react';

const SCRIPT_SRC = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';
const SCRIPT_ID = 'cf-turnstile-script';

/**
 * Cloudflare Turnstile widget.
 *
 * Renders nothing when no site key is configured, so local and preview
 * environments still allow registration. The server rule mirrors that — see
 * App\Rules\Turnstile.
 *
 * Rendered explicitly rather than by Cloudflare's auto-scan: Inertia swaps page
 * components without a document load, so the auto-scan would only ever find the
 * widget on a full refresh and silently miss it on client-side navigation.
 */
export default function Turnstile({ siteKey, onToken }) {
    const containerRef = useRef(null);
    const widgetIdRef = useRef(null);

    useEffect(() => {
        if (!siteKey || !containerRef.current) return undefined;

        let cancelled = false;

        const render = () => {
            if (cancelled || !window.turnstile || !containerRef.current) return;
            // Guard against React 18 StrictMode double-invoking effects, which
            // would otherwise stack two widgets in the same container.
            if (widgetIdRef.current !== null) return;

            widgetIdRef.current = window.turnstile.render(containerRef.current, {
                sitekey: siteKey,
                theme: 'auto',
                callback: (token) => onToken(token),
                'expired-callback': () => onToken(''),
                'error-callback': () => onToken(''),
            });
        };

        if (window.turnstile) {
            render();
        } else {
            let script = document.getElementById(SCRIPT_ID);

            if (!script) {
                script = document.createElement('script');
                script.id = SCRIPT_ID;
                script.src = SCRIPT_SRC;
                script.async = true;
                script.defer = true;
                document.head.appendChild(script);
            }

            script.addEventListener('load', render);
        }

        return () => {
            cancelled = true;

            if (widgetIdRef.current !== null && window.turnstile) {
                window.turnstile.remove(widgetIdRef.current);
                widgetIdRef.current = null;
            }
        };
    }, [siteKey, onToken]);

    if (!siteKey) return null;

    return <div ref={containerRef} className="mb-5" />;
}
