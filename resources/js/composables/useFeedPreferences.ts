export interface FeedPreferences {
    show_watched?: boolean;
    starred_only?: boolean;
}

const xsrfToken = (): string => {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : '';
};

/**
 * Persist the feed display toggles (show watched, starred only).
 *
 * A plain fetch, not an Inertia visit: the feed's `videos` prop is deferred,
 * so any visit resets it and flashes the loading skeleton. The UI already
 * updates optimistically.
 */
export const postFeedPreferences = (
    url: string,
    preferences: FeedPreferences,
): Promise<Response> =>
    fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-XSRF-TOKEN': xsrfToken(),
        },
        body: JSON.stringify(preferences),
    });
