import { Head } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../Layouts/AppLayout';
import RecommendationCard from '../Components/RecommendationCard';
import PageHeader from '../Components/PageHeader';
import Rule from '../Components/Rule';
import { motion } from 'motion/react';

const SEASON_EMOJI = { spring: '🌸', summer: '☀️', autumn: '🍂', winter: '❄️' };

/**
 * Recommend — today's pick, scored against live weather.
 *
 * Two ways in: browser geolocation, or a typed city name. Both POST to the same
 * endpoint, which fetches weather, runs the Python scoring engine over the
 * visitor's wardrobe, and returns ranked results.
 *
 * 'pickUps' are suggestions from outside the wardrobe — fragrances that suit
 * today but the visitor does not own.
 */
export default function Recommend({ urls, csrf, isGuest: initialIsGuest }) {
    const [status, setStatus] = useState('idle'); // idle | loading | done
    const [weather, setWeather] = useState(null);
    const [recommendations, setRecommendations] = useState([]);
    const [pickUps, setPickUps] = useState([]);
    const [logId, setLogId] = useState(null);
    const [chosenId, setChosenId] = useState(null);
    const [error, setError] = useState(null);
    const [city, setCity] = useState('');
    const [isGuest, setIsGuest] = useState(initialIsGuest);

    const fetchWeather = async (payload) => {
        try {
            const res = await fetch(urls.recommend, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify(payload),
            });

            if (!res.ok) {
                const data = await res.json().catch(() => ({}));
                setError(data.message || 'Something went wrong. Please try again.');
                setStatus('idle');
                return null;
            }

            const data = await res.json();
            setWeather(data.weather);
            setRecommendations(data.recommendations ?? []);
            setPickUps(data.pick_ups ?? []);
            setLogId(data.log_id);
            setIsGuest(data.guest ?? isGuest);
            setStatus('done');
            return data;
        } catch {
            setError('Could not reach the server. Please try again.');
            setStatus('idle');
            return null;
        }
    };

    const useLocation = () => {
        // The Geolocation API is gated to secure contexts, so on plain http it
        // fails with a confusing permissions error. Say so plainly instead.
        if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost') {
            setError('Location requires a secure connection (HTTPS). Please enter a city name instead.');
            return;
        }
        if (!navigator.geolocation) {
            setError('Geolocation is not supported by your browser. Please enter a city instead.');
            return;
        }

        setStatus('loading');
        setError(null);

        navigator.geolocation.getCurrentPosition(
            async (pos) => {
                const data = await fetchWeather({
                    latitude: pos.coords.latitude,
                    longitude: pos.coords.longitude,
                });
                if (data?.weather?.location) setCity(data.weather.location);
            },
            (err) => {
                setStatus('idle');
                // code 2 is POSITION_UNAVAILABLE, which is not a refusal —
                // saying "denied" there would misdescribe what happened.
                setError(
                    err.code === 2
                        ? 'Your location could not be determined. Please enter a city name instead.'
                        : 'Location access was denied. Please enter a city name instead.'
                );
            }
        );
    };

    const useCity = async () => {
        if (!city.trim()) return;
        setStatus('loading');
        setError(null);
        await fetchWeather({ city: city.trim() });
    };

    const choose = async (fragranceId) => {
        if (chosenId || !logId) return;
        setChosenId(fragranceId); // optimistic: the choice is a log write, not a gate
        await fetch(`${urls.choose}/${logId}/choose`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({ fragrance_id: fragranceId }),
        });
    };

    return (
        <AppLayout>
            <Head title="Today's Recommendation" />

            <div className="mx-auto max-w-4xl px-6 py-16">
                <PageHeader
                    eyebrow="Today's Reading"
                    accent="Weather"
                    subline="Your local conditions, scored against everything on your shelf."
                >
                    Wear The
                </PageHeader>

                {isGuest && (
                    <div className="mb-8 flex flex-wrap items-center gap-3 border-l-2 border-[var(--color-accent)] pl-4 py-1">
                        <span className="text-sm text-[var(--muted)]">
                            Sign in to keep your readings and wardrobe.
                        </span>
                        <a href={urls.register} className="btn-primary text-xs px-3.5 py-1.5">Create account</a>
                        <a href={urls.login} className="btn-ghost text-xs px-3.5 py-1.5">Sign in</a>
                    </div>
                )}

                {error && (
                    <div className="mt-6 mb-2 rounded-lg px-4 py-3 border border-[rgba(185,64,64,0.3)] bg-[rgba(185,64,64,0.08)]">
                        <p className="text-sm text-[var(--error)]">{error}</p>
                    </div>
                )}

                {status !== 'loading' && (
                    <div className="glass rounded-2xl p-6 my-8 max-w-[520px]">
                        <button onClick={useLocation} className="btn-primary w-full justify-center text-sm">
                            Use my location
                        </button>

                        <div className="flex items-center gap-3 my-4">
                            <div className="h-px flex-1 bg-[var(--hairline)]" />
                            <span className="text-xs text-[var(--muted)]">or</span>
                            <div className="h-px flex-1 bg-[var(--hairline)]" />
                        </div>

                        <div className="flex gap-2">
                            <input
                                type="text"
                                value={city}
                                onChange={(e) => setCity(e.target.value)}
                                onKeyDown={(e) => e.key === 'Enter' && useCity()}
                                placeholder="Enter a city…"
                                className="input-field flex-1"
                            />
                            <button onClick={useCity} disabled={!city.trim()} className="btn-ghost text-sm px-4 disabled:opacity-50">
                                Go
                            </button>
                        </div>
                    </div>
                )}

                {status === 'loading' && (
                    <div className="my-8 flex flex-col gap-4">
                        <div className="glass rounded-2xl h-28 animate-pulse" />
                        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            {[0, 1, 2].map((i) => (
                                <div key={i} className="neu-raised rounded-2xl h-64 animate-pulse" />
                            ))}
                        </div>
                    </div>
                )}

                {status === 'done' && (
                    <>
                        {weather && (
                            <motion.div
                                initial={{ opacity: 0, y: 10 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.4, ease: [0.22, 1, 0.36, 1] }}
                                className="mb-12 border-y border-[var(--hairline)] py-8"
                            >
                                {/* Set as a masthead rather than a card: the temperature is
                                    the largest thing on the page because it is the input the
                                    whole ranking below derives from. */}
                                <p className="font-mono text-[11px] uppercase tracking-[0.2em] text-[var(--color-accent)]">
                                    {weather.location}
                                </p>

                                <div className="mt-3 flex items-end gap-8 flex-wrap">
                                    <p className="font-display text-7xl sm:text-8xl font-light leading-[0.85] text-[var(--ink)]">
                                        {Math.round(weather.temperature_celsius)}
                                        <span className="text-3xl align-super">°</span>
                                    </p>

                                    <dl className="grid grid-cols-2 gap-x-10 gap-y-2 pb-2 font-mono text-[11px] uppercase tracking-[0.12em]">
                                        <dt className="text-[var(--muted)]">Sky</dt>
                                        <dd className="text-[var(--ink)]">{weather.condition}</dd>
                                        <dt className="text-[var(--muted)]">Humidity</dt>
                                        <dd className="text-[var(--ink)]">{weather.humidity_percent}%</dd>
                                        <dt className="text-[var(--muted)]">Season</dt>
                                        <dd className="text-[var(--ink)] capitalize">
                                            {SEASON_EMOJI[(weather.season || '').toLowerCase()] ?? ''} {weather.season}
                                        </dd>
                                    </dl>
                                </div>
                            </motion.div>
                        )}

                        {recommendations.length === 0 ? (
                            <div className="neu-raised rounded-2xl p-8 text-center">
                                <p className="font-display text-xl font-light text-[var(--ink)] mb-2">
                                    Nothing to score yet
                                </p>
                                <p className="text-sm text-[var(--muted)] mb-6">
                                    Add fragrances to your wardrobe and we can pick one for today.
                                </p>
                                <a href={urls.browse} className="btn-primary inline-flex">Browse Fragrances</a>
                            </div>
                        ) : (
                            <>
                            <Rule label="From your wardrobe">ranked by fit</Rule>
                            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                {recommendations.map((rec) => (
                                    <RecommendationCard
                                        key={rec.fragrance_id}
                                        rec={rec}
                                        detailUrl={`${urls.fragrance}/${rec.fragrance_id}`}
                                        chosen={chosenId === rec.fragrance_id}
                                        anyChosen={chosenId !== null}
                                        onChoose={isGuest ? null : choose}
                                    />
                                ))}
                            </div>
                            </>
                        )}

                        {pickUps.length > 0 && (
                            <div className="mt-14">
                                <Rule label="Pick-ups">not yours yet</Rule>
                                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    {pickUps.map((rec) => (
                                        <RecommendationCard
                                            key={rec.fragrance_id}
                                            rec={rec}
                                            detailUrl={`${urls.fragrance}/${rec.fragrance_id}`}
                                        />
                                    ))}
                                </div>
                            </div>
                        )}
                    </>
                )}
            </div>
        </AppLayout>
    );
}
