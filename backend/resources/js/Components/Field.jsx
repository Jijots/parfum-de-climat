/**
 * Field — a labelled input with inline validation.
 *
 * The label is a mono caps eyebrow rather than a floating placeholder: a
 * placeholder disappears the moment someone types, which is exactly when a
 * long-form field like "timezone" still needs explaining.
 *
 * aria-invalid and aria-describedby are wired up so the error reaches a screen
 * reader instead of only being visible.
 */
export default function Field({ id, label, error, hint, children }) {
    const errorId = error ? `${id}-error` : undefined;
    const hintId = hint && !error ? `${id}-hint` : undefined;

    return (
        <div className="mb-5">
            <label
                htmlFor={id}
                className="block font-mono text-[11px] uppercase tracking-[0.15em] text-[var(--muted)] mb-2"
            >
                {label}
            </label>

            {children({ id, 'aria-invalid': !!error, 'aria-describedby': errorId ?? hintId })}

            {hint && !error && (
                <p id={hintId} className="mt-1.5 text-xs text-[var(--muted)]">{hint}</p>
            )}
            {error && (
                <p id={errorId} className="mt-1.5 text-xs text-[var(--error)]">{error}</p>
            )}
        </div>
    );
}
