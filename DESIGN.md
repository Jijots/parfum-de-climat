---
name: Parfum de Climat
description: A weather-aware fragrance wardrobe and recommendation app with an apothecary-ledger aesthetic.
colors:
  parchment: "#FEF9EF"
  aged-paper: "#F5EEE1"
  ink: "#2A2B2F"
  warm-grey: "#515151"
  taupe-hairline: "#D1C9BF"
  terracotta: "#B05A36"
  terracotta-dim: "rgba(176, 90, 54, 0.10)"
  terracotta-edge: "rgba(176, 90, 54, 0.30)"
  error-red: "#B94040"
  legacy-wheat-gold-DEPRECATED: "#C4A882"
typography:
  display:
    fontFamily: "Zodiak, Cormorant Garamond, Georgia, Times New Roman, serif"
    fontSize: "clamp(3rem, 6vw, 5rem)"
    fontWeight: 300
    lineHeight: 1.05
    letterSpacing: "-0.02em"
  body:
    fontFamily: "Satoshi, Inter, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1rem"
    fontWeight: 400
    lineHeight: 1.5
    letterSpacing: "normal"
  label:
    fontFamily: "ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace"
    fontSize: "0.6875rem"
    fontWeight: 500
    lineHeight: 1.2
    letterSpacing: "0.1em"
rounded:
  pill: "9999px"
  xl: "20px"
  2xl: "24px"
  3xl: "32px"
spacing:
  section: "6.5rem"
components:
  button-primary:
    backgroundColor: "{colors.legacy-wheat-gold-DEPRECATED}"
    textColor: "#1A1A1A"
    rounded: "{rounded.pill}"
    padding: "12px 28px"
  button-ghost:
    backgroundColor: "transparent"
    textColor: "{colors.ink}"
    rounded: "{rounded.pill}"
    padding: "12px 28px"
  badge-active:
    backgroundColor: "rgba(196, 168, 130, 0.12)"
    textColor: "{colors.legacy-wheat-gold-DEPRECATED}"
    rounded: "9999px"
    padding: "2px 10px"
  input-field:
    backgroundColor: "{colors.parchment}"
    textColor: "{colors.ink}"
    rounded: "{rounded.pill}"
    padding: "10px 14px"
---

# Design System: Parfum de Climat

## Overview

**Creative North Star: "The Apothecary Ledger"**

Parfum de Climat reads like a bound record kept by someone who takes scent seriously: parchment pages, a terracotta wax seal standing in for the one accent that matters, and a warm, ink-on-paper voice throughout. Nothing about it should feel like a SaaS dashboard wearing perfume branding — it should feel like the tool a perfumer's assistant would actually keep, blending an apothecary's material warmth with the precision of a scoring instrument.

The system runs two elevation languages deliberately, never mixed on one element: neumorphism (soft dual-shadow depth, parchment-toned rather than black) for things you press or fill in — buttons, inputs, stat values — and liquid glass (blurred, hairline-bordered translucency) for things you sit inside — panels, modals, the sidebar. Type carries the same split: an editorial serif (Zodiak) for display moments with true italics, a warm humanist sans (Satoshi) for reading and interface copy, and a tracked, uppercase monospace for labels, eyebrows, and numeric stats — never for anything meant to be read at length.

Confirmed rejections: Inter as the body face (the single strongest tell of a generic AI-era product, deliberately avoided), sharp/boxy corners anywhere in the interactive layer, and cool-grey dark mode (dark stays warm, with every neutral keeping a brown cast rather than desaturating toward black).

**Key Characteristics:**
- Parchment-and-terracotta palette; warm in both light and dark mode
- Neumorphism for things you act on, liquid glass for things you sit inside — never combined
- Pill-shaped interactive elements, 24px-radius containers, no sharp corners
- Zodiak (display serif) / Satoshi (body sans) / tracked uppercase monospace (labels) — a three-role type system
- Tactile, confident interaction: buttons and inputs read as physically pressable, not flat

## Colors

Warm and paper-toned throughout; the only saturated color in the system is the terracotta accent, used sparingly enough that its rarity carries meaning.

### Primary
- **Terracotta** (`#B05A36`, dark mode `#C97551`): the one true accent token (`--color-accent`). Links on hover, active wardrobe state, focus rings' color intent, the similarity-match badge, "smells like this" scoring. Adaptive — lightens in dark mode for contrast against a near-black base rather than staying fixed.

