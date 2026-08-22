import { Head, useForm } from '@inertiajs/react';
import AuthLayout from '../../Layouts/AuthLayout';
import Field from '../../Components/Field';

export default function Login({ urls }) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();
        // Clear the password on any failure so a rejected credential is not
        // left sitting in the DOM for the next attempt.
        post(urls.submit, { onFinish: () => setData('password', '') });
    };

    return (
        <AuthLayout
            eyebrow="Welcome back"
            title="Sign"
            accent="In"
            subline="Your wardrobe and readings are waiting."
            footer={
                <>
                    No account yet?{' '}
                    <a href={urls.register} className="text-[var(--color-accent)] hover:underline">
                        Create one
                    </a>
                </>
            }
        >
            <Head title="Sign In" />

            <form onSubmit={submit}>
                <Field id="email" label="Email" error={errors.email}>
                    {(props) => (
                        <input
                            {...props}
                            type="email"
                            autoComplete="email"
                            autoFocus
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
                            autoComplete="current-password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            className="input-field w-full"
                        />
                    )}
                </Field>

                <div className="flex items-center justify-between mb-6">
                    <label className="flex items-center gap-2 text-xs text-[var(--muted)] cursor-pointer">
                        <input
                            type="checkbox"
                            checked={data.remember}
                            onChange={(e) => setData('remember', e.target.checked)}
                            className="accent-[var(--color-accent)]"
                        />
                        Remember me
                    </label>

                    <a href={urls.forgot} className="text-xs text-[var(--muted)] hover:text-[var(--color-accent)] transition-colors">
                        Forgot password?
                    </a>
                </div>

                <button type="submit" disabled={processing} className="btn-primary w-full justify-center disabled:opacity-50">
                    {processing ? 'Signing in…' : 'Sign in'}
                </button>
            </form>
        </AuthLayout>
    );
}
