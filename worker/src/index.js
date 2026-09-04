/**
 * Cold-start shield for Parfum de Climat.
 *
 * Render's free tier suspends the container after 15 minutes without traffic.
 * The next visitor pays a full boot — 20 to 30 seconds — and for that whole
 * window Render serves its OWN waking-up screen: a raw log dump with Render
 * branding and an ad for Render. On a landing page whose entire job is to earn
 * a stranger's trust in the first few seconds, that is the worst possible thing
 * to hand them, and it happens before a single pixel of the product renders.
 *
 * This Worker sits in front of the origin and changes only that window:
 *
 *   origin answers in time  ->  pass the response straight through, untouched
 *   origin is still booting ->  serve our own holding page, which polls and
 *                               reloads itself the moment the app is up
 *
 * The request that found the origin cold is what triggers the boot, so the wait
 * is spent on our page instead of Render's. Nothing else about the request is
 * altered: same method, headers, body, status, and streaming response.
 */

/** How long a navigation waits before we show the holding page instead. */
const NAVIGATION_TIMEOUT_MS = 3000;

/**
 * Statuses that mean "the origin is not up yet" rather than "the app returned
 * an error". A 500 from Laravel is a real bug and must reach the visitor; a
 * 502/503/504 at this layer is Render's router failing to reach a container.
 */
const COLD_STATUSES = new Set([502, 503, 504]);

/**
 * Set on every subrequest. A Worker's fetch to its own route is sent to the
 * origin rather than back through the Worker, so this should never be seen on
 * an inbound request — but if that ever changes, this header short-circuits
 * the logic instead of recursing until Cloudflare kills the request.
 */
const LOOP_HEADER = 'x-pdc-shield';

export default {
    /**
     * @param {Request} request
     * @param {{ ORIGIN_HOST?: string }} env
     * @param {ExecutionContext} ctx
     */
    async fetch(request, env, ctx) {
        if (request.headers.has(LOOP_HEADER)) {
            return fetch(request);
        }

        const upstream = new Request(request);
        upstream.headers.set(LOOP_HEADER, '1');

        // Only a document navigation gets the holding page. Substituting HTML
        // for a stylesheet, an image, or a JSON endpoint would break the page
        // that requested it in a far more confusing way than a failed request.
        const isNavigation =
            request.method === 'GET' &&
            (request.headers.get('sec-fetch-mode') === 'navigate' ||
                (request.headers.get('accept') ?? '').includes('text/html'));

        if (!isNavigation) {
            try {
                return await fetch(upstream);
            } catch (error) {
                console.log(JSON.stringify({
                    event: 'origin_subresource_failed',
                    url: request.url,
                    message: error instanceof Error ? error.message : String(error),
                }));
                return new Response('Upstream unavailable', {
                    status: 503,
                    headers: { 'retry-after': '10', 'cache-control': 'no-store' },
                });
            }
        }

        const originRequest = fetch(upstream);

        // Deliberately a race against a timer rather than AbortSignal: aborting
        // would cancel the very request that is waking the container. Letting it
        // run to completion under waitUntil is what makes the reload seconds
        // later find a warm app.
        const timer = new Promise((resolve) =>
            setTimeout(() => resolve('timeout'), NAVIGATION_TIMEOUT_MS)
        );

        try {
            const winner = await Promise.race([originRequest, timer]);

            if (winner === 'timeout') {
                ctx.waitUntil(originRequest.catch(() => {}));
                console.log(JSON.stringify({ event: 'cold_start_shielded', reason: 'timeout', url: request.url }));
                return holdingPage();
            }

            if (COLD_STATUSES.has(winner.status)) {
                console.log(JSON.stringify({ event: 'cold_start_shielded', reason: winner.status, url: request.url }));
                return holdingPage();
            }

            return winner;
        } catch (error) {
            // A connection-level failure to the origin looks the same to a
            // visitor as a cold start, and recovers the same way.
            console.log(JSON.stringify({
                event: 'origin_unreachable',
                url: request.url,
                message: error instanceof Error ? error.message : String(error),
            }));
            return holdingPage();
        }
    },
};

