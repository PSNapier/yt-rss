<?php

namespace Database\Factories;

use App\Enums\WebSubSubscriptionStatus;
use App\Models\Channel;
use App\Models\ChannelSubscription;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ChannelSubscription>
 */
class ChannelSubscriptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'channel_id' => Channel::factory(),
            'topic_url' => 'https://www.youtube.com/feeds/videos.xml?channel_id=UCplaceholder',
            'callback_token' => Str::random(40),
            'secret' => Str::random(32),
            'status' => WebSubSubscriptionStatus::Pending,
            'lease_seconds' => null,
            'expires_at' => null,
            'last_verified_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => WebSubSubscriptionStatus::Active,
            'lease_seconds' => 864000,
            'expires_at' => now()->addSeconds(864000),
            'last_verified_at' => now(),
        ]);
    }

    public function forChannel(Channel $channel): static
    {
        return $this->state(fn () => [
            'channel_id' => $channel->id,
            'topic_url' => $channel->rssUrl(),
        ]);
    }
}