### Neutral
- **Parchment** (`#FEF9EF`, dark mode `#1C1917`): page canvas background.
- **Aged Paper** (`#F5EEE1`, dark mode `#262120`): cards, panels — one tone deeper than the canvas, so elevation reads through warmth rather than shadow.
- **Ink** (`#2A2B2F`, dark mode `#F5EEE1`): primary text. Near-black with a warm cast, never a cold true-black.
- **Warm Grey** (`#515151`, dark mode `#A39A8C`): secondary/muted text.
- **Taupe Hairline** (`#D1C9BF`, dark mode `rgba(209,201,191,0.16)`): every border and divider in the system. No pure-white or pure-black borders anywhere.

### Feedback
- **Error Red** (`#B94040`, dark mode `#E05858`): validation errors and destructive state only.

### Named Rules
**The One Accent Rule.** Terracotta (`{colors.terracotta}` / `var(--color-accent)`) is the only accent token new work should reach for. A second accent color — a legacy wheat gold, `#C4A882` — is still hardcoded directly (not through a token) in six places: `.btn-primary`, `.badge-active`, `.stat-card` icons, `.sidebar-link.active`, the input focus glow, and the global `:focus-visible` outline. This is a known, unintended drift from before the palette moved to terracotta, not a deliberate two-accent system — confirmed with the project owner during this scan. New components should use terracotta; reconciling the six legacy spots is tracked as cleanup, not a pattern to extend.

## Typography

**Display Font:** Zodiak, with Cormorant Garamond → Georgia → Times New Roman → serif as fallbacks
**Body Font:** Satoshi, with Inter → ui-sans-serif → system-ui → sans-serif as fallbacks
**Label/Mono Font:** system monospace stack (`ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace`) — Tailwind's default, used deliberately rather than themed

**Character:** Zodiak is an editorial serif chosen specifically for having true italics, which the system's mixed roman/italic headline treatment depends on (a faux-obliqued fallback would read as a compromise). Satoshi is warmer and more characterful than Inter while staying neutral enough for dense UI — the explicit reason it replaced Inter. The monospace label voice is the system's third register: uppercase, tracked wide, used only for eyebrows, field labels, and numeric/stat values, never for anything a reader is meant to read at length.

### Hierarchy
- **Display XL** (weight 300, `clamp(3rem, 6vw, 5rem)`, line-height 1.05, tracking -0.02em): hero headlines only.
- **Display LG** (weight 300, `clamp(2rem, 4vw, 3.25rem)`, line-height 1.1, tracking -0.015em): section-level display headings.
- **Display SM** (weight 300, 1.5rem, line-height 1.2): smaller display moments — card titles in a display context.
- **Body** (weight 400, base 17px root, line-height 1.5): all reading copy.
- **Label** (weight 500, 0.6875rem–0.8125rem, tracking 0.06–0.15em, uppercase): eyebrows, field labels, badges, nav, stat labels.

### Named Rules
**The No-Inter Rule.** Inter never renders as the body face. It is a fallback three deep, present only so the page still reads if Satoshi fails to load.

## Layout

Base root font size is 17px (not the browser default 16px), so the whole type scale sits slightly larger than typical. Content columns are narrow and centered (`max-w-2xl` for forms/settings, `max-w-4xl`–`max-w-5xl` for content pages), with generous vertical rhythm — `py-16` page padding and a named `--spacing-section` token (6.5rem) for major section breaks. Responsive behavior follows Tailwind's stock breakpoints (`sm`/`md`/`lg`), used heavily; there is no custom breakpoint scale.

Two recurring structural patterns: hairline-separated label/value rows (settings, detail pages — a label column fixed-width on wide screens, stacking above its control on narrow ones) instead of bordered form panels, and card grids (`grid-cols-1` → `sm:grid-cols-3`) for browse/recommendation surfaces. Numbered/lettered section markers (`01`, `02`...) and hairline `<hr>`-style rules with an uppercase label are used instead of heavier section chrome.

## Elevation & Depth

Hybrid, and the split is load-bearing: neumorphism and liquid glass are never applied to the same element. Neumorphism carries things you act on; liquid glass carries things you sit inside.

### Shadow Vocabulary
- **Neumorphic raised** (`6px 6px 12px var(--shadow-dark), -6px -6px 12px var(--shadow-light)`): buttons, inputs (as the inset variant), stat cards. `--shadow-dark`/`--shadow-light` are parchment-toned (`#E6DDCD`/`#FFFDF7` in light mode), not black/white, so the depth reads warm rather than harsh.
- **Neumorphic inset** (`inset 4px 4px 8px var(--shadow-dark), inset -4px -4px 8px var(--shadow-light)`): input fields, the active sidebar item.
- **Glass flat** (`0 8px 32px rgba(0,0,0,0.08)`): panel separation only — sidebar, modals, feature cards, always paired with a hairline border and a 20px backdrop blur, never with a neumorphic shadow.
- **Focus** (`0 0 0 3px var(--accent-dim)`): the accent-colored focus indication layered on top of whichever base shadow the element already carries.

