<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password', 'feed_cap_enabled', 'shorts_hidden'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'feed_cap_enabled' => 'boolean',
            'shorts_hidden' => 'boolean',
        ];
    }

    public function channelGroups(): HasMany
    {
        return $this->hasMany(ChannelGroup::class);
    }

    /**
     * Channels reachable through at least one visible group. A channel that is
     * only in hidden groups is muted. Pass group IDs to treat them as hidden too.
     *
     * @param  iterable<int>  $excludingGroupIds
     * @return Collection<int, int>
     */
    public function visibleChannelIds(iterable $excludingGroupIds = []): Collection
    {
        return $this->channelGroups()
            ->visible()
            ->whereNotIn('channel_groups.id', $excludingGroupIds)
            ->join('channel_group_channel as cgc', 'cgc.channel_group_id', '=', 'channel_groups.id')
            ->distinct()
            ->pluck('cgc.channel_id');
    }

    public function favoritedChannels(): BelongsToMany
    {
        return $this->belongsToMany(Channel::class, 'user_channel_favorites')
            ->withTimestamps();
    }

    /**
     * Categories the user has hidden from the Shorts feed. Absence means visible,
     * so a newly created group shows up without an opt-in.
     */
    public function hiddenShortGroups(): BelongsToMany
    {
        return $this->belongsToMany(ChannelGroup::class, 'user_hidden_short_groups')
            ->withTimestamps();
    }
}
