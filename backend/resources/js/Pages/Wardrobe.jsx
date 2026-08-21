import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../Layouts/AppLayout';
import FragranceCard from '../Components/FragranceCard';

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

            <div className="mx-auto max-w-6xl px-6 py-12">
                {isGuest && (
                    <div className="mb-6 glass rounded-2xl p-4 text-sm text-[var(--muted)]">
                        This is your temporary wardrobe for this browser session. It will be merged
                        into your account if you sign in or register before the session ends.
                    </div>
                )}

                <div className="mb-8 flex items-center justify-between">
                    <div>
                        <h1 className="font-display text-4xl font-light text-[var(--ink)]">My Wardrobe</h1>
                        <p className="mt-2 text-sm text-[var(--muted)]">
                            {pagination.total} {pagination.total === 1 ? 'fragrance' : 'fragrances'} in your collection.
                        </p>
                    </div>
                </div>

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
                                <p className="text-xs text-[var(--muted)] uppercase tracking-widest mb-3">
                                    Favourites
                                </p>
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
