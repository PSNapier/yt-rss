<?php

namespace App\Console\Commands;

use App\Enums\WebSubSubscriptionStatus;
use App\Models\ChannelSubscription;
use App\Services\WebSubAlerter;
use App\Services\WebSubSubscriber;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

#[Signature('websub:renew {--limit= : Max subscriptions to renew this sweep}')]
#[Description('Re-subscribe WebSub leases before they expire, and retry subscriptions the hub never verified')]
class RenewWebSubLeasesCommand extends Command
{
    public function handle(WebSubSubscriber $subscriber, WebSubAlerter $alerter): int
    {
        $limit = (int) ($this->option('limit') ?: config('services.websub.renew_limit', 1000));
        $cutoff = now()->addHours((int) config('services.websub.renew_within_hours', 48));
        $retryAfter = now()->subHours((int) config('services.websub.renew_retry_hours', 6));

        $due = ChannelSubscription::query()
            ->with('channel')
            ->where(fn (Builder $query) => $query
                ->where(fn (Builder $expiring) => $expiring
                    ->whereIn('status', [
                        WebSubSubscriptionStatus::Active->value,
                        WebSubSubscriptionStatus::Pending->value,
                    ])
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '<=', $cutoff)
                )
                // Never verified, or the hub rejected us: both mean no push is arriving.
                ->orWhere(fn (Builder $unhealthy) => $unhealthy
                    ->where('status', WebSubSubscriptionStatus::Failed->value)
                    ->where('updated_at', '<=', $retryAfter)
                )
                ->orWhere(fn (Builder $unverified) => $unverified
                    ->whereNull('expires_at')
                    ->where('created_at', '<=', $retryAfter)
                )
            )
            // The lease only moves when the hub re-verifies, so throttle repeat POSTs.
            ->where(fn (Builder $query) => $query
                ->whereNull('last_renewal_attempt_at')
                ->orWhere('last_renewal_attempt_at', '<=', $retryAfter)
            )
            ->orderBy('expires_at')
            ->limit($limit)
            ->get();

        if ($due->isEmpty()) {
            $this->components->info('No WebSub leases due for renewal.');

            return self::SUCCESS;
        }

        $renewed = 0;
        /** @var Collection<int, ChannelSubscription> $failures */
        $failures = collect();

        foreach ($due as $subscription) {
            if ($subscriber->renew($subscription)) {
                $renewed++;

                continue;
            }

            $failures->push($subscription);
        }

        if ($failures->isNotEmpty()) {
            // One alert per sweep: a hub outage should not fan out into hundreds of pages.
            $alerter->renewalsFailed($failures);
        }

        if ($due->count() >= $limit) {
            // A full sweep means the queue is deeper than one hour can drain.
            $this->components->warn(
                "Renewal sweep hit its limit of {$limit}: the renewal queue is falling behind."
            );
        }

        $this->components->info(
            "Renewal requests sent: {$renewed} (failed: {$failures->count()})."
        );

        return self::SUCCESS;
    }
}
