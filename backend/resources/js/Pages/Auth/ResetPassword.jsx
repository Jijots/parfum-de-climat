import { Head, useForm } from '@inertiajs/react';
import AuthLayout from '../../Layouts/AuthLayout';
import Field from '../../Components/Field';

export default function ResetPassword({ urls, token, email }) {
    const { data, setData, post, processing, errors } = useForm({
        // The token and email come from the signed link and are not editable;
        // they ride along in the form body so the server can match the request
        // to the reset it issued.
        token,
        email,
        password: '',
        password_confirmation: '',
    });

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
            eyebrow="Account recovery"
            title="Choose a New"
            accent="Password"
            subline={email ? `Resetting the password for ${email}.` : null}
            footer={
                <a href={urls.login} className="text-[var(--color-accent)] hover:underline">
                    Back to sign in
                </a>
            }
        >
            <Head title="Reset Password" />

            <form onSubmit={submit}>
                <Field id="password" label="New password" error={errors.password}>
                    {(props) => (
                        <input
                            {...props}
                            type="password"
                            autoComplete="new-password"
                            autoFocus
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            className="input-field w-full"
                        />
                    )}
                </Field>

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

                {errors.email && <p className="mb-4 text-xs text-[var(--error)]">{errors.email}</p>}

                <button type="submit" disabled={processing} className="btn-primary w-full justify-center disabled:opacity-50">
                    {processing ? 'Saving…' : 'Set new password'}
                </button>
            </form>
        </AuthLayout>
    );
}
