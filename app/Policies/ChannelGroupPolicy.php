<?php

namespace App\Policies;

use App\Models\ChannelGroup;
use App\Models\User;

class ChannelGroupPolicy
{
    public function view(User $user, ChannelGroup $group): bool
    {
        return $group->user_id === $user->id;
    }

    public function update(User $user, ChannelGroup $group): bool
    {
        return $group->user_id === $user->id;
    }

    public function delete(User $user, ChannelGroup $group): bool
    {
        return $group->user_id === $user->id;
    }
}
