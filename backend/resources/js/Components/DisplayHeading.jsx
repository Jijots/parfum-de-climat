import { motion } from 'motion/react';

/**
 * DisplayHeading — an editorial headline that mixes roman and italic serif.
 *
 * The mixed-face headline is the signature of this design language: an italic
 * word set inside a roman line creates typographic rhythm that a single face
 * cannot. Pass the emphasised word via `accent`.
 *
 * Words fade up in sequence. The stagger is deliberately small (40ms) and the
 * travel short — enough to feel composed on load, not enough to make the reader
 * wait for the page to finish performing.
 *
 * Respects prefers-reduced-motion: the `motion` library skips transforms
 * automatically when the user has asked for reduced motion.
 */
export default function DisplayHeading({ children, accent, className = '' }) {
    const words = String(children).split(' ');

    return (
        <h1 className={`font-display font-light text-[var(--ink)] ${className}`}>
            {words.map((word, i) => (
                <motion.span
                    key={`${word}-${i}`}
                    initial={{ opacity: 0, y: 8 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.35, delay: i * 0.04, ease: [0.22, 1, 0.36, 1] }}
                    className="inline-block mr-[0.25em]"
                >
                    {word}
                </motion.span>
            ))}
            {accent && (
                <motion.em
                    initial={{ opacity: 0, y: 8 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.35, delay: words.length * 0.04, ease: [0.22, 1, 0.36, 1] }}
                    className="inline-block italic text-[var(--color-accent)]"
                >
                    {accent}
                </motion.em>
            )}
        </h1>
    );
}
