/**
 * StatBar — labelled value with a proportional fill.
 *
 * Used for rating/longevity/sillage (out of 5 or 10) and accord strength
 * (0–1). The `max` prop keeps one component covering both, and the readout
 * switches to a percentage when the scale is 0–1 since "0.8 / 1" reads poorly.
 */
export default function StatBar({ label, value, max }) {
    const pct = Math.max(0, Math.min(100, (value / max) * 100));
    const readout = max === 1 ? `${Math.round(pct)}%` : `${Number(value).toFixed(1)} / ${max}`;

    return (
        <div className="mb-3 last:mb-0">
            <div className="flex justify-between text-sm mb-1.5">
                <span className="text-[var(--muted)]">{label}</span>
                <span className="text-[var(--ink)]">{readout}</span>
            </div>
            <div className="h-1.5 w-full rounded-full bg-[var(--shadow-dark)] overflow-hidden">
                <div
                    className="h-full rounded-full bg-[var(--color-accent)]"
                    style={{ width: `${pct}%` }}
                />
            </div>
        </div>
    );
}
