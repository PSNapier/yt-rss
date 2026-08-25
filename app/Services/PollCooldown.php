<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;

/**
 * A YouTube RSS block is IP-scoped and lasts hours to about a day. Polling through
 * one wastes requests and may extend it, so a detected block parks the sweep until
 * a wall-clock deadline stored outside the process.
 */
class PollCooldown
{
    protected const KEY = 'poll:cooldown_until';

    protected const REASON_KEY = 'poll:cooldown_reason';

    public function __construct(protected PollAlert $alert) {}

    public function isActive(): bool
    {
        return $this->activeUntil() !== null;
    }

    public function activeUntil(): ?CarbonImmutable
    {
        $stored = Cache::get(self::KEY);

        if (! is_string($stored)) {
            return null;
        }

        $until = CarbonImmutable::parse($stored);

        return $until->isFuture() ? $until : null;
    }

    public function reason(): ?string
    {
        $reason = Cache::get(self::REASON_KEY);

        return is_string($reason) ? $reason : null;
    }

    public function start(string $reason): CarbonImmutable
    {
        $hours = max(1, (int) config('services.polling.cooldown_hours', 6));
        $until = CarbonImmutable::now()->addHours($hours);

        $expiry = $until->addHour();

        Cache::put(self::KEY, $until->toIso8601String(), $expiry);
        Cache::put(self::REASON_KEY, $reason, $expiry);

        $this->alert->raise('Polling cooldown started', [
            'reason' => $reason,
            'until' => $until->toIso8601String(),
        ]);

        return $until;
    }

    public function clear(): void
    {
        Cache::forget(self::KEY);
        Cache::forget(self::REASON_KEY);
    }
}
