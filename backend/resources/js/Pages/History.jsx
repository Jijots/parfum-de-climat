import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../Layouts/AppLayout';

/**
 * History — past recommendation runs.
 *
 * Each row expands to show what the engine returned that day. The payload is
 * already decoded server-side, so this component never parses JSON.
 */
export default function History({ logs, pagination, urls }) {
    const [expanded, setExpanded] = useState({});

    const toggle = (id) => setExpanded((e) => ({ ...e, [id]: !e[id] }));

    return (
        <AppLayout>
            <Head title="History" />

            <div className="mx-auto max-w-4xl px-6 py-12">
                <h1 className="font-display text-4xl font-light text-[var(--ink)]">History</h1>
                <p className="mt-2 text-sm text-[var(--muted)]">
                    {pagination.total} recommendation{pagination.total === 1 ? '' : 's'} so far.
                </p>

                {logs.length === 0 ? (
                    <div className="neu-raised rounded-2xl p-12 text-center mt-8">
                        <p className="font-display text-2xl font-light text-[var(--ink)] mb-3">No history yet</p>
                        <p className="text-sm text-[var(--muted)] mb-6">
                            Get a recommendation and it will show up here.
                        </p>
                        <a href={urls.app} className="btn-primary inline-flex">Get a Recommendation</a>
                    </div>
                ) : (
                    <div className="mt-8 space-y-3">
                        {logs.map((log) => (
                            <div key={log.id} className="neu-raised rounded-2xl overflow-hidden">
                                <button
                                    onClick={() => toggle(log.id)}
                                    className="w-full flex items-center gap-4 p-4 text-left"
                                >
                                    <span className="text-sm font-medium text-[var(--ink)] shrink-0 w-16">
                                        {Math.round(log.temperature_celsius)}°C
                                    </span>
                                    <span className="flex-1 min-w-0">
                                        <span className="block text-sm text-[var(--ink)] truncate">
                                            {log.location_name ?? 'Unknown location'}
                                        </span>
                                        <span className="block text-xs text-[var(--muted)]">
                                            {log.created_at} · {log.weather_condition}
                                        </span>
                                    </span>
                                    {log.chosen_name && (
                                        <span className="text-xs px-2 py-0.5 rounded-full bg-[var(--color-accent)]/10 text-[var(--color-accent)] border border-[var(--color-accent)]/30 shrink-0">
                                            Wore {log.chosen_name}
                                        </span>
                                    )}
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        className={`h-4 w-4 shrink-0 text-[var(--muted)] transition-transform ${expanded[log.id] ? 'rotate-180' : ''}`}
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.5"
                                    >
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>

                                {expanded[log.id] && (
                                    <div className="px-4 pb-4 border-t border-[var(--hairline)] pt-3">
                                        {log.results.length === 0 ? (
                                            <p className="text-xs text-[var(--muted)]">
                                                No scored results were stored for this run.
                                            </p>
                                        ) : (
                                            <ul className="space-y-2">
                                                {log.results.map((r, i) => (
                                                    <li key={i} className="flex items-center gap-3 text-sm">
                                                        <span className="text-xs text-[var(--muted)] w-10 shrink-0">
                                                            {Math.round((r.score ?? 0) * 100)}%
                                                        </span>
                                                        <span className="text-[var(--ink)] truncate">
                                                            {r.brand} — {r.name}
                                                        </span>
                                                    </li>
                                                ))}
                                            </ul>
                                        )}
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                )}

                {pagination.last_page > 1 && (
                    <div className="mt-8 flex items-center justify-center gap-2">
                        <button
                            onClick={() => router.get(urls.history, { page: pagination.current_page - 1 })}
                            disabled={pagination.current_page <= 1}
                            className="btn-ghost px-3 py-2 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Prev
                        </button>
                        <span className="px-3 py-2 text-sm text-[var(--muted)]">
                            Page {pagination.current_page} of {pagination.last_page}
                        </span>
                        <button
                            onClick={() => router.get(urls.history, { page: pagination.current_page + 1 })}
                            disabled={pagination.current_page >= pagination.last_page}
                            className="btn-ghost px-3 py-2 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Next
                        </button>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
