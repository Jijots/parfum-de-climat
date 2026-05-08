/**
 * app.js — Parfum de Climat
 * ─────────────────────────────────────────────────────────────────────────────
 * Alpine is imported from npm (not CDN) so we control the full lifecycle:
 *   1. Register stores BEFORE Alpine.start() — no event timing issues.
 *   2. Expose window.Alpine so Blade / inline x-data attributes still work.
 *
 * The CDN <script> tag has been removed from layouts/app.blade.php.
 */

import './bootstrap';
import Alpine from 'alpinejs';

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
/* Stores must be registered BEFORE Alpine.start(). Because Alpine is        */
/* imported (not CDN), we are guaranteed to reach this line before Alpine     */
/* has processed a single DOM node — no race condition possible.             */
/*                                                                            */
/* Usage in Blade / Alpine components:                                        */
/*   Toggle:        @click="$store.theme.toggle()"                           */
/*   Check state:   :class="{ 'dark': $store.theme.dark }"                   */
/*   Conditional:   <span x-show="$store.theme.dark">Moon icon</span>        */
Alpine.store('theme', {
    dark: document.documentElement.classList.contains('dark'),

    toggle() {
        this.dark = !this.dark;
        document.documentElement.classList.toggle('dark', this.dark);
        localStorage.setItem('pdc_theme', this.dark ? 'dark' : 'light');
    },
});

/* ── 3. Start Alpine ─────────────────────────────────────────────────────── */
window.Alpine = Alpine; // expose globally so inline x-data="" works in Blade
Alpine.start();
