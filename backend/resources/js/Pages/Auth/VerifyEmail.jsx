import { Head, useForm } from '@inertiajs/react';
import AuthLayout from '../../Layouts/AuthLayout';

/**
 * VerifyEmail — the holding page after registration.
 *
 * Laravel signs the verification link, so the only action available here is
 * requesting another one.
 */
export default function VerifyEmail({ urls, email }) {
    const { post, processing, recentlySuccessful } = useForm({});

    const resend = (e) => {
        e.preventDefault();
        post(urls.resend, { preserveScroll: true });
    };

    return (
        <AuthLayout
            eyebrow="One more step"
            title="Verify Your"
            accent="Email"
            subline={
                email
                    ? `We sent a link to ${email}. Open it to finish setting up your account.`
                    : 'Open the link we emailed you to finish setting up your account.'
            }
        >
            <Head title="Verify Email" />

            <form onSubmit={resend}>
                <button type="submit" disabled={processing} className="btn-primary w-full justify-center disabled:opacity-50">
                    {processing ? 'Sending…' : 'Resend verification email'}
                </button>

                {recentlySuccessful && (
                    <p className="mt-4 font-mono text-[11px] uppercase tracking-[0.15em] text-[var(--color-accent)] text-center">
                        Sent — check your inbox
                    </p>
                )}
            </form>

            <form method="POST" action={urls.logout} className="mt-6 text-center">
                <input type="hidden" name="_token" value={document.querySelector('meta[name=csrf-token]')?.content ?? ''} />
                <button type="submit" className="text-xs text-[var(--muted)] hover:text-[var(--color-accent)] transition-colors">
                    Sign out
                </button>
            </form>
        </AuthLayout>
    );
}
