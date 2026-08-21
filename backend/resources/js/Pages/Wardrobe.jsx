import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../Layouts/AppLayout';
import FragranceCard from '../Components/FragranceCard';
import FragranceIndex from '../Components/FragranceIndex';
import PageHeader from '../Components/PageHeader';
import ViewToggle from '../Components/ViewToggle';
import Rule from '../Components/Rule';
import AnimatedNumber from '../Components/AnimatedNumber';

/**
 * Wardrobe — the fragrances a visitor owns.
 *
 * Backed by one of two stores depending on who is asking: a signed-in user's
 * UserCollection rows, or a guest's session bag. The controller flattens both
 * into the same shape, so this component never has to know which it is looking
 * at — only whether to show the guest banner.
 */
export default function Wardrobe({ favorites, collection, pagination, isGuest, urls, csrf }) {
    const [items, setItems] = useState(collection);
    const [favs, setFavs] = useState(favorites);

    // Shares the key Browse writes, so a visitor who picks a view on one page
    // gets it on the other. Two catalogues that disagree about how to present
    // the same objects is exactly the kind of drift the shared components exist
    // to prevent. Defaults to tiles, matching Browse.
    const [view, setView] = useState(() => {
        try {
            return localStorage.getItem('pdc_browse_view') || 'grid';
        } catch {
            return 'grid';
        }
    });

    const changeView = (next) => {
        setView(next);
        try {
            localStorage.setItem('pdc_browse_view', next);
        } catch {
            // localStorage blocked — the choice just will not persist.
        }
    };

    const removeFromWardrobe = async (id) => {
        const res = await fetch(`${urls.wardrobe}/${id}/toggle`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        });
        const data = await res.json();

        if (!data.in_wardrobe) {
            // Drop it from both lists rather than reloading — the favourites
            // strip is a subset of the collection, so a removal affects both.
            setItems((current) => current.filter((f) => f.id !== id));
            setFavs((current) => current.filter((f) => f.id !== id));
        }
    };

    const goToPage = (page) => {
        // A full Inertia visit rather than a fetch: the wardrobe has no search
        // to preserve, so letting the URL carry the page keeps it linkable and
        // makes the browser back button work.
        router.get(urls.wardrobe, { page }, { preserveScroll: false, preserveState: false });
    };

    return (
        <AppLayout>
            <Head title="My Wardrobe" />

            <div className="mx-auto max-w-5xl px-6 py-16">
                {isGuest && (
                    <div className="mb-8 flex items-start gap-3 border-l-2 border-[var(--color-accent)] pl-4 py-1">
                        <p className="text-sm text-[var(--muted)]">
                            A temporary wardrobe for this browser session. It merges into your
                            account if you sign in or register before the session ends.
                        </p>
                    </div>
                )}

                <PageHeader
                    eyebrow="Your Collection"
                    accent="Wardrobe"
                    subline={
                        <>
                            <AnimatedNumber value={pagination.total} />{' '}
                            {pagination.total === 1 ? 'fragrance' : 'fragrances'} on the shelf.
                        </>
                    }
                    actions={items.length > 0 ? <ViewToggle view={view} onChange={changeView} /> : null}
                >
                    The
                </PageHeader>

                {items.length === 0 ? (
                    <div className="neu-raised rounded-2xl p-12 text-center">
                        <p className="font-display text-2xl font-light text-[var(--ink)] mb-3">
                            Your wardrobe is empty
                        </p>
                        <p className="text-sm text-[var(--muted)] mb-6">
                            Browse the catalog and add fragrances you own.
                        </p>
                        <a href={urls.browse} className="btn-primary inline-flex">Browse Fragrances</a>
                    </div>
                ) : (
                    <>
                        {favs.length > 0 && (
                            <div className="mb-10">
                                <Rule label="Favourites" />
                                <div className="flex gap-4 overflow-x-auto pb-2">
                                    {favs.map((f) => (
                                        <a
                                            key={f.id}
                                            href={`${urls.fragrance}/${f.id}`}
                                            className="neu-raised rounded-2xl overflow-hidden shrink-0 w-36 group"
                                        >
                                            <div className="aspect-square bg-[var(--shadow-dark)]/30 flex items-center justify-center">
                                                {f.image_url ? (
                                                    <img
                                                        src={f.image_url}
                                                        alt={f.name}
                                                        loading="lazy"
                                                        className="w-full h-full object-contain p-3 group-hover:scale-105 transition-transform duration-300"
                                                    />
                                                ) : (
                                                    <span className="text-[var(--muted)] opacity-25 text-xs">No image</span>
                                                )}
                                            </div>
                                            <div className="p-2.5">
                                                <p className="text-[11px] text-[var(--muted)] truncate">{f.brand}</p>
                                                <p className="text-xs font-medium text-[var(--ink)] truncate">{f.name}</p>
                                            </div>
                                        </a>
                                    ))}
                                </div>
                            </div>
                        )}

                        <Rule label="Collection" />

                        {view === 'index' ? (
                            <FragranceIndex
                                results={items.map((f) => ({ ...f, in_wardrobe: true }))}
                                startIndex={(pagination.current_page - 1) * 16}
                                urls={urls}
                                onToggleWardrobe={removeFromWardrobe}
                            />
                        ) : (
                            <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                                {items.map((f) => (
                                    <FragranceCard
                                        key={f.id}
                                        fragrance={{ ...f, in_wardrobe: true }}
                                        detailUrl={`${urls.fragrance}/${f.id}`}
                                        onToggleWardrobe={removeFromWardrobe}
                                    />
                                ))}
                            </div>
                        )}

                        {pagination.last_page > 1 && (
                            <div className="mt-8 flex items-center justify-center gap-2">
                                <button
                                    onClick={() => goToPage(pagination.current_page - 1)}
                                    disabled={pagination.current_page <= 1}
                                    className="btn-ghost px-3 py-2 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Prev
                                </button>
                                <span className="px-3 py-2 text-sm text-[var(--muted)]">
                                    Page {pagination.current_page} of {pagination.last_page}
                                </span>
                                <button
                                    onClick={() => goToPage(pagination.current_page + 1)}
                                    disabled={pagination.current_page >= pagination.last_page}
                                    className="btn-ghost px-3 py-2 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Next
                                </button>
                            </div>
                        )}
                    </>
                )}
            </div>
        </AppLayout>
    );
}
