import { motion } from 'motion/react';
import { useState } from 'react';

/**
 * FragranceIndex — the catalogue as an editorial index rather than a card grid.
 *
 * Numbered rows separated by hairlines, with brand, name and gender in columns,
 * the way a perfume house prints its catalogue. A uniform grid of image cards is
 * the default shape every catalogue page reaches for; this reads as a document
 * instead, which suits the apothecary-journal direction and lets far more of the
 * 24,000-strong catalogue fit on screen at once.
 *
 * The bottle image only appears on hover, anchored to the row. That keeps the
 * page typographic at rest while still giving the visual check a grid provides.
 */
export default function FragranceIndex({ results, startIndex, urls, onToggleWardrobe }) {
    const [hovered, setHovered] = useState(null);

    return (
        <div className="relative">
            <ol className="border-t border-[var(--hairline)]">
                {results.map((f, i) => (
                    <li
                        key={f.id}
                        onMouseEnter={() => setHovered(f.id)}
                        onMouseLeave={() => setHovered(null)}
                        className="group relative border-b border-[var(--hairline)]"
                    >
                        <div className="flex items-baseline gap-4 sm:gap-6 py-4 px-2 -mx-2 transition-colors">
                            {/* Catalogue number — continues across pages rather than
                                restarting at 1, so it reads as a position in the
                                whole catalogue, not a position on this screen. */}
                            <span className="font-mono text-[11px] text-[var(--muted)] tabular-nums w-10 shrink-0 opacity-60">
                                {String(startIndex + i + 1).padStart(4, '0')}
                            </span>

                            <a
                                href={`${urls.fragrance}/${f.id}`}
                                className="flex-1 min-w-0 flex flex-col sm:flex-row sm:items-baseline sm:gap-6"
                            >
                                <span className="font-display text-xl sm:text-2xl font-light text-[var(--ink)] truncate group-hover:text-[var(--color-accent)] transition-colors">
                                    {f.name}
                                </span>
                                <span className="text-xs text-[var(--muted)] truncate sm:flex-1">
                                    {f.brand}
                                </span>
                            </a>

                            <span className="hidden md:block text-[11px] uppercase tracking-widest text-[var(--muted)] w-16 shrink-0">
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

                        {/* Hover preview. Hidden below lg — on a touch screen there
                            is no hover, and the panel would only ever obstruct. */}
                        {hovered === f.id && f.image_url && (
                            <motion.div
                                initial={{ opacity: 0, x: -8 }}
                                animate={{ opacity: 1, x: 0 }}
                                transition={{ duration: 0.18, ease: 'easeOut' }}
                                className="hidden lg:block pointer-events-none absolute right-full top-1/2 -translate-y-1/2 mr-6 w-28 h-28 rounded-2xl overflow-hidden bg-[var(--panel)] border border-[var(--hairline)]"
                            >
                                <img src={f.image_url} alt="" className="w-full h-full object-contain p-2" />
                            </motion.div>
                        )}
                    </li>
                ))}
            </ol>
        </div>
    );
}
