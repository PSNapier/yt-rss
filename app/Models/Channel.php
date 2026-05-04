<?php

namespace App\Models;

use Database\Factories\ChannelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['channel_id', 'name', 'rss_url', 'last_fetched_at'])]
class Channel extends Model
{
    /** @use HasFactory<ChannelFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'last_fetched_at' => 'datetime',
        ];
    }

    public function channelGroups(): BelongsToMany
    {
        return $this->belongsToMany(ChannelGroup::class, 'channel_group_channel')
            ->withPivot('is_favorite')
            ->withTimestamps();
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }

    public function rssUrl(): string
    {
        return $this->rss_url
            ?: 'https://www.youtube.com/feeds/videos.xml?channel_id='.$this->channel_id;
    }
}
