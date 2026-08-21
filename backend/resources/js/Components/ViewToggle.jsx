/**
 * ViewToggle — switch between the editorial index and the image grid.
 *
 * Both views suit different tasks: the index is for scanning names and brands
 * quickly, the grid for recognising bottles by sight. The choice is remembered
 * in localStorage so it survives navigation.
 */
export default function ViewToggle({ view, onChange }) {
    // Grid first: it is the default, and a toggle whose default sits second
    // reads as though the other option is primary.
    const options = [
        ['grid', 'Tiles', 'M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z'],
        ['index', 'List', 'M4 6h16M4 12h16M4 18h16'],
    ];

    return (
        <div className="flex gap-1 glass rounded-full p-1 self-start">
            {options.map(([value, label, path]) => (
                <button
                    key={value}
                    onClick={() => onChange(value)}
                    title={`${label} view`}
                    aria-pressed={view === value}
                    className={`px-3 py-1.5 rounded-full text-xs transition-colors flex items-center gap-1.5 ${
                        view === value
                            ? 'bg-[var(--color-accent)] text-white'
                            : 'text-[var(--muted)] hover:text-[var(--ink)]'
                    }`}
                >
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.5">
                        <path strokeLinecap="round" strokeLinejoin="round" d={path} />
                    </svg>
                    <span className="hidden sm:inline">{label}</span>
                </button>
            ))}
        </div>
    );
}
