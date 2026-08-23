export type VideoState = 'watched' | 'hidden' | null;

const xsrfToken = (): string => {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : '';
};

/**
 * Persist a video's watched/hidden state.
 *
 * Deliberately a plain fetch rather than an Inertia visit: the feed's `videos`
 * prop is deferred, so any Inertia visit resets it to undefined and makes the
 * whole grid fall back to the loading skeleton for a frame. The UI already
 * updates optimistically, so this only needs to write and return.
 */
export const postVideoState = (
    url: string,
    state: VideoState,
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
        body: JSON.stringify({ state }),
    });
