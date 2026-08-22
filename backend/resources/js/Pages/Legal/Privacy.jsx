import { Head } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import PageHeader from '../../Components/PageHeader';
import Rule from '../../Components/Rule';

/**
 * Privacy policy.
 *
 * Written from the actual schema and the actual outbound calls, not a
 * template — every claim below corresponds to a real column or a real request.
 * If the data model changes, this page has to change with it.
 */
export default function Privacy({ updated, contactEmail }) {
    return (
        <AppLayout>
            <Head title="Privacy Policy" />

            <div className="mx-auto max-w-2xl px-6 py-16">
                <PageHeader
                    eyebrow="Legal"
                    accent="Policy"
                    subline={`Last updated ${updated}.`}
                >
                    Privacy
                </PageHeader>

                <div className="space-y-12 text-[15px] leading-relaxed text-[var(--muted)]">
                    <section>
                        <Rule label="In short" />
                        <p>
                            Parfum de Climat stores the fragrances you save, the weather readings
                            you take, and the details of your account. It does not sell your data,
                            does not run advertising, and does not use third-party analytics or
                            tracking cookies.
                        </p>
                    </section>

                    <section>
                        <Rule label="What is collected" />

                        <dl className="space-y-5">
                            <Item term="Account details">
                                Your name, email address and time zone. Optionally a fragrance
                                preference. If you sign in with Google, your Google account
                                identifier and profile picture URL are stored so the account can be
                                recognised next time.
                            </Item>

                            <Item term="Your wardrobe">
                                Which fragrances you have saved, which are marked favourite, any
                                personal notes you write against them, and the date you recorded
                                acquiring one.
                            </Item>

                            <Item term="Weather readings">
                                Each time you ask for a recommendation while signed in, the reading
                                is saved: the temperature, humidity, conditions and season, the
                                place name, and{' '}
                                <strong className="text-[var(--ink)]">
                                    the latitude and longitude used for the lookup
                                </strong>
                                . If you typed a city name rather than sharing your location, the
                                coordinates are those of the city, not of you. The fragrance you
                                said you wore is stored alongside it.
                            </Item>

                            <Item term="Passwords">
                                Stored only as a one-way hash, never in a readable form. Accounts
                                created through Google have no password at all.
                            </Item>
                        </dl>
                    </section>

                    <section>
                        <Rule label="What is not collected" />
                        <p>
                            No advertising or analytics trackers. No third-party cookies. The only
                            cookies set are the ones needed to keep you signed in and to protect
                            forms against cross-site request forgery. Browsing as a guest keeps
                            your wardrobe in your browser session and writes nothing to an account.
                        </p>
                    </section>

                    <section>
                        <Rule label="Who it is shared with" />

                        <dl className="space-y-5">
                            <Item term="OpenWeatherMap">
                                Receives the coordinates or city name you ask about, in order to
                                return the weather. It is not told who you are.
                            </Item>
                            <Item term="Google">
                                Only if you choose to sign in with Google. Google tells us your
                                email address, name and profile picture; we tell Google nothing
                                about your use of the app.
                            </Item>
                            <Item term="Hosting and database">
                                The application runs on Render and stores data in a Neon PostgreSQL
                                database hosted in Singapore. Traffic is served through Cloudflare.
                                Using the app therefore involves your data being stored outside your
                                own country.
                            </Item>
                        </dl>

                        <p className="mt-5">
                            Your data is not sold, rented, or shared with anyone else.
                        </p>
                    </section>

                    <section>
                        <Rule label="How long it is kept" />
                        <p>
                            Account details and wardrobe entries are kept until you delete them or
                            close your account. Weather readings are kept so the history page can
                            show them. Deleting your account removes your account record, your
                            wardrobe and your readings.
                        </p>
                    </section>

                    <section>
                        <Rule label="Your choices" />
                        <ul className="space-y-2 list-disc pl-5">
                            <li>You can use the recommendation feature without sharing your location by typing a city name instead.</li>
                            <li>You can use the whole app as a guest, without an account.</li>
                            <li>You can change your name, time zone and preference at any time on your profile.</li>
                            <li>You can ask for a copy of your data, or for your account and its data to be deleted, by emailing the address below.</li>
                        </ul>
                    </section>

                    <section>
                        <Rule label="Contact" />
                        <p>
                            Questions, data requests, or account deletion:{' '}
                            <a href={`mailto:${contactEmail}`} className="text-[var(--color-accent)] hover:underline">
                                {contactEmail}
                            </a>
                        </p>
                    </section>
                </div>
            </div>
        </AppLayout>
    );
}

function Item({ term, children }) {
    return (
        <div>
            <dt className="font-mono text-[11px] uppercase tracking-[0.15em] text-[var(--ink)] mb-1.5">
                {term}
            </dt>
            <dd>{children}</dd>
        </div>
    );
}
