import { Head, useForm } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';

const GENDERS = [
    ['', 'Prefer not to say'],
    ['masculine', 'Masculine'],
    ['feminine', 'Feminine'],
    ['unisex', 'No preference'],
];

/**
 * Profile — account settings.
 *
 * Uses Inertia's useForm rather than a hand-rolled fetch: it tracks dirty
 * state, wires server-side validation errors back onto the right fields, and
 * exposes `processing` so the submit button can disable itself. That is the
 * whole reason to use Inertia for a form page instead of an XHR endpoint.
 */
export default function Profile({ user, urls, timezones }) {
    const { data, setData, patch, processing, errors, recentlySuccessful } = useForm({
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

            <div className="mx-auto max-w-xl px-6 py-12">
                <h1 className="font-display text-4xl font-light text-[var(--ink)]">Profile</h1>
                <p className="mt-2 text-sm text-[var(--muted)]">
                    Your timezone decides which season the engine scores against.
                </p>

                <form onSubmit={submit} className="neu-raised rounded-2xl p-6 mt-8 space-y-5">
                    <Field label="Email">
                        <input
                            type="email"
                            value={user.email}
                            disabled
                            className="input-field w-full opacity-60 cursor-not-allowed"
                        />
                        <p className="mt-1 text-xs text-[var(--muted)]">Email cannot be changed here.</p>
                    </Field>

                    <Field label="Name" error={errors.name}>
                        <input
                            type="text"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            className="input-field w-full"
                        />
                    </Field>

                    <Field label="Fragrance preference" error={errors.gender}>
                        <select
                            value={data.gender}
                            onChange={(e) => setData('gender', e.target.value)}
                            className="input-field w-full"
                        >
                            {GENDERS.map(([value, label]) => (
                                <option key={value} value={value}>{label}</option>
                            ))}
                        </select>
                    </Field>

                    <Field label="Timezone" error={errors.timezone}>
                        <select
                            value={data.timezone}
                            onChange={(e) => setData('timezone', e.target.value)}
                            className="input-field w-full"
                        >
                            {timezones.map((tz) => (
                                <option key={tz} value={tz}>{tz}</option>
                            ))}
                        </select>
                    </Field>

                    <div className="flex items-center gap-3 pt-2">
                        <button type="submit" disabled={processing} className="btn-primary text-sm disabled:opacity-50">
                            {processing ? 'Saving…' : 'Save changes'}
                        </button>
                        {recentlySuccessful && (
                            <span className="text-sm text-[var(--color-accent)]">Saved.</span>
                        )}
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}

function Field({ label, error, children }) {
    return (
        <div>
            <label className="block text-xs text-[var(--muted)] uppercase tracking-widest mb-2">{label}</label>
            {children}
            {error && <p className="mt-1 text-xs text-[var(--error)]">{error}</p>}
        </div>
    );
}
