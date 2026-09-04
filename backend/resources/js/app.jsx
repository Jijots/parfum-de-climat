import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import '../css/app.css';

/**
 * Inertia entry point.
 *
 * Pages live in resources/js/Pages and are matched by the name a controller
 * passes to Inertia::render('Browse') -> resources/js/Pages/Browse.jsx.
 *
 * This runs alongside app.js (Alpine), which still drives the Blade pages that
 * have not been ported. Each page loads one entry or the other, never both.
 */

// Theme must be applied before first paint or the page flashes light then dark.
// The key must stay 'pdc_theme' to match the inline guard in the Blade layouts —
// a user's choice has to survive moving between ported and unported pages.
const storedTheme = localStorage.getItem('pdc_theme');
const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
if (storedTheme === 'dark' || (!storedTheme && prefersDark)) {
    document.documentElement.classList.add('dark');
}

createInertiaApp({
    title: (title) => (title ? `${title} · Parfum de Climat` : 'Parfum de Climat'),

    // eager: true bundles every page into one asset. With a handful of pages
    // that is smaller and simpler than the waterfall of lazy chunk requests,
    // and it removes the loading flash between navigations.
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.jsx', { eager: true });
        const page = pages[`./Pages/${name}.jsx`];

        if (!page) {
            throw new Error(`Inertia page not found: ./Pages/${name}.jsx`);
        }

        return page;
    },

    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },

    progress: {
        // The bar is appended to <body>, so the adaptive accent var resolves
        // against :root / .dark and flips with the theme like everything else.
        color: 'var(--accent)',
        showSpinner: false,
    },
});
