import { Head, useForm } from '@inertiajs/react';
import { useMemo } from 'react';
import AuthLayout from '../../Layouts/AuthLayout';
import Field from '../../Components/Field';

const GENDERS = [
    ['', 'Prefer not to say'],
    ['masculine', 'Masculine'],
    ['feminine', 'Feminine'],
    ['unisex', 'No preference'],
];

/**
 * The server enforces Password::min(12)->letters()->mixedCase()->numbers()
 * ->symbols()->uncompromised(). Mirroring the mechanical rules here lets
 * someone see what is missing while they type instead of after a round-trip.
 *
 * The list is advisory only — the server remains the authority, and it also
 * checks the password against known breach corpora, which cannot be done in
 * the browser.
 */
const RULES = [
    ['At least 12 characters', (v) => v.length >= 12],
    ['Upper and lower case', (v) => /[a-z]/.test(v) && /[A-Z]/.test(v)],
    ['A number', (v) => /\d/.test(v)],
    ['A symbol', (v) => /[^A-Za-z0-9]/.test(v)],
];

/**
 * The browser knows its own zone, so ask it rather than defaulting everyone to
 * UTC. Wrapped because Intl can throw in locked-down environments, and a
 * failed guess should cost a sensible default rather than the whole page.
 */
function guessTimezone(available) {
    try {
        const guess = Intl.DateTimeFormat().resolvedOptions().timeZone;

        return available.includes(guess) ? guess : 'UTC';
    } catch {
        return 'UTC';
    }
}

export default function Register({ urls, timezones }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        // Pre-filled from the browser: the timezone decides which season the
        // engine scores against, and asking someone to find their own zone in
        // a 400-entry list is a poor first impression.
        timezone: guessTimezone(timezones),
        gender: '',
    });

    const checks = useMemo(
        () => RULES.map(([label, test]) => [label, test(data.password)]),
        [data.password]
    );

    const submit = (e) => {
        e.preventDefault();
        post(urls.submit, {
            onFinish: () => {
                setData('password', '');
                setData('password_confirmation', '');
            },
        });
    };

    return (
        <AuthLayout
            eyebrow="Get started"
            title="Create an"
            accent="Account"
            subline="Keep a wardrobe, and a record of what you wore when."
            footer={
                <>
                    Already have one?{' '}
                    <a href={urls.login} className="text-[var(--color-accent)] hover:underline">
                        Sign in
                    </a>
                </>
            }
        >
            <Head title="Create Account" />

            <form onSubmit={submit}>
                <Field id="name" label="Name" error={errors.name}>
                    {(props) => (
                        <input
                            {...props}
                            type="text"
                            autoComplete="name"
                            autoFocus
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            className="input-field w-full"
                        />
                    )}
                </Field>

                <Field id="email" label="Email" error={errors.email}>
                    {(props) => (
                        <input
                            {...props}
                            type="email"
                            autoComplete="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            className="input-field w-full"
                        />
                    )}
                </Field>

                <Field id="password" label="Password" error={errors.password}>
                    {(props) => (
                        <input
                            {...props}
                            type="password"
                            autoComplete="new-password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            className="input-field w-full"
                        />
                    )}
                </Field>

                {data.password.length > 0 && (
                    <ul className="-mt-2 mb-5 space-y-1">
                        {checks.map(([label, passed]) => (
                            <li
                                key={label}
                                className={`flex items-center gap-2 text-xs ${
                                    passed ? 'text-[var(--color-accent)]' : 'text-[var(--muted)]'
                                }`}
                            >
                                <span aria-hidden="true">{passed ? '✓' : '·'}</span>
                                {label}
                            </li>
                        ))}
                    </ul>
                )}

                <Field id="password_confirmation" label="Confirm password" error={errors.password_confirmation}>
                    {(props) => (
                        <input
                            {...props}
                            type="password"
                            autoComplete="new-password"
                            value={data.password_confirmation}
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                            className="input-field w-full"
                        />
                    )}
                </Field>

                <Field
                    id="timezone"
                    label="Timezone"
                    error={errors.timezone}
                    hint="Sets your hemisphere, and so your season."
                >
                    {(props) => (
                        <select
                            {...props}
                            value={data.timezone}
                            onChange={(e) => setData('timezone', e.target.value)}
                            className="input-field w-full"
                        >
                            {timezones.map((tz) => (
                                <option key={tz} value={tz}>{tz}</option>
                            ))}
                        </select>
                    )}
                </Field>

                <Field id="gender" label="Fragrance preference" error={errors.gender}>
                    {(props) => (
                        <select
                            {...props}
                            value={data.gender}
                            onChange={(e) => setData('gender', e.target.value)}
                            className="input-field w-full"
                        >
                            {GENDERS.map(([value, label]) => (
                                <option key={value} value={value}>{label}</option>
                            ))}
                        </select>
                    )}
                </Field>

                <button type="submit" disabled={processing} className="btn-primary w-full justify-center disabled:opacity-50">
                    {processing ? 'Creating account…' : 'Create account'}
                </button>
            </form>
        </AuthLayout>
    );
}
