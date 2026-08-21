import { Head } from '@inertiajs/react';
import { useCallback, useEffect, useRef, useState } from 'react';
import AppLayout from '../Layouts/AppLayout';
import FragranceCard from '../Components/FragranceCard';
import FragranceIndex from '../Components/FragranceIndex';
import PageHeader from '../Components/PageHeader';
import ViewToggle from '../Components/ViewToggle';
import AnimatedNumber from '../Components/AnimatedNumber';
import Pagination from '../Components/Pagination';
import SearchBar from '../Components/SearchBar';

/**
 * Browse — the fragrance catalog.
 *
 * Search, gender filter and pagination all hit /browse/search and swap the grid
 * without a full page load. The Alpine version did the same thing, but had to
 * hand-roll debouncing, request cancellation and the loading-flash guard inside
 * an x-data blob; here each of those is a small hook with a clear lifetime.
 */
export default function Browse({ initialResults, filters, pagination, total, urls }) {
    const [results, setResults] = useState(initialResults);
    const [search, setSearch] = useState(filters.search ?? '');
    const [gender, setGender] = useState(filters.gender ?? 'all');
    const [page, setPage] = useState(pagination.current_page);
    const [lastPage, setLastPage] = useState(pagination.last_page);
    const [count, setCount] = useState(total);
    const [loading, setLoading] = useState(false);

    // Tiles are the default. People recognise fragrances by the bottle before
    // they read the name, so the image is the most useful thing a catalogue row
    // can lead with. The index view remains available from the toggle for
    // scanning names and houses quickly.
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

    // Cancels the previous request the moment a new one starts, so a slow
    // response for "ro" can never land after the fast one for "rose".
    const abortRef = useRef(null);
    // Delays the loading state so responses under 150ms never flash a spinner.
    const loadingTimerRef = useRef(null);
    // Skips the fetch on first render — the server already sent page 1.
    const isFirstRender = useRef(true);

    const doSearch = useCallback(async (term, genderValue, targetPage) => {
        abortRef.current?.abort();
        clearTimeout(loadingTimerRef.current);

        const controller = new AbortController();
        abortRef.current = controller;
        loadingTimerRef.current = setTimeout(() => setLoading(true), 150);

        const params = new URLSearchParams({
            q: term,
            gender: genderValue,
            page: String(targetPage),
        });

        try {
            const res = await fetch(`${urls.search}?${params}`, {
                signal: controller.signal,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!res.ok) {
                throw new Error(`Search failed: ${res.status}`);
            }

            const data = await res.json();

            // Guard against a malformed payload blanking the grid. The Alpine
            // version assigned data.fragrances unconditionally, so when the
            // endpoint 500'd it set results to undefined and the page threw
            // "Cannot read properties of undefined (reading 'length')".
            setResults(Array.isArray(data.fragrances) ? data.fragrances : []);
            setCount(data.total ?? 0);
            setPage(data.current_page ?? 1);
            setLastPage(data.last_page ?? 1);
        } catch (err) {
            if (err.name === 'AbortError') return; // superseded, not a failure
            console.error(err);
        } finally {
            clearTimeout(loadingTimerRef.current);
            setLoading(false);
            abortRef.current = null;
        }
    }, [urls.search]);

    // Debounced search on keystrokes.
    useEffect(() => {
        if (isFirstRender.current) return;
        const timer = setTimeout(() => doSearch(search, gender, 1), 250);
        return () => clearTimeout(timer);
    }, [search, doSearch, gender]);

    // Gender changes are a click, not typing — no debounce needed.
    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return;
        }
        doSearch(search, gender, 1);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [gender]);

    // Abort any in-flight request if the user navigates away mid-search.
    useEffect(() => () => abortRef.current?.abort(), []);

    const goToPage = (target) => {
        if (loading || target < 1 || target > lastPage || target === page) return;
        doSearch(search, gender, target);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const toggleWardrobe = async (id) => {
        const res = await fetch(`${urls.wardrobeToggle}/${id}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            },
        });
        const data = await res.json();

        setResults((current) =>
            current.map((f) => (f.id === id ? { ...f, in_wardrobe: data.in_wardrobe } : f))
        );
    };

    return (
        <AppLayout>
            <Head title="Browse Fragrances" />

            <div className="mx-auto max-w-5xl px-6 py-16">
                <PageHeader
                    eyebrow="The Catalogue"
                    accent="Fragrance"
                    subline={<><AnimatedNumber value={count} /> entries, indexed by house and composition.</>}
                    actions={<ViewToggle view={view} onChange={changeView} />}
                >
                    Browse Every
                </PageHeader>

                <div className="mb-8">
                    <SearchBar
                        search={search}
                        onSearchChange={setSearch}
                        gender={gender}
                        onGenderChange={setGender}
                        loading={loading}
                    />
                </div>

                <div className={`relative min-h-[28rem] transition-opacity duration-200 ${loading ? 'opacity-50' : 'opacity-100'}`}>
                    {results.length === 0 && !loading ? (
                        <div className="py-16 text-center text-[var(--muted)]">
                            No fragrances found{search ? ` for "${search}"` : ''}.
                        </div>
                    ) : view === 'index' ? (
                        <FragranceIndex
                            results={results}
                            // Continue the numbering across pages so an entry's
                            // number reflects its place in the whole catalogue.
                            startIndex={(page - 1) * 24}
                            urls={urls}
                            onToggleWardrobe={toggleWardrobe}
                        />
                    ) : (
                        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                            {results.map((fragrance) => (
                                <FragranceCard
                                    key={fragrance.id}
                                    fragrance={fragrance}
                                    detailUrl={`${urls.fragrance}/${fragrance.id}`}
                                    onToggleWardrobe={toggleWardrobe}
                                />
                            ))}
                        </div>
                    )}
                </div>

                <Pagination
                    currentPage={page}
                    lastPage={lastPage}
                    loading={loading}
                    onNavigate={goToPage}
                />
            </div>
        </AppLayout>
    );
}
