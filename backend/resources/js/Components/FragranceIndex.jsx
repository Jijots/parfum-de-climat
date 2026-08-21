import { useState } from 'react';

/**
 * FragranceIndex — the catalogue as an editorial index rather than a card grid.
 *
 * Numbered rows separated by hairlines, each carrying a small plate of the
 * bottle, then name, house and gender in columns — the way a perfume house
 * prints its catalogue. A uniform grid of large image cards is the shape every
 * catalogue page defaults to; this reads as a document instead, and fits far
 * more of 24,000 entries on screen.
 *
 * The thumbnail is always visible. An earlier version revealed it only on
 * hover, which looked tidy but meant the bottle was invisible on touch screens
 * and invisible at rest everywhere else — for a catalogue where people
 * recognise things by sight, that hid the most useful column.
 */

function Thumb({ src, alt }) {
    const [failed, setFailed] = useState(false);

    return (
        <div className="h-12 w-12 shrink-0 rounded-xl overflow-hidden bg-[var(--panel)] border border-[var(--hairline)]">
            {src && !failed ? (
                <img
                    src={src}
                    alt={alt}
                    loading="lazy"
                    onError={() => setFailed(true)}
                    className="h-full w-full object-contain p-1"
                />
            ) : (
                <div className="h-full w-full flex items-center justify-center text-[var(--muted)]">
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.5">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z" />
                    </svg>
                </div>
            )}
        </div>
    );
}

export default function FragranceIndex({ results, startIndex, urls, onToggleWardrobe }) {
    return (
        <ol className="border-t border-[var(--hairline)]">
            {results.map((f, i) => (
                <li key={f.id} className="group border-b border-[var(--hairline)]">
                    <div className="flex items-center gap-4 sm:gap-5 py-3 px-2 -mx-2">
                        {/* Catalogue number — continues across pages rather than
                            restarting at 1, so it reads as a position in the whole
                            catalogue, not a position on this screen. */}
                        <span className="hidden sm:block font-mono text-[11px] text-[var(--muted)] tabular-nums w-10 shrink-0 opacity-60">
                            {String(startIndex + i + 1).padStart(4, '0')}
                        </span>

                        <a href={`${urls.fragrance}/${f.id}`} className="shrink-0">
                            <Thumb src={f.image_url} alt={f.name} />
                        </a>

                        <a
                            href={`${urls.fragrance}/${f.id}`}
                            className="flex-1 min-w-0 flex flex-col sm:flex-row sm:items-baseline sm:gap-5"
                        >
                            <span className="font-display text-lg sm:text-2xl font-light text-[var(--ink)] truncate group-hover:text-[var(--color-accent)] transition-colors">
                                {f.name}
                            </span>
                            <span className="text-xs text-[var(--muted)] truncate sm:flex-1">
                                {f.brand}
                            </span>
                        </a>

                        <span className="hidden md:block font-mono text-[11px] uppercase tracking-[0.15em] text-[var(--muted)] w-16 shrink-0">
                            {f.gender === 'masculine' ? 'Men' : f.gender === 'feminine' ? 'Women' : 'Unisex'}
                        </span>

                        <button
                            onClick={() => onToggleWardrobe(f.id)}
                            aria-label={f.in_wardrobe ? 'Remove from wardrobe' : 'Add to wardrobe'}
                            className={`shrink-0 p-1.5 rounded-full border transition-colors ${
                                f.in_wardrobe
                                    ? 'text-[var(--color-accent)] border-[var(--color-accent-border)] bg-[var(--color-accent-dim)]'
                                    : 'text-[var(--muted)] border-transparent hover:text-[var(--color-accent)] hover:border-[var(--color-accent-border)]'
                            }`}
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill={f.in_wardrobe ? 'currentColor' : 'none'} viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.5">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                            </svg>
                        </button>
                    </div>
                </li>
            ))}
        </ol>
    );
}
