# Cold-start shield

A Cloudflare Worker that sits in front of the Render origin and replaces
Render's own "service waking up" screen with a branded holding page.

## Why this exists

Render's free tier suspends the container after 15 minutes idle. The next
visitor waits 20-30 seconds, and Render fills that window with a raw log dump,
its own branding, and an ad for Render. On a landing page meant to earn a
stranger's trust, that is the first thing they see.

The keep-warm workflow (`.github/workflows/keep-warm.yml`) already covers
8am-6pm PHT. It cannot cover more: the 750 free instance-hours per month are
pooled across the whole Render account, not per service. This Worker covers
what is left.

## How it works

```
warm origin   ->  response passes straight through, untouched
cold origin   ->  our holding page, which polls /up and reloads when ready
```

The request that found the origin cold is what triggers the boot, so the wait
is spent on our page instead of Render's. Nothing else is altered: same method,
headers, body, status, and streaming response.

Deliberate choices worth knowing before editing `src/index.js`:

- **Only document navigations get the holding page.** Substituting HTML for a
  stylesheet or a JSON endpoint would break the caller more confusingly than a
  failed request does.
- **A 500 passes through; a 502/503/504 does not.** A 500 is a real Laravel bug
  the visitor needs to see. The others mean Render's router could not reach a
  container.
- **The origin fetch is raced against a timer, not aborted.** Aborting would
  cancel the request that is waking the container. It finishes under
  `ctx.waitUntil()`, which is what makes the reload find a warm app.
- **The holding page is 503 + `Retry-After` + `noindex`.** It is not content,
  and a crawler arriving mid-boot must not index it as the site.
- **No webfonts, no external CSS.** It has to paint on the first byte.

## Deploy

Requires Wrangler authenticated against the account that owns the
`parfumdeclimat.app` zone.

```bash
cd worker
npx wrangler deploy
```

The routes in `wrangler.jsonc` attach it to `parfumdeclimat.app/*` and
`www.parfumdeclimat.app/*`.

## Verify after deploying

```bash
# Warm: should be the real page, HTTP 200
curl -sI https://parfumdeclimat.app/ | head -1

# Cold: let the app sleep >15 min, then the first hit should be
# HTTP 503 with the holding page, and a reload a few seconds later
# should be the real site.
```

Logs (`wrangler tail`) emit a structured `cold_start_shielded` event with the
reason each time the holding page is served — useful for seeing how often cold
starts actually happen.

## Rollback

This Worker is on the path of every request to the site, so if anything looks
wrong, remove the route and traffic goes straight to the origin again:

```bash
npx wrangler delete            # removes the Worker and its routes
```

Or disable just the routes in the Cloudflare dashboard under
**Workers Routes** for the zone.
