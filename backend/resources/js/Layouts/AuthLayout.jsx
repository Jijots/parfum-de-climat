import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

/**
 * AuthLayout — the shell for sign-in, registration and password recovery.
 *
 * Deliberately not AppLayout: those pages have no wardrobe, no catalogue and
 * nothing to navigate to, so the full nav would offer choices that go nowhere.
 * A wordmark, a centred column and a way back is the whole surface.
 */
export default function AuthLayout({ eyebrow, title, accent, subline, children, footer }) {
    const { nav, flash } = usePage().props;

    // Same FOUC-guarded key the rest of the app uses, so a visitor arriving
    // straight at /login sees the theme they chose elsewhere.
    const [dark, setDark] = useState(() => document.documentElement.classList.contains('dark'));

    useEffect(() => {
        document.documentElement.classList.toggle('dark', dark);
        try {
            localStorage.setItem('pdc_theme', dark ? 'dark' : 'light');
        } catch {
            // localStorage blocked — the class still applies for this session.
        }
    }, [dark]);

    return (
        <div className="min-h-screen bg-[var(--bg)] text-[var(--ink)] font-sans antialiased flex flex-col">
            <header className="flex items-center justify-between px-6 py-6 max-w-5xl mx-auto w-full">
                <a
                    href={nav.landing}
                    className="font-display text-xl font-light tracking-wide text-[var(--ink)] hover:text-[var(--color-accent)] transition-colors"
                >
                    Parfum <span className="text-[var(--color-accent)]">de</span> Climat
                </a>

                <button
                    type="button"
                    onClick={() => setDark((d) => !d)}
                    className="btn-icon"
                    aria-label={dark ? 'Switch to light mode' : 'Switch to dark mode'}
                >
                    {dark ? (
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.5">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M12 3v2.25M18.364 5.636l-1.591 1.591M21 12h-2.25M18.364 18.364l-1.591-1.591M12 21v-2.25M5.636 18.364l1.591-1.591M3 12h2.25M5.636 5.636l1.591 1.591M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                        </svg>
                    ) : (
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.5">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M21.752 15.002A9.72 9.72 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                        </svg>
                    )}
                </button>
            </header>

            <main className="flex-1 flex items-center justify-center px-6 py-10">
                <div className="w-full max-w-sm">
                    {eyebrow && (
                        <p className="font-mono text-[11px] uppercase tracking-[0.2em] text-[var(--color-accent)] mb-3">
                            {eyebrow}
                        </p>
                    )}

                    <h1 className="font-display text-4xl font-light leading-[1.05] text-[var(--ink)]">
                        {title}{' '}
                        {accent && <em className="italic text-[var(--color-accent)]">{accent}</em>}
                    </h1>

                    {subline && <p className="mt-3 text-sm text-[var(--muted)]">{subline}</p>}

                    {/* Session status, e.g. "we have emailed your reset link". */}
                    {flash?.status && (
                        <p className="mt-5 border-l-2 border-[var(--color-accent)] pl-3 py-1 text-sm text-[var(--muted)]">
                            {flash.status}
                        </p>
                    )}

                    <div className="mt-8">{children}</div>

                    {footer && (
                        <div className="mt-8 pt-6 border-t border-[var(--hairline)] text-sm text-[var(--muted)]">
                            {footer}
                        </div>
                    )}

                    <p className="mt-8 font-mono text-[10px] uppercase tracking-[0.15em] text-[var(--muted)]">
                        <a href={nav.privacy} className="hover:text-[var(--color-accent)] transition-colors">Privacy</a>
                        <span className="mx-2 opacity-40">/</span>
                        <a href={nav.terms} className="hover:text-[var(--color-accent)] transition-colors">Terms</a>
                    </p>
                </div>
            </main>
        </div>
    );
}
