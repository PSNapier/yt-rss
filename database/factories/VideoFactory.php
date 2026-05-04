<?php

namespace Database\Factories;

use App\Models\Channel;
use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Video>
 */
class VideoFactory extends Factory
{
    public function definition(): array
    {
        $videoId = fake()->unique()->regexify('[A-Za-z0-9_-]{11}');

        return [
            'channel_id' => Channel::factory(),
            'youtube_video_id' => $videoId,
            'title' => fake()->sentence(),
            'thumbnail_url' => 'https://i.ytimg.com/vi/'.$videoId.'/hqdefault.jpg',
            'published_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
