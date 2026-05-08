/**
 * app.js — Parfum de Climat
 * ─────────────────────────────────────────────────────────────────────────────
 * Two responsibilities:
 *
 *   1. FOUC-safe dark mode initialisation (runs synchronously before first paint)
 *   2. Alpine.js store registration (wires the theme toggle logic)
 *
 * Alpine itself is loaded via CDN with the `defer` attribute in the Blade
 * layout, so it is NOT imported here. The `alpine:init` event fires before
 * Alpine walks the DOM — that is the correct hook for store registration.
 */

import './bootstrap';

/* ── 1. Dark mode: apply class before first paint ─────────────────────────*/
/*                                                                            */
/* This IIFE executes synchronously as a JS module. The <html> class is set  */
/* before any CSS is applied, preventing a flash of wrong theme colour.      */
/*                                                                            */
/* Precedence:                                                                */
/*   1. Explicit user preference stored in localStorage (pdc_theme)           */
/*   2. OS/browser prefers-color-scheme: dark media query                     */
/*   3. Light mode (default — no class added)                                 */
(function () {
    const saved       = localStorage.getItem('pdc_theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    if (saved === 'dark' || (!saved && prefersDark)) {
        document.documentElement.classList.add('dark');
    }
}());

/* ── 2. Alpine.js store: theme toggle ────────────────────────────────────── */
/*                                                                            */
/* Alpine is loaded via CDN < script defer > in the Blade layout.            */
/* The 'alpine:init' event fires before Alpine processes x-data directives,  */
/* making it the safe registration point for global stores.                  */
/*                                                                            */
/* Usage in Blade / Alpine components:                                        */
/*   Toggle:        @click="$store.theme.toggle()"                           */
/*   Check state:   x-bind:class="{ 'dark': $store.theme.dark }"             */
/*   Conditional:   <span x-show="$store.theme.dark">Moon icon</span>        */
/**
 * Register the theme store as soon as Alpine is ready.
 *
 * We listen for both 'alpine:init' (fires before Alpine walks the DOM,
 * which is the correct hook) AND fall back to registering immediately
 * if Alpine has already initialised by the time this module runs.
 * This prevents the race condition where both x-show icons are hidden
 * because $store.theme is undefined on first evaluation.
 */
function registerThemeStore() {
    Alpine.store('theme', {
        dark: document.documentElement.classList.contains('dark'),

        toggle() {
            this.dark = !this.dark;
            document.documentElement.classList.toggle('dark', this.dark);
            localStorage.setItem('pdc_theme', this.dark ? 'dark' : 'light');
        },
    });
}

if (window.Alpine) {
    // Alpine already loaded (e.g. cached CDN) — register immediately
    registerThemeStore();
} else {
    document.addEventListener('alpine:init', registerThemeStore);
}
