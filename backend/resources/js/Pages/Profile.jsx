import { Head, useForm } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import PageHeader from '../Components/PageHeader';
import Rule from '../Components/Rule';

const GENDERS = [
    ['', 'Prefer not to say'],
    ['masculine', 'Masculine'],
    ['feminine', 'Feminine'],
    ['unisex', 'No preference'],
];

/**
 * Profile — account settings, set as a record rather than a form card.
 *
 * Fields sit in labelled rows separated by hairlines, matching the index and
 * detail pages. A bordered form panel is the shape every settings page defaults
 * to, and it would be the one screen still reading as a dashboard.
 *
 * Uses Inertia's useForm: it tracks dirty state, maps server-side validation
 * errors back onto the right fields, and exposes `processing` for the submit
 * button. That is the whole reason to render a form through Inertia rather than
 * post it over XHR.
 */
export default function Profile({ user, urls, timezones }) {
    const { data, setData, patch, processing, errors, isDirty, recentlySuccessful } = useForm({
        name: user.name ?? '',
        gender: user.gender ?? '',
        timezone: user.timezone ?? 'UTC',
    });

    const submit = (e) => {
        e.preventDefault();
        patch(urls.update, { preserveScroll: true });
    };

    return (
        <AppLayout>
            <Head title="Profile" />

            <div className="mx-auto max-w-2xl px-6 py-16">
                <PageHeader
                    eyebrow="Your Account"
                    accent="Details"
                    subline="Your timezone decides which season the engine scores against."
                >
                    The
                </PageHeader>

                <form onSubmit={submit}>
                    <Rule label="Identity" />

                    <Row label="Email" hint="Cannot be changed here.">
                        <input
                            type="email"
                            value={user.email}
                            disabled
                            className="input-field w-full opacity-60 cursor-not-allowed"
                        />
                    </Row>

                    <Row label="Name" error={errors.name}>
                        <input
                            type="text"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            className="input-field w-full"
                        />
                    </Row>

                    <div className="mt-12">
                        <Rule label="Preferences" />
                    </div>

                    <Row label="Fragrance" error={errors.gender} hint="Filters what gets recommended.">
                        <select
                            value={data.gender}
                            onChange={(e) => setData('gender', e.target.value)}
                            className="input-field w-full"
                        >
                            {GENDERS.map(([value, label]) => (
                                <option key={value} value={value}>{label}</option>
                            ))}
                        </select>
                    </Row>

                    <Row label="Timezone" error={errors.timezone} hint="Sets your hemisphere, and so your season.">
                        <select
                            value={data.timezone}
                            onChange={(e) => setData('timezone', e.target.value)}
                            className="input-field w-full"
                        >
                            {timezones.map((tz) => (
                                <option key={tz} value={tz}>{tz}</option>
                            ))}
                        </select>
                    </Row>

                    <div className="mt-10 flex items-center gap-4">
                        <button
                            type="submit"
                            disabled={processing || !isDirty}
                            className="btn-primary text-sm disabled:opacity-40 disabled:cursor-not-allowed"
                        >
                            {processing ? 'Saving…' : 'Save changes'}
                        </button>

                        {recentlySuccessful && (
                            <span className="font-mono text-[11px] uppercase tracking-[0.15em] text-[var(--color-accent)]">
                                Saved
                            </span>
                        )}
                        {isDirty && !processing && !recentlySuccessful && (
                            <span className="font-mono text-[11px] uppercase tracking-[0.15em] text-[var(--muted)]">
                                Unsaved changes
                            </span>
                        )}
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}

/**
 * One labelled setting. Label sits in a fixed left column on wide screens and
 * stacks above the control on narrow ones.
 */
function Row({ label, hint, error, children }) {
    return (
        <div className="grid grid-cols-1 sm:grid-cols-[8rem_1fr] gap-2 sm:gap-6 py-5 border-b border-[var(--hairline)]">
            <div className="pt-2.5">
                <label className="font-mono text-[11px] uppercase tracking-[0.15em] text-[var(--ink)]">
                    {label}
                </label>
            </div>

            <div className="min-w-0">
                {children}
                {hint && !error && <p className="mt-1.5 text-xs text-[var(--muted)]">{hint}</p>}
                {error && <p className="mt-1.5 text-xs text-[var(--error)]">{error}</p>}
            </div>
        </div>
    );
}
