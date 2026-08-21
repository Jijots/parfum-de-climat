const GENDERS = [
    ['all', 'All'],
    ['masculine', 'Men'],
    ['feminine', 'Women'],
    ['unisex', 'Unisex'],
];

/**
 * SearchBar — text input plus gender segmented control.
 *
 * The magnifier swaps to a spinner while a request is in flight; the parent
 * decides when that is, after its 150ms grace period.
 */
export default function SearchBar({ search, onSearchChange, gender, onGenderChange, loading }) {
    return (
        <div className="mb-6 flex flex-col sm:flex-row gap-3">
            <div className="relative flex-1">
                {loading ? (
                    <svg
                        className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-[var(--color-accent)] animate-spin"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    >
                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                ) : (
                    <svg
                        className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-[var(--muted)]"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" strokeWidth="1.5"
                    >
                        <path strokeLinecap="round" strokeLinejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                )}

                <input
                    type="text"
                    value={search}
                    onChange={(e) => onSearchChange(e.target.value)}
                    placeholder="Search by name or brand…"
                    className="input-field w-full pl-9"
                />
            </div>

            <div className="flex gap-1 glass rounded-xl p-1 self-start">
                {GENDERS.map(([value, label]) => (
                    <button
                        key={value}
                        onClick={() => onGenderChange(value)}
                        className={`px-3 py-1.5 rounded-lg text-sm transition-colors ${
                            gender === value
                                ? 'bg-[var(--color-accent)] text-white'
                                : 'text-[var(--muted)] hover:text-[var(--ink)]'
                        }`}
                    >
                        {label}
                    </button>
                ))}
            </div>
        </div>
    );
}
