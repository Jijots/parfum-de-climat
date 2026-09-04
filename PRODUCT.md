# Product

<!-- impeccable:product-schema 1 -->

## Platform

adaptive

## Stack

Web: Laravel 11 + Inertia.js v3 + React 19, PostgreSQL (Neon, Singapore region), a Python 3.11 recommendation engine invoked as a subprocess (optional FastAPI microservice mode), deployed on Render's free tier.
Mobile: Flutter (iOS + Android), a separate client of the same Laravel REST API; currently earlier-stage relative to the web app (navigation/state/models and UI screens exist; not yet through the same design and hardening pass as web).
(Existing codebase — not asked; evidenced by `backend/composer.json`, `backend/package.json`, `mobile/pubspec.yaml`, and this project's deployment history.)

## Users

Primary: people who own a personal fragrance collection and want to know which one to wear today, based on real-time local weather (temperature, humidity, hemisphere-corrected season). Secondary: visitors browsing the fragrance catalogue or exploring "smells like this" similarity without owning anything yet — no account is required to build a wardrobe or take a reading.

Also relevant to how this project gets presented: it is the developer's own portfolio piece. Its audiences include a university Information Security 2 (cyber ops) professor, and a fragrance-hobbyist community the developer has posted in while also advertising freelance e-commerce/web-dev services.

## Product Purpose

Reads a user's local weather and ranks the fragrances they already own by fit for today's conditions, note by note. Separately, a "smells like this" similarity index over the whole catalogue surfaces fragrances built from similar materials. Success is a user trusting the day's top recommendation enough to act on it, and being able to build a wardrobe and get a reading with no friction, including as a guest.

## Positioning

The mechanism a generic "what perfume to wear" article or seasonal buying guide can't replicate: live local weather scored against a per-note climate profile, personalized to what the visitor actually owns, not a generic quiz result. Recommendations draw only from the user's own wardrobe, with separate, clearly distinguished "pick-up" suggestions from outside it.

## Operating Context

- Guest-first: a wardrobe can be built and a weather reading taken with no account; it carries over on sign-up.
- Registration is Google OAuth-first. Password-based email verification is not reliably deliverable in production — the transactional email provider's one-verified-domain limit is already committed to an unrelated small business the developer runs, so verification email cannot reliably reach real users. Google-verified email routes around this; a non-Google, non-verifying identity provider would not be safe to auto-link the same way.
- Weather lookups are cached (coordinates rounded to ~1.1km precision, short TTL) to protect a free-tier weather API quota; the rounding also limits location precision as a privacy side benefit.
- Hosted on Render's free tier; a scheduled keep-warm ping reduces cold-start sleep, with a known ~750-hour pooled account cap as a hard ceiling.
- An admin panel manages the fragrance catalogue and note-to-climate-profile mappings.
- Public registration only recently opened; the project is early in accumulating real user activity.

## Capabilities and Constraints

- Free-tier infrastructure throughout (hosting, database, weather API, embedding inference) is a durable constraint on new features, not a temporary one to design around once and forget.
- The recommendation engine's note-vs-climate scoring is rule-based, not machine learning. The catalogue similarity index ("smells like this") is TF-IDF + cosine similarity — also not machine learning, no training or learned parameters. The one genuine ML component is a pretrained sentence-transformer used to semantically map inconsistent note names during data cleanup (real transfer learning, but not self-trained). This distinction is deliberate and must stay accurate in any UI copy or external claim.
- Catalogue: 24,000+ fragrance records, deduplicated and name/brand-cleaned from earlier data-quality issues.
- Security is a deliberate, demonstrable strength of this build: nonce-based CSP, HSTS, tightened CORS, Cloudflare Turnstile bot protection on auth flows, a fixed mass-assignment/privilege-escalation gap on user roles, all checked against a public pre-launch security checklist. Treat this posture as something future work should preserve and extend, not something to work around for convenience.
- Terminology: "wardrobe" = fragrances a user owns/tracks; "reading" = one weather-based recommendation request; "pick-ups" = suggested fragrances outside the user's wardrobe.

## Brand Commitments

Name: Parfum de Climat. Existing UI copy voice is quiet, editorial, and confident, not chipper or salesy — em dashes were deliberately removed from user-facing copy because they read as AI-generated to some readers. No other binding identity commitments beyond what is already implemented; palette, type, and other visual specifics belong in DESIGN.md, not here.

## Evidence on Hand

Real, non-fabricated data throughout: 24,000+ real fragrance records (sourced via PerfumAPI/Fragrantica), live weather via OpenWeatherMap, a working deployed instance. No testimonials, press mentions, case studies, or pricing exist anywhere in the product — none should be invented for any surface.

## Product Principles

1. Recommendations only ever come from what the user actually owns, plus clearly separate "pick-up" suggestions — never a generic quiz-like result.
2. No feature ships that depends on paid infrastructure; free-tier limits are a design constraint from the start, not an edge case patched in later.
3. Any "AI" or "machine learning" claim in user-facing or marketing copy must be literally true: rule-based scoring and TF-IDF similarity are not ML; only the pretrained embedding step is.
4. Security is treated as a first-class, demonstrable feature of this build, not an afterthought — it is part of what the project is meant to prove.
5. Guest-first: nothing requires an account until the user wants their data to persist.

## Accessibility & Inclusion

Must meet WCAG 2.1 AA.