/**
 * The holding page.
 *
 * 503 + Retry-After on purpose: this is not content, and a crawler that arrives
 * mid-boot must not index it as the site. no-store so it never survives past
 * the boot it was written for.
 *
 * Self-contained by design — no webfonts, no external CSS, no framework. It has
 * to paint on the first byte, which rules out anything that needs a second
 * round trip. The type is the design system's own fallback stack, and the
 * palette is its tokens inlined.
 */
function holdingPage() {
    return new Response(HOLDING_HTML, {
        status: 503,
        headers: {
            'content-type': 'text/html; charset=utf-8',
            'cache-control': 'no-store',
            'retry-after': '15',
        },
    });
}

const HOLDING_HTML = `<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Parfum de Climat</title>
<noscript><meta http-equiv="refresh" content="8"></noscript>
<style>
  :root {
    --bg: #FEF9EF;
    --ink: #2A2B2F;
    --muted: #515151;
    --accent: #B05A36;
    --hairline: #D1C9BF;
  }
  @media (prefers-color-scheme: dark) {
    :root {
      --bg: #1C1917;
      --ink: #F5EEE1;
      --muted: #A39A8C;
      --accent: #C97551;
      --hairline: rgba(209, 201, 191, 0.16);
    }
  }
  * { box-sizing: border-box; }
  html, body { height: 100%; }
  body {
    margin: 0;
    background: var(--bg);
    color: var(--ink);
    font-family: 'Satoshi', ui-sans-serif, system-ui, -apple-system, sans-serif;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
  }
  main { max-width: 30rem; }
  .mark {
    font-family: 'Zodiak', Georgia, 'Times New Roman', serif;
    font-size: clamp(1.75rem, 5vw, 2.5rem);
    font-weight: 300;
    letter-spacing: 0.01em;
    margin: 0 0 1.75rem;
  }
  .mark em { font-style: normal; color: var(--accent); }
  .rule { height: 1px; background: var(--hairline); border: 0; margin: 0 0 1.75rem; }
  .label {
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.15em;
    color: var(--accent);
    margin: 0 0 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.6rem;
  }
  .dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: var(--accent);
    animation: pulse 1.6s cubic-bezier(0.22, 1, 0.36, 1) infinite;
  }
  @keyframes pulse { 0%, 100% { opacity: 0.25; } 50% { opacity: 1; } }
  @media (prefers-reduced-motion: reduce) { .dot { animation: none; opacity: 0.7; } }
  p { margin: 0; line-height: 1.6; color: var(--muted); font-size: 0.95rem; }
  .slow { margin-top: 1rem; display: none; }
</style>
</head>
<body>
  <main>
    <p class="label"><span class="dot"></span><span>Waking up</span></p>
    <h1 class="mark">Parfum <em>de</em> Climat</h1>
    <hr class="rule">
    <p>The app sleeps when nobody is using it, and takes a few seconds to stir. This page will move on by itself the moment it is ready.</p>
    <p class="slow" id="slow">Still going. A cold start can take up to half a minute.</p>
  </main>
<script>
(function () {
  var started = Date.now();
  var delay = 1500;

  function check() {
    fetch('/up', { cache: 'no-store' })
      .then(function (r) {
        if (r.ok) { location.reload(); return; }
        again();
      })
      .catch(again);
  }

  function again() {
    if (Date.now() - started > 15000) {
      document.getElementById('slow').style.display = 'block';
    }
    // Ease off gradually so a long boot does not turn into a request flood.
    delay = Math.min(delay * 1.3, 5000);
    setTimeout(check, delay);
  }

  setTimeout(check, delay);
})();
</script>
</body>
</html>`;