Note: the `.neu-raised` class name is legacy from before a later pass — despite the name, its current implementation carries `box-shadow: none` and conveys elevation through tone (one step darker than canvas) plus a hairline border instead of an actual shadow. True dual-shadow neumorphism is still live on buttons, inputs, and stat cards; cards using `.neu-raised` (fragrance tiles, most panels) are tonal, not shadowed, despite the name.

### Named Rules
**The Never-Both Rule.** Neumorphism and Liquid Glass never coexist on one element. This is stated explicitly in the source and is the system's central material law.

## Shapes

Pill radius (`9999px`) on every interactive control — buttons, inputs, badges, chips — is the system's loudest and most deliberate signal; it is what reads as "revamped" rather than merely recolored. Containers step down from that: 24px (`--radius-lg` / `--radius-2xl`) is the card standard, 20px for smaller contained elements, 32px for the largest feature surfaces. No sharp (0px) corners appear anywhere in the interactive layer.

## Components

### Buttons
- **Shape:** pill (`border-radius: 9999px`).
- **Primary:** currently the legacy wheat-gold background (`#C4A882`) with near-black text (`#1A1A1A`), uppercase tracked label type, full neumorphic dual-shadow. Per the Named Rule above, new primary-action surfaces should target terracotta instead; this entry documents current shipped state, not the target.
- **Ghost/Secondary:** transparent background, ink-colored text and border at 20% opacity, no shadow — border darkens to `--muted` on hover.
- **Icon:** compact, transparent, hairline border, muted icon color that shifts to ink on hover.
- **States:** hover softens the shadow (primary) or darkens the border (ghost); active scales to 98% with an inset-shadow press effect on primary; disabled drops to 45% opacity and removes shadow.

### Badges
- **Style:** pill, hairline border, translucent tinted background matching its semantic state.
- **Variants:** active (wheat-gold tint — same drift as buttons), error (red tint), inactive (neutral grey tint).

### Cards / Containers
- **Corner style:** 24px radius (`--radius-lg`).
- **Background:** Aged Paper (`--panel`).
- **Elevation strategy:** tonal + hairline border, not shadowed (see Elevation & Depth note on `.neu-raised`). Border shifts to the terracotta accent-edge color on hover.

### Inputs / Fields
- **Style:** pill radius, parchment background, neumorphic inset shadow.
- **Focus:** border and glow currently key off the legacy wheat gold, not terracotta — same drift noted above.
- **Error:** red border with a matching red-tinted focus glow; label text drops to `.field-error` (small, red).

### Stat Card
- **Style:** neumorphic raised (real dual-shadow, unlike `.neu-raised`), parchment background.
- **Value:** display-font, large, light weight, tight tracking.
- **Icon color:** legacy wheat gold — same drift.

### Data Table
- **Style:** flat, hairline row dividers only, no zebra striping. Uppercase tracked monospace-adjacent header labels. Row hover is a near-imperceptible accent tint (3.5% opacity).

### Sidebar Navigation
- **Style:** flat list items, muted icon/text at rest, ink on hover with a subtle surface-tint background. Active state uses the legacy wheat gold plus a neumorphic inset shadow — the same drift as buttons/badges.

### Divider / Section Label
- **Divider:** a 1px hairline rule, no gradient or decoration.
- **Section label:** uppercase, tracked wide, muted color, monospace-adjacent — used as an eyebrow above nearly every section.

## Do's and Don'ts

### Do:
- **Do** use terracotta (`{colors.terracotta}` / `var(--color-accent)`) for any new accent, link, or active-state need.
- **Do** use pill radius (`9999px`) on every new interactive control; use 20–32px radius on containers, scaling with the surface's size.
- **Do** reserve the uppercase tracked monospace type role for labels, eyebrows, and numeric stats — never for body copy or anything meant to be read at length.
- **Do** keep dark-mode neutrals warm (a brown cast throughout); never desaturate toward cool grey or true black.
- **Do** treat neumorphism and liquid glass as mutually exclusive per element — pick one based on whether the element is acted on (neumorphic) or sat inside (glass).

### Don't:
- **Don't** combine neumorphic shadows and glass blur/translucency on the same element.
- **Don't** introduce a new hardcoded accent hex. Route through the terracotta token — and don't propagate the legacy wheat gold (`#C4A882`) into any new component; it is drift to reconcile, not a pattern to extend.
- **Don't** use Inter as the body face; it is a fallback only.
- **Don't** use sharp (0px) corners on any interactive element.
