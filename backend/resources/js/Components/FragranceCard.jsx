import { useState } from 'react';

const GENDER_LABELS = {
    masculine: 'Men',
    feminine: 'Women',
    unisex: 'Unisex',
};

/**
 * FragranceCard — one bottle in the browse grid.
 *
 * Shows an optional match percentage, used by the "Smells like this" section
 * where cards carry a similarity score from the TF-IDF index.
 */
export default function FragranceCard({ fragrance, detailUrl, onToggleWardrobe, match }) {
    // Some catalog rows point at dead remote images. Track the failure locally
    // so the card falls back to the placeholder instead of a broken-image icon.
    const [imageFailed, setImageFailed] = useState(false);
    const showImage = fragrance.image_url && !imageFailed;

    return (
        <div className="neu-raised rounded-2xl overflow-hidden group">
            <a href={detailUrl} className="relative block aspect-square bg-[var(--shadow-dark)]/30 overflow-hidden">
                {showImage ? (
                    <img
                        src={fragrance.image_url}
                        alt={fragrance.name}
                        loading="lazy"
                        onError={() => setImageFailed(true)}
                        className="w-full h-full object-contain p-4 group-hover:scale-105 transition-transform duration-300"
                    />
                ) : (
                    <div className="w-full h-full flex items-center justify-center text-[var(--muted)]">
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-10 w-10 opacity-25" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z" />
                        </svg>
                    </div>
                )}

                {typeof match === 'number' && (
                    <span className="absolute top-2 right-2 text-[10px] font-medium px-2 py-0.5 rounded-full bg-[var(--color-accent)] text-white shadow">
                        {match}%
                    </span>
                )}
            </a>

            <div className="p-3">
                <p className="text-xs text-[var(--muted)] truncate">{fragrance.brand}</p>
                <a
                    href={detailUrl}
                    className="block text-sm font-medium text-[var(--ink)] hover:text-[var(--color-accent)] transition-colors truncate mt-0.5"
                >
                    {fragrance.name}
                </a>

                <div className="mt-2">
                    <span className="text-xs px-2 py-0.5 rounded-full border border-[var(--hairline)] text-[var(--muted)]">
                        {GENDER_LABELS[fragrance.gender] ?? 'Unisex'}
                    </span>
                </div>

                {onToggleWardrobe && (
                    <button
                        onClick={() => onToggleWardrobe(fragrance.id)}
                        className={`mt-2 w-full flex items-center justify-center gap-1.5 rounded-lg border py-1.5 text-xs transition-colors ${
                            fragrance.in_wardrobe
                                ? 'bg-[var(--color-accent)]/10 text-[var(--color-accent)] border-[var(--color-accent)]/30'
                                : 'text-[var(--muted)] hover:text-[var(--color-accent)] border-[var(--hairline)]'
                        }`}
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            className="h-3.5 w-3.5 shrink-0"
                            fill={fragrance.in_wardrobe ? 'currentColor' : 'none'}
                            viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.5"
                        >
                            <path strokeLinecap="round" strokeLinejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                        <span>{fragrance.in_wardrobe ? 'In wardrobe' : 'Add to wardrobe'}</span>
                    </button>
                )}
            </div>
        </div>
    );
}
