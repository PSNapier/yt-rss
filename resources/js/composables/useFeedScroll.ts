// Per-feed scroll memory. Inertia resets scroll to the top on every visit, so
// returning to a feed would otherwise lose the user's place even though its
// cards are restored from `useFeedCache`.

const positions = new Map<string, number>();

let currentKey: string | null = null;
let suppress = false;
let suppressTimer: ReturnType<typeof setTimeout> | undefined;

export const onFeedScroll = (): void => {
    if (!suppress && currentKey) {
        positions.set(currentKey, window.scrollY);
    }
};

// Switch to a feed and restore its saved position once its cards render.
// Saves are suppressed while Inertia's own scroll-to-top settles.
export const beginFeedScroll = (key: string, nextTick: () => Promise<void>) => {
    // The group feed calls this from setup, which also runs during SSR.
    if (typeof window === 'undefined') {
        return;
    }

    const target = positions.get(key) ?? 0;

    currentKey = key;
    suppress = true;
    clearTimeout(suppressTimer);

    nextTick().then(() => {
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                window.scrollTo(0, target);
                positions.set(key, target);
                suppressTimer = setTimeout(() => {
                    suppress = false;
                }, 150);
            });
        });
    });
};

export const endFeedScroll = (): void => {
    currentKey = null;
};
