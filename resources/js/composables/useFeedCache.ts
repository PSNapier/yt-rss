// Module-scoped cache of loaded feed state, keyed by feed identity
// (e.g. `group:12` or `all`). It persists across Inertia SPA navigations
// because the JS runtime stays alive between `<Link>` visits, so returning
// to a feed can restore its already-loaded videos, cursor, and expand state
// instead of resetting to page one. A full page reload clears it.

export interface FeedCacheEntry<T> {
    items: T[];
    nextUrl: string | null;
    olderExpanded: boolean;
}

const cache = new Map<string, FeedCacheEntry<unknown>>();

export function getFeedCache<T>(key: string): FeedCacheEntry<T> | undefined {
    return cache.get(key) as FeedCacheEntry<T> | undefined;
}

export function saveFeedCache<T extends object>(
    key: string,
    entry: FeedCacheEntry<T>,
): void {
    // Store shallow copies so the cache is decoupled from the component's
    // reactive state once it unmounts.
    cache.set(key, {
        items: entry.items.map((v) => ({ ...v })),
        nextUrl: entry.nextUrl,
        olderExpanded: entry.olderExpanded,
    });
}
