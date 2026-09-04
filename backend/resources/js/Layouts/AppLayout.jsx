import { usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

/**
 * AppLayout — the public shell: fixed nav, theme toggle, account menu.
 *
 * A React port of layouts/app.blade.php. Both shells are live during the
 * migration, so they must agree on the 'pdc_theme' localStorage key or a user's
 * theme choice resets when they cross between a ported and an unported page.
 */

function useTheme() {
    const [dark, setDark] = useState(() => document.documentElement.classList.contains('dark'));

    useEffect(() => {
        document.documentElement.classList.toggle('dark', dark);
        try {
            localStorage.setItem('pdc_theme', dark ? 'dark' : 'light');
        } catch {
            // localStorage blocked (private browsing) — the class still applies
            // for this session, the preference just will not persist.
        }
    }, [dark]);

    return [dark, () => setDark((d) => !d)];
}

/**
 * NavLink — the label rolls up on hover, revealing an identical copy beneath.
 *
 * Two stacked copies inside a fixed-height clipping box; hovering translates
 * the pair up by exactly one line. The effect is cheap (one transform, no
 * layout) and reads as considered rather than decorative.
 *
 * aria-hidden on the second copy so screen readers announce the label once.
 */
function NavLink({ href, active, children }) {
    return (
        <a
            href={href}
            className={`group relative block h-5 overflow-hidden transition-colors ${
                active
                    ? 'text-[var(--ink)] font-medium'
                    : 'text-[var(--muted)] hover:text-[var(--ink)]'
            }`}
        >
            <span className="block transition-transform duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:-translate-y-5">
                <span className="block h-5 leading-5">{children}</span>
                <span className="block h-5 leading-5" aria-hidden="true">{children}</span>
            </span>

            {/* Active underline sits outside the clipping box so it is not
                cropped by overflow-hidden. */}
            {active && (
                <span className="absolute -bottom-0.5 left-0 right-0 h-px bg-[var(--color-accent)]" />
            )}
        </a>
    );
}

export default function AppLayout({ children }) {
    const { auth, nav, currentRoute } = usePage().props;
    const [dark, toggleTheme] = useTheme();
    const [menuOpen, setMenuOpen] = useState(false);
    const [mobileNavOpen, setMobileNavOpen] = useState(false);
    const menuRef = useRef(null);
    const mobileNavRef = useRef(null);

    // Close the account menu on any click outside it — the Alpine original used
    // @click.outside; in React that needs an explicit document listener.
    useEffect(() => {
        if (!menuOpen) return;

        const onClick = (e) => {
            if (menuRef.current && !menuRef.current.contains(e.target)) {
                setMenuOpen(false);
            }
        };

        document.addEventListener('mousedown', onClick);
        return () => document.removeEventListener('mousedown', onClick);
    }, [menuOpen]);

    // Same outside-click contract as the account menu, plus Escape: this is a
    // disclosure panel, not a modal, but it still needs a keyboard-only exit.
    useEffect(() => {
        if (!mobileNavOpen) return;

        const onClick = (e) => {
            if (mobileNavRef.current && !mobileNavRef.current.contains(e.target)) {
                setMobileNavOpen(false);
            }
        };
        const onKey = (e) => {
            if (e.key === 'Escape') setMobileNavOpen(false);
        };

        document.addEventListener('mousedown', onClick);
        document.addEventListener('keydown', onKey);
        return () => {
            document.removeEventListener('mousedown', onClick);
            document.removeEventListener('keydown', onKey);
        };
    }, [mobileNavOpen]);

    const isActive = (name) =>
        name === 'browse'
            ? currentRoute?.startsWith('browse')
            : currentRoute === name;

    // Shared with the mobile panel below — the primary sections, in order.
    const navItems = [
        { key: 'app', href: nav.app, label: 'Recommend' },
        { key: 'browse', href: nav.browse, label: 'Browse' },
        { key: 'wardrobe', href: nav.wardrobe, label: 'Wardrobe' },
        ...(auth.user?.verified ? [{ key: 'history', href: nav.history, label: 'History' }] : []),
    ];

    return (
        <div className="min-h-screen bg-[var(--bg)] text-[var(--ink)] font-sans antialiased transition-colors duration-150">
            <header
                className="fixed inset-x-0 top-0 z-50 border-b border-[var(--hairline)]"
                style={{
                    backgroundColor: 'var(--surface-bg)',
                    backdropFilter: 'blur(20px)',
                    WebkitBackdropFilter: 'blur(20px)',
                }}
            >
                <div className="mx-auto flex h-16 max-w-6xl items-center px-6">
                    {/* flex-1 on both sides keeps the centre nav optically centred.
                        min-w-0 lets this shrink instead of forcing the row to wrap;
                        the wordmark steps down below md so it doesn't crowd the icon
                        cluster, and truncate is a hard safety net — nowrap text on a
                        shrunk flex child overflows into its siblings, not out of view,
                        which is what produced the overlap this replaces. */}
                    <div className="flex-1 min-w-0">
                        <a
                            href={nav.landing}
                            className="block truncate font-display text-lg sm:text-xl md:text-2xl font-light tracking-wide text-[var(--ink)] hover:text-[var(--color-accent)] transition-colors duration-150"
                        >
                            Parfum <span className="text-[var(--color-accent)]">de</span> Climat
                        </a>
                    </div>

                    <div className="hidden md:flex items-center">
                        <nav className="flex items-center gap-6 text-sm">
                            {navItems.map((item) => (
                                <NavLink key={item.key} href={item.href} active={isActive(item.key)}>
                                    {item.label}
                                </NavLink>
                            ))}
                        </nav>
                    </div>

                    <div className="flex flex-1 justify-end items-center gap-3">
                        <button
                            type="button"
                            className="btn-icon"
                            title="Toggle dark mode"
                            onClick={toggleTheme}
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

                        {auth.user ? (
                            <div className="relative" ref={menuRef}>
                                <button onClick={() => setMenuOpen((o) => !o)} className="btn-icon" title="Account">
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.5">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                    </svg>
                                </button>

                                {menuOpen && (
                                    <div className="absolute right-0 mt-2 w-48 glass rounded-xl border border-[var(--hairline)] shadow-[0_8px_32px_rgba(0,0,0,0.08)] py-1">
                                        <p className="px-4 py-2 text-xs text-[var(--muted)] border-b border-[var(--hairline)]">
                                            {auth.user.name}
                                        </p>
                                        <a href={nav.profile} className="block px-4 py-2 text-sm text-[var(--ink)] hover:text-[var(--color-accent)] transition-colors">
                                            Edit Profile
                                        </a>
                                        {!auth.user.verified && (
                                            <a href={nav.verify} className="block px-4 py-2 text-sm text-[var(--ink)] hover:text-[var(--color-accent)] transition-colors">
                                                Verify email
                                            </a>
                                        )}
                                        {/* A real form post, not fetch: logout must rotate the
                                            session cookie, and letting the browser follow the
                                            redirect keeps that behaviour identical to Blade. */}
                                        <form method="POST" action={nav.logout}>
                                            <input
                                                type="hidden"
                                                name="_token"
                                                value={document.querySelector('meta[name=csrf-token]')?.content ?? ''}
                                            />
                                            <button type="submit" className="w-full text-left px-4 py-2 text-sm text-[var(--ink)] hover:text-[var(--error)] transition-colors">
                                                Sign out
                                            </button>
                                        </form>
                                    </div>
                                )}
                            </div>
                        ) : (
                            <>
                                {/* Below md both move into the mobile panel instead of
                                    shrinking in place — at 375px, a full wordmark plus
                                    Sign in, Get started, and the hamburger has no honest
                                    fit; the panel gives Get started a full-width primary
                                    button instead of a squeezed one. */}
                                <a href={nav.login} className="hidden md:inline text-sm text-[var(--muted)] hover:text-[var(--ink)] transition-colors">
                                    Sign in
                                </a>
                                <a href={nav.register} className="hidden md:inline-flex btn-primary text-sm py-1.5 px-4">
                                    Get started
                                </a>
                            </>
                        )}

                        {/* Mobile nav toggle — the desktop <nav> above is hidden below
                            md with nothing standing in for it; this and the panel below
                            are that replacement. */}
                        <button
                            type="button"
                            className="btn-icon md:hidden"
                            title="Menu"
                            aria-label={mobileNavOpen ? 'Close menu' : 'Open menu'}
                            aria-expanded={mobileNavOpen}
                            aria-controls="mobile-nav-panel"
                            onClick={() => setMobileNavOpen((o) => !o)}
                        >
                            {mobileNavOpen ? (
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.5">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            ) : (
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.5">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                                </svg>
                            )}
                        </button>
                    </div>
                </div>

                {/* Mobile nav panel — a disclosure, not a modal: no focus trap or
                    scroll lock, just the same outside-click + Escape contract as
                    the account menu above, extended to a full-width surface. */}
                {mobileNavOpen && (
                    <div
                        id="mobile-nav-panel"
                        ref={mobileNavRef}
                        className="md:hidden glass border-t border-[var(--hairline)] px-6 py-4"
                    >
                        {/* Guest conversion leads the panel — this is its only home
                            below md now, so it gets a full-width primary button
                            rather than the squeezed pill the header had no room for. */}
                        {!auth.user && (
                            <a href={nav.register} className="btn-primary w-full justify-center text-sm mb-3">
                                Get started
                            </a>
                        )}

                        <nav className="flex flex-col">
                            {navItems.map((item) => (
                                <a
                                    key={item.key}
                                    href={item.href}
                                    className={`flex items-center h-11 text-sm transition-colors ${
                                        isActive(item.key)
                                            ? 'text-[var(--ink)] font-medium'
                                            : 'text-[var(--muted)] hover:text-[var(--ink)]'
                                    }`}
                                >
                                    {item.label}
                                </a>
                            ))}
                            {!auth.user && (
                                <a
                                    href={nav.login}
                                    className="flex items-center h-11 text-sm text-[var(--muted)] hover:text-[var(--ink)] transition-colors border-t border-[var(--hairline)]"
                                >
                                    Sign in
                                </a>
                            )}
                        </nav>
                    </div>
                )}
            </header>

            {/* Offsets the fixed header so page content is not hidden beneath it */}
            <main className="pt-16">{children}</main>

            {/* A policy nobody can reach satisfies nothing — these links are the
                only route to them for someone deciding whether to sign up. */}
            <footer className="border-t border-[var(--hairline)] mt-24">
                <div className="mx-auto max-w-5xl px-6 py-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <p className="font-mono text-[11px] uppercase tracking-[0.15em] text-[var(--muted)]">
                        Parfum de Climat
                    </p>

                    <nav className="flex flex-wrap items-center gap-x-6 gap-y-2 font-mono text-[11px] uppercase tracking-[0.15em]">
                        <a href={nav.browse} className="text-[var(--muted)] hover:text-[var(--color-accent)] transition-colors">Browse</a>
                        <a href={nav.privacy} className="text-[var(--muted)] hover:text-[var(--color-accent)] transition-colors">Privacy</a>
                        <a href={nav.terms} className="text-[var(--muted)] hover:text-[var(--color-accent)] transition-colors">Terms</a>
                    </nav>
                </div>
            </footer>
        </div>
    );
}
