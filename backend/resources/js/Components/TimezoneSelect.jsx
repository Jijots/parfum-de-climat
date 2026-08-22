import { useEffect, useMemo, useRef, useState } from 'react';

/**
 * Searchable timezone picker.
 *
 * A plain select holds roughly 419 options, which is unusable: finding
 * Asia/Manila means scrolling past four hundred entries, and native select
 * type-ahead only matches from the start of the string, so typing "manila"
 * finds nothing.
 *
 * This filters on any part of the name and matches the city as readily as the
 * region. Each row shows its current UTC offset, which is usually how someone
 * recognises their own zone when the city listed is not the one they live in.
 */

/** "Asia/Manila" -> "Manila · Asia" so the city, the useful part, leads. */
function label(zone) {
    const parts = zone.split('/');
    const city = parts[parts.length - 1].replace(/_/g, ' ');
    const region = parts.slice(0, -1).join(' / ').replace(/_/g, ' ');

    return region ? `${city} · ${region}` : city;
}

/**
 * Current UTC offset for a zone, e.g. "UTC+8".
 *
 * Uses the browser's own timezone database rather than a shipped table, so it
 * stays correct through daylight-saving changes. Wrapped because an unknown
 * zone throws, and one bad entry should not blank the whole list.
 */
function offset(zone) {
    try {
        const parts = new Intl.DateTimeFormat('en-US', {
            timeZone: zone,
            timeZoneName: 'shortOffset',
        }).formatToParts(new Date());

        return parts.find((p) => p.type === 'timeZoneName')?.value ?? '';
    } catch {
        return '';
    }
}

export default function TimezoneSelect({ id, value, onChange, zones, invalid, describedBy }) {
    const [open, setOpen] = useState(false);
    const [query, setQuery] = useState('');
    const [active, setActive] = useState(0);

    const wrapRef = useRef(null);
    const listRef = useRef(null);

    // Offsets are computed once for the whole list rather than per render:
    // 419 Intl.DateTimeFormat constructions on every keystroke is noticeable.
    const withOffsets = useMemo(
        () => zones.map((z) => ({ zone: z, text: label(z), off: offset(z) })),
        [zones]
    );

    const matches = useMemo(() => {
        const q = query.trim().toLowerCase();

        if (!q) return withOffsets;

        // Match the raw zone as well as the display label, so both
        // "asia/manila" and "manila" work.
        return withOffsets.filter(
            (o) => o.zone.toLowerCase().includes(q) || o.text.toLowerCase().includes(q)
        );
    }, [query, withOffsets]);

    // Close when focus or a click leaves the component.
    useEffect(() => {
        if (!open) return undefined;

        const onDown = (e) => {
            if (wrapRef.current && !wrapRef.current.contains(e.target)) setOpen(false);
        };

        document.addEventListener('mousedown', onDown);
        return () => document.removeEventListener('mousedown', onDown);
    }, [open]);

    // Keep the highlighted row in view while arrowing through a long list.
    useEffect(() => {
        if (!open || !listRef.current) return;
        listRef.current.querySelector('[data-active="true"]')?.scrollIntoView({ block: 'nearest' });
    }, [active, open]);

    const commit = (zone) => {
        onChange(zone);
        setQuery('');
        setOpen(false);
    };

    const onKeyDown = (e) => {
        if (!open && (e.key === 'ArrowDown' || e.key === 'Enter')) {
            setOpen(true);
            return;
        }

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            setActive((i) => Math.min(i + 1, matches.length - 1));
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            setActive((i) => Math.max(i - 1, 0));
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (matches[active]) commit(matches[active].zone);
        } else if (e.key === 'Escape') {
            setOpen(false);
            setQuery('');
        }
    };

    const selected = withOffsets.find((o) => o.zone === value);

    return (
        <div ref={wrapRef} className="relative">
            <input
                id={id}
                type="text"
                role="combobox"
                aria-expanded={open}
                aria-controls={`${id}-list`}
                aria-autocomplete="list"
                aria-invalid={invalid}
                aria-describedby={describedBy}
                autoComplete="off"
                className="input-field w-full"
                // Shows the chosen zone until the field is focused for typing,
                // so the current value is never hidden behind a placeholder.
                value={open ? query : (selected ? `${selected.text}  ${selected.off}` : value)}
                placeholder="Search for a city or region"
                onFocus={() => {
                    setOpen(true);
                    setQuery('');
                    setActive(Math.max(0, matches.findIndex((o) => o.zone === value)));
                }}
                onChange={(e) => {
                    setQuery(e.target.value);
                    setOpen(true);
                    setActive(0);
                }}
                onKeyDown={onKeyDown}
            />

            {open && (
                <ul
                    id={`${id}-list`}
                    ref={listRef}
                    role="listbox"
                    className="absolute z-30 mt-1 max-h-64 w-full overflow-y-auto rounded-2xl border border-[var(--hairline)] bg-[var(--panel)] py-1 shadow-lg"
                >
                    {matches.length === 0 && (
                        <li className="px-4 py-3 text-sm text-[var(--muted)]">
                            No timezone matches “{query}”.
                        </li>
                    )}

                    {/* Capped at 60 rows. The filter narrows fast, and rendering
                        four hundred list items on every keystroke janks. */}
                    {matches.slice(0, 60).map((o, i) => (
                        <li key={o.zone}>
                            <button
                                type="button"
                                role="option"
                                aria-selected={o.zone === value}
                                data-active={i === active}
                                onMouseEnter={() => setActive(i)}
                                onClick={() => commit(o.zone)}
                                className={`flex w-full items-baseline justify-between gap-4 px-4 py-2 text-left text-sm transition-colors ${
                                    i === active
                                        ? 'bg-[var(--color-accent-dim)] text-[var(--color-accent)]'
                                        : 'text-[var(--ink)]'
                                }`}
                            >
                                <span className="truncate">{o.text}</span>
                                <span className="shrink-0 font-mono text-[11px] text-[var(--muted)]">{o.off}</span>
                            </button>
                        </li>
                    ))}

                    {matches.length > 60 && (
                        <li className="px-4 py-2 font-mono text-[10px] uppercase tracking-[0.15em] text-[var(--muted)]">
                            {matches.length - 60} more, keep typing
                        </li>
                    )}
                </ul>
            )}
        </div>
    );
}
