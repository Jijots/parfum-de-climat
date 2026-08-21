import { Head, router } from '@inertiajs/react';
import { motion } from 'motion/react';
import { useState } from 'react';
import AppLayout from '../Layouts/AppLayout';
import PageHeader from '../Components/PageHeader';
import AnimatedNumber from '../Components/AnimatedNumber';

const SEASON_EMOJI = { spring: '🌸', summer: '☀️', autumn: '🍂', winter: '❄️' };

/**
 * History — past recommendation runs, as a timeline.
 *
 * A continuous rule runs down the left with a node per entry, so the sequence
 * reads as elapsed time rather than as a list of rows. That matters here: the
 * page's subject is what the weather was doing on particular days, and a card
 * stack flattens exactly the dimension worth showing.
 *
 * Each entry expands to reveal what the engine returned. The payload is decoded
 * server-side, so this component never parses JSON.
 */
export default function History({ logs, pagination, urls }) {
    const [expanded, setExpanded] = useState({});
    const toggle = (id) => setExpanded((e) => ({ ...e, [id]: !e[id] }));

    return (
        <AppLayout>
            <Head title="History" />

            <div className="mx-auto max-w-3xl px-6 py-16">
                <PageHeader
                    eyebrow="Your Record"
                    accent="Worn"
                    subline={
                        <>
                            <AnimatedNumber value={pagination.total} />{' '}
                            {pagination.total === 1 ? 'reading' : 'readings'} logged against the weather.
                        </>
                    }
                >
                    What You
                </PageHeader>

                {logs.length === 0 ? (
                    <div className="border-l-2 border-[var(--hairline)] pl-6 py-2">
                        <p className="font-display text-2xl font-light text-[var(--ink)] mb-2">
                            Nothing logged yet
                        </p>
                        <p className="text-sm text-[var(--muted)] mb-5">
                            Take a reading and it will appear here.
                        </p>
                        <a href={urls.app} className="btn-primary inline-flex text-sm">
                            Get a Recommendation
                        </a>
                    </div>
                ) : (
                    <ol className="relative">
                        {/* The spine. Inset to sit under the node centres. */}
                        <span
                            aria-hidden="true"
                            className="absolute left-[5px] top-2 bottom-2 w-px bg-[var(--hairline)]"
                        />

                        {logs.map((log, i) => (
                            <motion.li
                                key={log.id}
                                initial={{ opacity: 0, x: -6 }}
                                animate={{ opacity: 1, x: 0 }}
                                transition={{ duration: 0.3, delay: Math.min(i, 8) * 0.03, ease: [0.22, 1, 0.36, 1] }}
                                className="relative pl-8 pb-8 last:pb-0"
                            >
                                {/* Node */}
                                <span
                                    aria-hidden="true"
                                    className="absolute left-0 top-[7px] h-[11px] w-[11px] rounded-full border-2 border-[var(--bg)] bg-[var(--color-accent)]"
                                />

                                <button
                                    onClick={() => toggle(log.id)}
                                    aria-expanded={!!expanded[log.id]}
                                    className="w-full text-left group"
                                >
                                    <div className="flex items-baseline gap-3 flex-wrap">
                                        <span className="font-display text-3xl font-light text-[var(--ink)] leading-none">
                                            {Math.round(log.temperature_celsius)}°
                                        </span>
                                        <span className="font-mono text-[11px] uppercase tracking-[0.15em] text-[var(--muted)]">
                                            {log.location_name ?? 'Unknown location'}
                                        </span>
                                        {log.season && (
                                            <span className="text-[11px] text-[var(--muted)]">
                                                {SEASON_EMOJI[String(log.season).toLowerCase()] ?? ''}
                                            </span>
                                        )}
                                    </div>

                                    <div className="mt-1.5 flex items-center gap-3 flex-wrap">
                                        <span className="text-xs text-[var(--muted)]">{log.created_at}</span>
                                        <span className="text-xs text-[var(--muted)]">·</span>
                                        <span className="text-xs text-[var(--muted)]">{log.weather_condition}</span>

                                        {log.chosen_name && (
                                            <span className="text-[11px] font-mono uppercase tracking-[0.1em] text-[var(--color-accent)]">
                                                wore {log.chosen_name}
                                            </span>
                                        )}
                                    </div>
                                </button>

                                {expanded[log.id] && (
                                    <motion.div
                                        initial={{ opacity: 0, height: 0 }}
                                        animate={{ opacity: 1, height: 'auto' }}
                                        transition={{ duration: 0.22, ease: 'easeOut' }}
                                        className="overflow-hidden"
                                    >
                                        <div className="mt-4 pt-4 border-t border-[var(--hairline)]">
                                            {log.results.length === 0 ? (
                                                <p className="text-xs text-[var(--muted)]">
                                                    No scored results were stored for this reading.
                                                </p>
                                            ) : (
                                                <ul className="space-y-2.5">
                                                    {log.results.map((r, j) => (
                                                        <li key={j} className="flex items-baseline gap-4">
                                                            <span className="font-mono text-[11px] text-[var(--muted)] tabular-nums w-9 shrink-0">
                                                                {Math.round((r.score ?? 0) * 100)}%
                                                            </span>
                                                            <span className="min-w-0">
                                                                <span className="block font-display text-lg font-light text-[var(--ink)] truncate">
                                                                    {r.name}
                                                                </span>
                                                                <span className="block text-[11px] text-[var(--muted)] truncate">
                                                                    {r.brand}
                                                                </span>
                                                            </span>
                                                        </li>
                                                    ))}
                                                </ul>
                                            )}
                                        </div>
                                    </motion.div>
                                )}
                            </motion.li>
                        ))}
                    </ol>
                )}

                {pagination.last_page > 1 && (
                    <div className="mt-12 flex items-center justify-center gap-4 font-mono text-[11px] uppercase tracking-[0.15em]">
                        <button
                            onClick={() => router.get(urls.history, { page: pagination.current_page - 1 })}
                            disabled={pagination.current_page <= 1}
                            className="text-[var(--muted)] hover:text-[var(--color-accent)] transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                        >
                            ← Earlier
                        </button>
                        <span className="text-[var(--muted)]">
                            {pagination.current_page} / {pagination.last_page}
                        </span>
                        <button
                            onClick={() => router.get(urls.history, { page: pagination.current_page + 1 })}
                            disabled={pagination.current_page >= pagination.last_page}
                            className="text-[var(--muted)] hover:text-[var(--color-accent)] transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                        >
                            Later →
                        </button>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
