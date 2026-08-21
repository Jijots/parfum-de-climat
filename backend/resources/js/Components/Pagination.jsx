/**
 * Pagination — a sliding window of page buttons with first/last shortcuts.
 *
 * Kept purely presentational: it reports which page was clicked and knows
 * nothing about how results are fetched.
 */
export default function Pagination({ currentPage, lastPage, loading, onNavigate }) {
    if (lastPage <= 1) return null;

    // A five-wide window centred on the current page, clamped at both ends so
    // it stays five wide near the start and finish rather than shrinking.
    const windowSize = 5;
    let start = Math.max(1, currentPage - Math.floor(windowSize / 2));
    const end = Math.min(lastPage, start + windowSize - 1);
    start = Math.max(1, end - windowSize + 1);

    const pages = [];
    for (let p = start; p <= end; p++) pages.push(p);

    return (
        <div className="mt-8 flex items-center justify-center gap-2">
            <button
                onClick={() => onNavigate(currentPage - 1)}
                disabled={loading || currentPage <= 1}
                className="btn-ghost px-3 py-2 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
            >
                Prev
            </button>

            {start > 1 && (
                <button onClick={() => onNavigate(1)} className="btn-ghost px-3 py-2 text-sm">1</button>
            )}
            {start > 2 && <span className="px-1 text-sm text-[var(--muted)]">…</span>}

            {pages.map((p) => (
                <button
                    key={p}
                    onClick={() => onNavigate(p)}
                    className={`btn-ghost px-3 py-2 text-sm min-w-10 ${
                        p === currentPage ? 'bg-[var(--color-accent)] text-white border-transparent' : ''
                    }`}
                >
                    {p}
                </button>
            ))}

            {end < lastPage - 1 && <span className="px-1 text-sm text-[var(--muted)]">…</span>}
            {end < lastPage && (
                <button onClick={() => onNavigate(lastPage)} className="btn-ghost px-3 py-2 text-sm">
                    {lastPage}
                </button>
            )}

            <button
                onClick={() => onNavigate(currentPage + 1)}
                disabled={loading || currentPage >= lastPage}
                className="btn-ghost px-3 py-2 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
            >
                Next
            </button>
        </div>
    );
}
