<?php

namespace Database\Factories;

use App\Models\Channel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Channel>
 */
class ChannelFactory extends Factory
{
    public function definition(): array
    {
        $channelId = 'UC'.fake()->unique()->regexify('[A-Za-z0-9_-]{22}');

        return [
            'channel_id' => $channelId,
            'name' => fake()->company(),
            'rss_url' => 'https://www.youtube.com/feeds/videos.xml?channel_id='.$channelId,
            'last_fetched_at' => null,
        ];
    }
}
