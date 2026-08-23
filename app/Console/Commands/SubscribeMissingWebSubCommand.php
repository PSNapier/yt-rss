<?php

namespace App\Console\Commands;

use App\Enums\WebSubSubscriptionStatus;
use App\Models\Channel;
use App\Models\ChannelSubscription;
use App\Services\WebSubAlerter;
use App\Services\WebSubSubscriber;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

#[Signature('websub:subscribe-missing {--limit= : Max channels to subscribe this run (default: all)} {--dry-run : List candidates without subscribing}')]
#[Description('Subscribe and backfill any channel that has no WebSub subscription row at all')]
class SubscribeMissingWebSubCommand extends Command
{
    /**
     * Channels only get a subscription when they are first added, so any channel
     * that predates WebSub, or that arrived with a fresh database on a new
     * environment, would never be pushed to. Run this by hand to close that gap,
     * typically once after a deploy to a new environment.
     *
     * Rows that exist but are unhealthy are `websub:renew`'s job, not this one.
     */
    public function handle(WebSubSubscriber $subscriber, WebSubAlerter $alerter): int
    {
        $limit = $this->option('limit') !== null
            ? max(1, (int) $this->option('limit'))
            : null;

        /** @var Collection<int, Channel> $missing */
        $missing = Channel::query()
            ->whereDoesntHave('webSubSubscription')
            ->orderBy('id')
            ->when($limit !== null, fn ($query) => $query->limit($limit))
            ->get();

        if ($missing->isEmpty()) {
            $this->components->info('Every channel already has a WebSub subscription.');

            return self::SUCCESS;
        }

        foreach ($missing as $channel) {
            $this->line("{$channel->channel_id}: no subscription");
        }

        if ($this->option('dry-run')) {
            $this->components->info('[dry-run] '.$missing->count().' channels would be subscribed.');

            return self::SUCCESS;
        }

        $subscribed = 0;
        /** @var Collection<int, ChannelSubscription> $failures */
        $failures = collect();

        foreach ($missing as $channel) {
            $subscription = $subscriber->ensureSubscribed($channel);

            if ($subscription->status === WebSubSubscriptionStatus::Failed) {
                $failures->push($subscription);

                continue;
            }

            $subscribed++;
        }

        if ($failures->isNotEmpty()) {
            // One alert per sweep: a hub outage should not fan out into hundreds of pages.
            $alerter->subscribesFailed($failures);
        }

        if ($limit !== null && $missing->count() >= $limit) {
            $this->components->warn(
                "Run hit its limit of {$limit}; more channels may remain unsubscribed."
            );
        }

        $this->components->info(
            "Subscribe requests sent: {$subscribed} (failed: {$failures->count()})."
        );

        return self::SUCCESS;
    }
}
