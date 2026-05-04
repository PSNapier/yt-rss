<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserVideoState;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserVideoState>
 */
class UserVideoStateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'youtube_video_id' => fake()->unique()->regexify('[A-Za-z0-9_-]{11}'),
            'state' => fake()->randomElement([UserVideoState::STATE_WATCHED, UserVideoState::STATE_HIDDEN]),
        ];
    }

    public function watched(): static
    {
        return $this->state(fn () => ['state' => UserVideoState::STATE_WATCHED]);
    }

    public function hidden(): static
    {
        return $this->state(fn () => ['state' => UserVideoState::STATE_HIDDEN]);
    }
}
