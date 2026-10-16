<?php

namespace App\Models;

use Database\Factories\ChannelGroupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['user_id', 'name', 'icon', 'hidden'])]
class ChannelGroup extends Model
{
    /** @use HasFactory<ChannelGroupFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hidden' => 'boolean',
        ];
    }

    /**
     * A hidden group is muted: off the sidebar, and its channels drop out of
     * All Videos and Shorts unless another visible group also holds them.
     *
     * @param  Builder<ChannelGroup>  $query
     */
    #[Scope]
    protected function visible(Builder $query): void
    {
        $query->where('channel_groups.hidden', false);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(Channel::class, 'channel_group_channel')
            ->withTimestamps();
    }
}
