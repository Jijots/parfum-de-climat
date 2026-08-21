import { animate } from 'motion/react';
import { useEffect, useState } from 'react';

/**
 * AnimatedNumber — counts up to a value on mount and whenever it changes.
 *
 * Used for the catalogue total. A number that settles into place reads as a
 * measured quantity rather than a static label, which suits a catalogue index.
 *
 * ── Why React state and not a MotionValue ────────────────────────────────────
 * The obvious implementation is useMotionValue + useTransform rendered inside a
 * motion.span. That version is broken in a way that is easy to miss: a
 * MotionValue writes to the DOM through motion's own requestAnimationFrame
 * loop, deliberately bypassing React. Browsers pause rAF in background tabs, so
 * a page opened in a background tab renders the initial value forever — "0
 * entries" — and only corrects itself if the tab is focused. Setting the value
 * imperatively does not help, because the write still has to be flushed by that
 * same paused loop.
 *
 * Holding the number in React state instead means every update is a normal
 * render, which happens whether or not the tab is visible. The animation is
 * then purely an enhancement layered on top.
 *
 * Restrained on purpose: 900ms, ease-out, no bounce.
 */
export default function AnimatedNumber({ value, className = '' }) {
    const [display, setDisplay] = useState(value);

    useEffect(() => {
        const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        // No animation wanted, or none possible — show the real number at once.
        if (prefersReduced || document.hidden) {
            setDisplay(value);
            return;
        }

        const controls = animate(0, value, {
            duration: 0.9,
            ease: [0.22, 1, 0.36, 1],
            onUpdate: (v) => setDisplay(Math.round(v)),
            // If the tab is hidden partway through, the loop stalls. Landing on
            // the exact target here means the number is never left mid-count.
            onComplete: () => setDisplay(value),
        });

        return () => {
            controls.stop();
            setDisplay(value);
        };
    }, [value]);

    // tabular-nums stops the digits changing width mid-count, which otherwise
    // makes the surrounding sentence jitter.
    return <span className={`tabular-nums ${className}`}>{display.toLocaleString()}</span>;
}
