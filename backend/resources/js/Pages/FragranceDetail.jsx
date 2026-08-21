import { Head } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../Layouts/AppLayout';
import FragranceCard from '../Components/FragranceCard';
import StatBar from '../Components/StatBar';

const GENDER_LABELS = { masculine: 'Men', feminine: 'Women', unisex: 'Unisex' };

/**
 * FragranceDetail — one fragrance, plus its "smells like this" neighbours.
 *
 * The similar list comes from the pre-computed TF-IDF index; nothing is scored
 * at request time. See FragranceController::similarFragrances().
 */
export default function FragranceDetail({ item, similar, wardrobe, urls, csrf }) {
    const [inWardrobe, setInWardrobe] = useState(wardrobe.in_wardrobe);
    const [isFavorite, setIsFavorite] = useState(wardrobe.is_favorite);
    const [busy, setBusy] = useState(false);
    const [imageFailed, setImageFailed] = useState(false);

    const post = (url, method = 'POST') =>
        fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        }).then((r) => r.json());

    const toggleWardrobe = async () => {
        setBusy(true);
        try {
            const data = await post(urls.toggle);
            setInWardrobe(data.in_wardrobe);
            // Favouriting something no longer in the wardrobe is meaningless,
            // and the server clears it too — mirror that here.
            if (!data.in_wardrobe) setIsFavorite(false);
        } finally {
            setBusy(false);
        }
    };

    const toggleFavorite = async () => {
        const data = await post(urls.favorite, 'PATCH');
        setIsFavorite(data.is_favorite);
    };

    const showImage = item.image_url && !imageFailed;

    return (
        <AppLayout>
            <Head title={`${item.name} by ${item.brand}`} />

            <div className="mx-auto max-w-2xl px-6 py-12">
                <a
                    href={urls.browse}
                    className="inline-flex items-center gap-1.5 text-sm text-[var(--muted)] hover:text-[var(--ink)] transition-colors mb-8"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.5">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Back
                </a>

                {/* Hero */}
                <div className="glass rounded-2xl p-8 mb-8 flex flex-col sm:flex-row gap-6 items-center sm:items-start">
                    <div className="w-32 h-32 shrink-0 bg-[var(--shadow-dark)]/20 rounded-xl overflow-hidden">
                        {showImage ? (
                            <img
                                src={item.image_url}
                                alt={item.name}
                                onError={() => setImageFailed(true)}
                                className="w-full h-full object-contain p-2"
                            />
                        ) : (
                            <div className="w-full h-full flex items-center justify-center text-[var(--muted)] opacity-25">
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z" />
                                </svg>
                            </div>
                        )}
                    </div>

                    <div className="text-center sm:text-left flex-1">
                        <p className="text-sm text-[var(--muted)]">{item.brand}</p>
                        <h1 className="font-display text-3xl font-light text-[var(--ink)] mt-1">{item.name}</h1>

                        <div className="flex flex-wrap gap-2 mt-3 justify-center sm:justify-start">
                            {item.release_year && <Chip>{item.release_year}</Chip>}
                            {item.concentration && <Chip>{item.concentration}</Chip>}
                            <Chip>{GENDER_LABELS[item.gender_target] ?? 'Unisex'}</Chip>
                        </div>

                        <div className="mt-4 flex gap-2 justify-center sm:justify-start">
                            <button
                                onClick={toggleWardrobe}
                                disabled={busy}
                                className={`text-sm py-1.5 px-4 rounded-lg ${inWardrobe ? 'btn-ghost' : 'btn-primary'}`}
                            >
                                {inWardrobe ? 'In wardrobe ✓' : 'Add to wardrobe'}
                            </button>

                            {inWardrobe && (
                                <button
                                    onClick={toggleFavorite}
                                    title="Toggle favorite"
                                    className={`p-2 transition-colors rounded-lg border border-[var(--hairline)] ${
                                        isFavorite
                                            ? 'text-[var(--color-accent)]'
                                            : 'text-[var(--muted)] hover:text-[var(--color-accent)]'
                                    }`}
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg" className="h-5 w-5"
                                        fill={isFavorite ? 'currentColor' : 'none'}
                                        viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.5"
                                    >
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                    </svg>
                                </button>
                            )}
                        </div>
                    </div>
                </div>

                {item.description && (
                    <div className="mb-8">
                        <p className="text-sm text-[var(--muted)] leading-relaxed">{item.description}</p>
                    </div>
                )}

                {(item.rating || item.sillage || item.longevity) && (
                    <Panel title="Performance">
                        {item.rating && <StatBar label="Rating" value={item.rating} max={5} />}
                        {item.longevity && <StatBar label="Longevity" value={item.longevity} max={10} />}
                        {item.sillage && <StatBar label="Sillage" value={item.sillage} max={10} />}
                    </Panel>
                )}

                {(item.notes.top.length > 0 || item.notes.heart.length > 0 || item.notes.base.length > 0) && (
                    <Panel title="Notes">
                        {[['Top', item.notes.top], ['Heart', item.notes.heart], ['Base', item.notes.base]].map(
                            ([layer, names]) =>
                                names.length > 0 && (
                                    <div key={layer} className="mb-4 last:mb-0">
                                        <p className="text-xs text-[var(--muted)] mb-2">{layer}</p>
                                        <div className="flex flex-wrap gap-1.5">
                                            {names.map((n) => (
                                                <span
                                                    key={n}
                                                    className="text-xs px-2.5 py-1 rounded-full border border-[var(--hairline)] text-[var(--ink)]"
                                                >
                                                    {n}
                                                </span>
                                            ))}
                                        </div>
                                    </div>
                                )
                        )}
                    </Panel>
                )}

                {item.accords.length > 0 && (
                    <Panel title="Accords">
                        <div className="space-y-2">
                            {item.accords.map((a) => (
                                <StatBar key={a.accord} label={a.accord} value={a.strength} max={1} />
                            ))}
                        </div>
                    </Panel>
                )}

                {item.external_source_url && (
                    <a
                        href={item.external_source_url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="btn-ghost w-full justify-center text-sm mt-2"
                    >
                        View on Fragrantica ↗
                    </a>
                )}

                {similar.length > 0 && (
                    <div className="mt-10">
                        <div className="flex items-baseline justify-between mb-4">
                            <h2 className="font-display text-2xl font-light text-[var(--ink)]">Smells like this</h2>
                            <span className="text-xs text-[var(--muted)]">matched on shared notes</span>
                        </div>

                        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                            {similar.map((s) => (
                                <FragranceCard
                                    key={s.id}
                                    fragrance={s}
                                    detailUrl={`${urls.fragrance}/${s.id}`}
                                    match={s.match}
                                />
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}

function Chip({ children }) {
    return (
        <span className="text-xs px-2 py-0.5 rounded-full border border-[var(--hairline)] text-[var(--muted)]">
            {children}
        </span>
    );
}

function Panel({ title, children }) {
    return (
        <div className="neu-raised rounded-2xl p-6 mb-6">
            <p className="text-xs text-[var(--muted)] uppercase tracking-widest mb-4">{title}</p>
            {children}
        </div>
    );
}
