---
target: "whole app (Welcome, Browse, Recommend, Register) at https://parfumdeclimat.app"
total_score: 25
max_score: 40
na_heuristics: 
p0_count: 2
p1_count: 2
target_identity: "url:https://parfumdeclimat.app/"
timestamp: 2026-09-04T00-20-07Z
slug: parfumdeclimat-app
---
Method: dual-agent (A: general-purpose design review · B: general-purpose detector/browser evidence)

## Design Health Score
Total: 25/40 (Acceptable, 62.5%). All ten heuristics scored, none n/a.
Synthesis note: Heuristic 5 (Error Prevention) reads worse than 3/4 once combined with Assessment B's finding that the Turnstile widget never mounts on /register.

## Design Specificity Verdict
Strongly authored for this product — precise mechanism-level copy, consistent terminology, deliberate temperature-emphasis on Recommend; corroborated by a clean (zero-finding) deterministic scan across 29 source files.

## Overall Impression
Coherent design language, product logic works end-to-end where reachable (guest wardrobe -> live-weather recommendation confirmed working), but registration and mobile navigation — two of the most important conversion paths — appear broken in production right now.

## What's Working
1. Guest-to-recommendation loop works end-to-end in production (verified: built a guest wardrobe, queried live weather, got a correctly scored recommendation with pick-ups).
2. Error copy is specific and humane throughout (geolocation failure correctly distinguishes unavailable vs. denied; Register field errors are precise and positioned at the source).
3. Defensive image handling (onError fallback to placeholder) quietly addresses a known data-quality issue without ever showing a broken-image glyph.

## Priority Issues

[P0] Registration appears to have no working path in production
Why it matters: Turnstile widget never mounts (no iframe, no Cloudflare network request) AND Google OAuth button is entirely absent from /register (0 matching anchors) — no visible route to a new account for a real visitor.
Fix: Reproduce from a clean session; check Turnstile site key/domain config and OAuth provider config in production.
Suggested command: /impeccable harden

[P0] Mobile navigation is effectively gone; also a WCAG 1.4.10 Reflow risk
Why it matters: hidden md:flex with no hamburger/fallback means a mobile visitor landing anywhere but the homepage cannot reach other sections; a desktop user at high zoom crosses the same breakpoint and loses nav with no keyboard alternative, touching PRODUCT.md's stated WCAG 2.1 AA requirement.
Fix: Add a real mobile nav at the md breakpoint; cap the wordmark so the header never overflows.
Suggested command: /impeccable harden

[P1] A legacy Blade/Alpine shell is still live in production and the new CSP breaks it
Why it matters: /recommend 404s in production (network-confirmed); the real route /app throws 5 repeated Alpine.js EvalErrors because the session's own nonce-based CSP (no unsafe-eval) blocks Alpine's expression evaluator. app.blade.php's own comment confirms both the new Inertia shell and the old Alpine shell are "both live during the migration."
Fix: Identify which routes still serve the old shell; finish migrating them or scope a CSP exception; fix or redirect /recommend.
Suggested command: /impeccable audit

[P1] Cold-start screen is the actual first impression, not the landing page
Why it matters: ~20-30s of raw Render.com log output and Render's own ad precede any product UI on a cold instance, on a Persuade-mode surface meant to build trust.
Fix: A minimal static branded holding page during the wake-up window.
Suggested command: /impeccable polish

[P2] Primary CTA carries the already-documented accent-color drift
Why it matters: .btn-primary is confirmed live at #C4A882 (legacy wheat-gold) while the rest of the same screen is terracotta — on the single highest-traffic pixel in the product.
Fix: Route .btn-primary through var(--color-accent) per DESIGN.md's own Do rule.
Suggested command: /impeccable harden

## Persona Red Flags
Casey (mobile): header overlap reads as broken; GET STARTED button clipped on Browse at 375px; no way back to Recommend without browser back button.
Jordan (first-timer): no explanation of match-% calculation anywhere; empty search has no clear-search affordance; registration may have no working path at all.
Sam (accessibility): Register's form fields are well-built (correct aria attributes, full keyboard support) but mobile nav collapse is a real WCAG 1.4.10 Reflow risk, not just small-screen polish.

## Minor Observations
- Recommend's input card stays fully rendered above the weather masthead after a reading returns.
- Browse's SearchBar has no clear-search control.
- Public 404 page links to an Admin Panel (observation only, not a security judgment).
- Password confirmation error message doesn't mention complexity even when that's the real issue.
- AnimatedNumber/DisplayHeading motion respects prefers-reduced-motion — worth protecting.

Tooling reliability note: a handful of early Assessment A screenshots were excluded as tooling artifacts (browser pane shared with Assessment B's concurrent session); all reported findings are corroborated by a clean screenshot, source, or direct DOM/computed-style inspection.
