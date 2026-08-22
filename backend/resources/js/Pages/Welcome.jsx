import { Head } from '@inertiajs/react';
import { motion } from 'motion/react';
import AppLayout from '../Layouts/AppLayout';
import Rule from '../Components/Rule';
import AnimatedNumber from '../Components/AnimatedNumber';

const FEATURES = [
    {
        n: '01',
        title: 'Live weather',
        body: 'Your coordinates or a city name become temperature, humidity and a hemisphere-corrected season.',
    },
    {
        n: '02',
        title: 'Note-level scoring',
        body: 'Every note carries a climate profile. A Python engine weighs each one against the conditions and ranks your shelf.',
    },
    {
        n: '03',
        title: 'Smells like this',
        body: 'A TF-IDF index over the whole catalogue finds fragrances built from the same rare materials.',
    },
];

const STEPS = [
    ['Build a wardrobe', 'Add what you own from the catalogue. No account needed to start.'],
    ['Take a reading', 'Share a location and the engine scores your collection against it.'],
    ['Wear it', 'Log what you chose. The record builds a picture of what you reach for, and when.'],
];

/**
 * Welcome — the landing page.
 *
 * Ported from welcome.blade.php and re-cut to the editorial language: numbered
 * sections, hairline rules, mixed roman/italic display type. The catalogue
 * figures are passed from the controller rather than hard-coded, so the page
 * cannot drift out of date as the catalogue grows.
 */
export default function Welcome({ urls, stats }) {
    return (
        <AppLayout>
            <Head title="Parfum de Climat" />

            {/* ── Hero ───────────────────────────────────────────────────── */}
            <section className="relative overflow-hidden px-6 pt-24 pb-28">
                {/* One static accent glow. No animation — a moving gradient is
                    the loudest tell of a generic landing page. */}
                <div className="pointer-events-none absolute inset-0 flex items-start justify-center" aria-hidden="true">
                    <div className="h-[520px] w-[520px] rounded-full bg-[var(--color-accent)]/10 blur-3xl" />
                </div>

                <div className="relative z-10 mx-auto max-w-4xl">
                    <motion.p
                        initial={{ opacity: 0, y: 8 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.4, ease: [0.22, 1, 0.36, 1] }}
                        className="font-mono text-[11px] uppercase tracking-[0.2em] text-[var(--color-accent)] mb-6"
                    >
                        Weather-aware fragrance intelligence
                    </motion.p>

                    <h1 className="font-display text-[clamp(3rem,9vw,6.5rem)] font-light leading-[0.95] tracking-tight text-[var(--ink)]">
                        {['Wear', 'the'].map((word, i) => (
                            <motion.span
                                key={word}
                                initial={{ opacity: 0, y: 12 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.45, delay: 0.05 + i * 0.06, ease: [0.22, 1, 0.36, 1] }}
                                className="inline-block mr-[0.25em]"
                            >
                                {word}
                            </motion.span>
                        ))}
                        <motion.em
                            initial={{ opacity: 0, y: 12 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.45, delay: 0.17, ease: [0.22, 1, 0.36, 1] }}
                            className="inline-block italic text-[var(--color-accent)]"
                        >
                            Weather.
                        </motion.em>
                    </h1>

                    <motion.p
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        transition={{ duration: 0.5, delay: 0.3 }}
                        className="mt-8 max-w-xl text-lg font-light leading-relaxed text-[var(--muted)]"
                    >
                        Parfum de Climat reads your local conditions and ranks the fragrances you
                        own against today&apos;s temperature, humidity and season, note by note.
                    </motion.p>

                    <motion.div
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        transition={{ duration: 0.5, delay: 0.4 }}
                        className="mt-10 flex flex-col sm:flex-row items-start gap-3"
                    >
                        <a href={urls.app} className="btn-primary">Take a reading</a>
                        <a href={urls.browse} className="btn-ghost">Browse the catalogue</a>
                    </motion.div>

                    <div className="mt-16 flex flex-wrap gap-x-12 gap-y-4 font-mono text-[11px] uppercase tracking-[0.12em] text-[var(--muted)]">
                        <span>
                            <AnimatedNumber value={stats.fragrances} className="text-[var(--ink)] text-base" />{' '}
                            fragrances
                        </span>
                        <span>
                            <AnimatedNumber value={stats.brands} className="text-[var(--ink)] text-base" />{' '}
                            houses
                        </span>
                        <span>
                            <AnimatedNumber value={stats.profiles} className="text-[var(--ink)] text-base" />{' '}
                            note profiles
                        </span>
                    </div>
                </div>
            </section>

            {/* ── What it does ───────────────────────────────────────────── */}
            <section className="mx-auto max-w-5xl px-6 pb-24">
                <Rule label="What it does" />

                <div className="grid gap-10 sm:grid-cols-3">
                    {FEATURES.map((f) => (
                        <div key={f.n}>
                            <p className="font-mono text-[11px] text-[var(--color-accent)] tabular-nums mb-3">{f.n}</p>
                            <h3 className="font-display text-2xl font-light text-[var(--ink)] mb-2">{f.title}</h3>
                            <p className="text-sm leading-relaxed text-[var(--muted)]">{f.body}</p>
                        </div>
                    ))}
                </div>
            </section>

            {/* ── How it works ───────────────────────────────────────────── */}
            <section className="mx-auto max-w-5xl px-6 pb-24">
                <Rule label="How it works" />

                <ol className="border-t border-[var(--hairline)]">
                    {STEPS.map(([title, body], i) => (
                        <li
                            key={title}
                            className="border-b border-[var(--hairline)] py-6 grid grid-cols-1 sm:grid-cols-[3rem_1fr] gap-2 sm:gap-8"
                        >
                            <span className="font-mono text-[11px] text-[var(--muted)] tabular-nums pt-2">
                                {String(i + 1).padStart(2, '0')}
                            </span>
                            <div>
                                <h3 className="font-display text-2xl font-light text-[var(--ink)]">{title}</h3>
                                <p className="mt-1.5 text-sm leading-relaxed text-[var(--muted)] max-w-prose">{body}</p>
                            </div>
                        </li>
                    ))}
                </ol>
            </section>

            {/* ── Close ──────────────────────────────────────────────────── */}
            <section className="mx-auto max-w-5xl px-6 pb-32">
                <div className="border-y border-[var(--hairline)] py-14">
                    <h2 className="font-display text-[clamp(2rem,5vw,3.5rem)] font-light leading-tight text-[var(--ink)]">
                        Start with what you{' '}
                        <em className="italic text-[var(--color-accent)]">already own.</em>
                    </h2>
                    <p className="mt-4 max-w-lg text-sm leading-relaxed text-[var(--muted)]">
                        No account needed to try it. A wardrobe you build as a guest carries
                        over if you decide to sign up.
                    </p>

                    <div className="mt-8 flex flex-col sm:flex-row items-start gap-3">
                        <a href={urls.register} className="btn-primary">Create an account</a>
                        <a href={urls.app} className="btn-ghost">Try it first</a>
                    </div>
                </div>
            </section>
        </AppLayout>
    );
}
