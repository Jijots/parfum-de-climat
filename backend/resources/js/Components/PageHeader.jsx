import DisplayHeading from './DisplayHeading';

/**
 * PageHeader — the masthead every page opens with.
 *
 * Three fixed parts, in this order:
 *   eyebrow   small mono caps in the accent colour, naming the section
 *   heading   mixed roman/italic serif, the italic word carrying the accent
 *   subline   one line of muted context, held to a readable measure
 *
 * Exists so the pattern lives in one place. Repeating the markup per page is
 * how a design language quietly drifts — a different heading size here, a
 * missing eyebrow there — until the pages stop looking related.
 */
export default function PageHeader({ eyebrow, children, accent, subline, actions }) {
    return (
        <div className="mb-10 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-6">
            <div>
                {eyebrow && (
                    <p className="font-mono text-[11px] uppercase tracking-[0.2em] text-[var(--color-accent)] mb-3">
                        {eyebrow}
                    </p>
                )}

                <DisplayHeading className="text-4xl sm:text-6xl" accent={accent}>
                    {children}
                </DisplayHeading>

                {subline && (
                    <p className="mt-4 text-sm text-[var(--muted)] max-w-md">{subline}</p>
                )}
            </div>

            {actions && <div className="shrink-0">{actions}</div>}
        </div>
    );
}
