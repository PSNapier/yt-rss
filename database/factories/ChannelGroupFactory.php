<?php

namespace Database\Factories;

use App\Models\ChannelGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChannelGroup>
 */
class ChannelGroupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->unique()->words(2, true),
        ];
    }
}
