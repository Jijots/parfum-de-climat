import { Head, useForm } from '@inertiajs/react';
import AuthLayout from '../../Layouts/AuthLayout';
import Field from '../../Components/Field';

export default function ForgotPassword({ urls }) {
    const { data, setData, post, processing, errors } = useForm({ email: '' });

    const submit = (e) => {
        e.preventDefault();
        post(urls.submit);
    };

    return (
        <AuthLayout
            eyebrow="Account recovery"
            title="Reset Your"
            accent="Password"
            subline="We'll email you a link to set a new one."
            footer={
                <a href={urls.login} className="text-[var(--color-accent)] hover:underline">
                    Back to sign in
                </a>
            }
        >
            <Head title="Forgot Password" />

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

                <button type="submit" disabled={processing} className="btn-primary w-full justify-center disabled:opacity-50">
                    {processing ? 'Sending…' : 'Email reset link'}
                </button>
            </form>
        </AuthLayout>
    );
}
