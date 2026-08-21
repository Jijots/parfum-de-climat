import { Head } from '@inertiajs/react';
import { motion } from 'motion/react';
import { useState } from 'react';
import AppLayout from '../Layouts/AppLayout';
import FragranceCard from '../Components/FragranceCard';
import StatBar from '../Components/StatBar';
import Rule from '../Components/Rule';

const GENDER_LABELS = { masculine: 'Men', feminine: 'Women', unisex: 'Unisex' };

const LAYERS = [
    ['top', 'Top', 'first impression, 0–30 min'],
    ['heart', 'Heart', 'the core, 30 min – 4 hrs'],
    ['base', 'Base', 'dry-down, 4 hrs onward'],
];

/**
 * FragranceDetail — one fragrance, read as a catalogue entry.
 *
 * Laid out as an asymmetric editorial split rather than a stack of cards: the
 * bottle and its actions hold a sticky left rail, the composition runs down the
 * right. The notes pyramid is set as typography — layer name, timing, then the
 * notes themselves at display size — because the pyramid IS the subject of the
 * page, and burying it in chips understates it.
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
    const hasNotes = item.notes.top.length || item.notes.heart.length || item.notes.base.length;

    return (
        <AppLayout>
            <Head title={`${item.name} by ${item.brand}`} />

            <div className="mx-auto max-w-5xl px-6 py-16">
                <a
                    href={urls.browse}
                    className="inline-flex items-center gap-1.5 font-mono text-[11px] uppercase tracking-[0.2em] text-[var(--muted)] hover:text-[var(--color-accent)] transition-colors mb-10"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.5">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    The Catalogue
                </a>

                <div className="grid grid-cols-1 lg:grid-cols-[minmax(0,18rem)_1fr] gap-10 lg:gap-14">
                    {/* ── Left rail: bottle + actions, sticky on wide screens ── */}
                    <div className="lg:sticky lg:top-28 lg:self-start">
                        <motion.div
                            initial={{ opacity: 0, y: 12 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.4, ease: [0.22, 1, 0.36, 1] }}
                            className="aspect-square rounded-2xl overflow-hidden bg-[var(--panel)] border border-[var(--hairline)]"
                        >
                            {showImage ? (
                                <img
                                    src={item.image_url}
                                    alt={item.name}
                                    onError={() => setImageFailed(true)}
                                    className="w-full h-full object-contain p-8"
                                />
                            ) : (
                                <div className="w-full h-full flex items-center justify-center text-[var(--muted)] opacity-25">
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-14 w-14" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z" />
                                    </svg>
                                </div>
                            )}
                        </motion.div>

                        <div className="mt-5 flex gap-2">
                            <button
                                onClick={toggleWardrobe}
                                disabled={busy}
                                className={`flex-1 text-sm py-2.5 ${inWardrobe ? 'btn-ghost' : 'btn-primary'} justify-center`}
                            >
                                {inWardrobe ? 'In wardrobe ✓' : 'Add to wardrobe'}
                            </button>

                            {inWardrobe && (
                                <button
                                    onClick={toggleFavorite}
                                    title="Toggle favourite"
                                    aria-pressed={isFavorite}
                                    className={`p-2.5 rounded-full border transition-colors ${
                                        isFavorite
                                            ? 'text-[var(--color-accent)] border-[var(--color-accent-border)] bg-[var(--color-accent-dim)]'
                                            : 'text-[var(--muted)] border-[var(--hairline)] hover:text-[var(--color-accent)]'
                                    }`}
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill={isFavorite ? 'currentColor' : 'none'} viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.5">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                    </svg>
                                </button>
                            )}
                        </div>

                        {(item.rating || item.sillage || item.longevity) && (
                            <div className="mt-8">
                                <Rule label="Performance" />
                                {item.rating && <StatBar label="Rating" value={item.rating} max={5} />}
                                {item.longevity && <StatBar label="Longevity" value={item.longevity} max={10} />}
                                {item.sillage && <StatBar label="Sillage" value={item.sillage} max={10} />}
                            </div>
                        )}
                    </div>

                    {/* ── Right column: the entry itself ─────────────────────── */}
                    <div className="min-w-0">
                        <p className="font-mono text-[11px] uppercase tracking-[0.2em] text-[var(--color-accent)] mb-3">
                            {item.brand}
                        </p>
                        <h1 className="font-display text-4xl sm:text-6xl font-light leading-[1.05] text-[var(--ink)]">
                            {item.name}
                        </h1>

                        <div className="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 font-mono text-[11px] uppercase tracking-[0.15em] text-[var(--muted)]">
                            {item.release_year && <span>{item.release_year}</span>}
                            {item.concentration && <span>{item.concentration}</span>}
                            <span>{GENDER_LABELS[item.gender_target] ?? 'Unisex'}</span>
                        </div>

                        {item.description && (
                            <p className="mt-8 text-[15px] leading-relaxed text-[var(--muted)] max-w-prose">
                                {item.description}
                            </p>
                        )}

                        {hasNotes ? (
                            <div className="mt-12">
                                <Rule label="Composition" />

                                <div className="space-y-8">
                                    {LAYERS.map(([key, label, timing]) =>
                                        item.notes[key].length > 0 ? (
                                            <div key={key} className="grid grid-cols-1 sm:grid-cols-[7rem_1fr] gap-2 sm:gap-6">
                                                <div className="pt-1">
                                                    <p className="font-mono text-[11px] uppercase tracking-[0.15em] text-[var(--ink)]">
                                                        {label}
                                                    </p>
                                                    <p className="text-[11px] text-[var(--muted)] mt-0.5">{timing}</p>
                                                </div>

                                                {/* Notes set at reading size, comma-separated, rather
                                                    than as chips. The pyramid is the substance of the
                                                    entry; chips make it look like metadata. */}
                                                <p className="font-display text-xl sm:text-2xl font-light leading-snug text-[var(--ink)]">
                                                    {item.notes[key].join(', ')}
                                                </p>
                                            </div>
                                        ) : null
                                    )}
                                </div>
                            </div>
                        ) : (
                            <p className="mt-12 text-sm text-[var(--muted)]">
                                No note breakdown recorded for this entry.
                            </p>
                        )}

                        {item.accords.length > 0 && (
                            <div className="mt-12">
                                <Rule label="Accords" />
                                <div className="space-y-2">
                                    {item.accords.map((a) => (
                                        <StatBar key={a.accord} label={a.accord} value={a.strength} max={1} />
                                    ))}
                                </div>
                            </div>
                        )}

                        {item.external_source_url && (
                            <a
                                href={item.external_source_url}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="mt-10 inline-flex font-mono text-[11px] uppercase tracking-[0.2em] text-[var(--muted)] hover:text-[var(--color-accent)] transition-colors"
                            >
                                View on Fragrantica ↗
                            </a>
                        )}
                    </div>
                </div>

                {similar.length > 0 && (
                    <div className="mt-20">
                        <Rule label="Smells like this">matched on shared notes</Rule>

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
