const MINUTE = 60;
const HOUR = 60 * MINUTE;
const DAY = 24 * HOUR;
const WEEK = 7 * DAY;
const MONTH = 30 * DAY;
const YEAR = 365 * DAY;

/**
 * Compact relative age: `now`, `5m`, `3h`, `3d`, `2w`, `5mo`, `2y`.
 * Future or unparseable dates return an empty string.
 */
export function relativeAge(
    date: string | Date,
    now: Date = new Date(),
): string {
    const then = typeof date === 'string' ? new Date(date) : date;
    const seconds = Math.floor((now.getTime() - then.getTime()) / 1000);

    if (Number.isNaN(seconds) || seconds < 0) {
        return '';
    }

    if (seconds < MINUTE) {
        return 'now';
    }

    if (seconds < HOUR) {
        return `${Math.floor(seconds / MINUTE)}m`;
    }

    if (seconds < DAY) {
        return `${Math.floor(seconds / HOUR)}h`;
    }

    if (seconds < WEEK) {
        return `${Math.floor(seconds / DAY)}d`;
    }

    if (seconds < MONTH) {
        return `${Math.floor(seconds / WEEK)}w`;
    }

    if (seconds < YEAR) {
        return `${Math.floor(seconds / MONTH)}mo`;
    }

    return `${Math.floor(seconds / YEAR)}y`;
}
