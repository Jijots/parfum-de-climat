/**
 * Rule — a labelled hairline that opens a section.
 *
 * The printed-catalogue equivalent of a section header: a small caps label
 * against a rule that runs to the edge of the measure. Used instead of stacking
 * another bordered card, which is what makes a page read as a dashboard.
 */
export default function Rule({ label, children }) {
    return (
        <div className="flex items-baseline gap-4 mb-5">
            <span className="font-mono text-[11px] uppercase tracking-[0.2em] text-[var(--muted)] shrink-0">
                {label}
            </span>
            <span className="h-px flex-1 bg-[var(--hairline)]" />
            {children && <span className="text-[11px] text-[var(--muted)] shrink-0">{children}</span>}
        </div>
    );
}
