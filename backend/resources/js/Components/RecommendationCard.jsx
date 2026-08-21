import { useState } from 'react';

/**
 * RecommendationCard — one scored fragrance from the weather engine.
 *
 * Shows the composite score, the notes that drove it, and the engine's
 * reasoning string. `onChoose` is omitted for guests and for pick-ups, since
 * neither can be logged against a wardrobe.
 */
export default function RecommendationCard({ rec, detailUrl, chosen, anyChosen, onChoose }) {
    const [imageFailed, setImageFailed] = useState(false);
    const showImage = rec.image_url && !imageFailed;
    const pct = Math.round((rec.score ?? 0) * 100);

    return (
        <div className={`neu-raised rounded-2xl overflow-hidden flex flex-col ${chosen ? 'ring-2 ring-[var(--color-accent)]' : ''}`}>
            <a href={detailUrl} className="block aspect-square bg-[var(--shadow-dark)]/30 overflow-hidden">
                {showImage ? (
                    <img
                        src={rec.image_url}
                        alt={rec.name}
                        loading="lazy"
                        onError={() => setImageFailed(true)}
                        className="w-full h-full object-contain p-4"
                    />
                ) : (
                    <div className="w-full h-full flex items-center justify-center text-[var(--muted)]">
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-10 w-10 opacity-25" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z" />
                        </svg>
                    </div>
                )}
            </a>

            <div className="p-4 flex flex-col flex-1">
                <p className="text-xs text-[var(--muted)] truncate">{rec.brand}</p>
                <a href={detailUrl} className="text-sm font-medium text-[var(--ink)] hover:text-[var(--color-accent)] transition-colors truncate">
                    {rec.name}
                </a>

                <div className="mt-3">
                    <div className="flex justify-between text-xs mb-1">
                        <span className="text-[var(--muted)]">Match</span>
                        <span className="text-[var(--ink)]">{pct}%</span>
                    </div>
                    <div className="h-1.5 w-full rounded-full bg-[var(--shadow-dark)] overflow-hidden">
                        <div className="h-full rounded-full bg-[var(--color-accent)]" style={{ width: `${pct}%` }} />
                    </div>
                </div>

                {rec.matched_notes?.length > 0 && (
                    <div className="mt-3 flex flex-wrap gap-1">
                        {rec.matched_notes.slice(0, 4).map((note) => (
                            <span key={note} className="text-[0.65rem] px-[7px] py-px rounded-full border border-[var(--hairline)] text-[var(--muted)]">
                                {note}
                            </span>
                        ))}
                    </div>
                )}

                {rec.reasoning && (
                    <p className="mt-3 text-xs text-[var(--muted)] leading-relaxed flex-1">{rec.reasoning}</p>
                )}

                {onChoose && (
                    <button
                        onClick={() => onChoose(rec.fragrance_id)}
                        disabled={anyChosen}
                        className={`mt-3 w-full rounded-lg border py-1.5 text-xs transition-colors ${
                            chosen
                                ? 'bg-[var(--color-accent)]/10 text-[var(--color-accent)] border-[var(--color-accent)]/30'
                                : 'text-[var(--muted)] hover:text-[var(--color-accent)] border-[var(--hairline)] disabled:opacity-40'
                        }`}
                    >
                        {chosen ? 'Wearing this ✓' : "I'm wearing this"}
                    </button>
                )}
            </div>
        </div>
    );
}
