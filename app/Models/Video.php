<?php

namespace App\Models;

use Database\Factories\VideoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['channel_id', 'youtube_video_id', 'title', 'thumbnail_url', 'published_at'])]
class Video extends Model
{
    /** @use HasFactory<VideoFactory> */
    use HasFactory;

    /**
     * Cap a feed query to at most one unwatched video per channel (the newest
     * by published_at, id) while keeping all watched videos. Assumes the query
     * already left joins `user_video_states` (aliased) for the given user, so
     * `user_video_states.state` is NULL for unwatched, 'watched' for watched.
     *
     * @param  Builder<Video>  $query
     * @return Builder<Video>
     */
    public function scopeUnwatchedCapped(Builder $query, int $userId): Builder
    {
        return $query->where(function ($q) use ($userId) {
            // Watched videos always render (existing behavior).
            $q->where('user_video_states.state', 'watched')
                // ...or this is the newest unwatched video of its channel:
                // no other unwatched video of the same channel is more recent.
                ->orWhere(function ($q2) use ($userId) {
                    $q2->whereNull('user_video_states.state')
                        ->whereNotExists(function ($sub) use ($userId) {
                            $sub->selectRaw('1')
                                ->from('videos as v2')
                                ->leftJoin('user_video_states as uvs2', function ($j) use ($userId) {
                                    $j->on('uvs2.youtube_video_id', '=', 'v2.youtube_video_id')
                                        ->where('uvs2.user_id', $userId);
                                })
                                ->whereColumn('v2.channel_id', 'videos.channel_id')
                                ->whereNull('uvs2.state')
                                ->where(function ($w) {
                                    $w->whereColumn('v2.published_at', '>', 'videos.published_at')
                                        ->orWhere(function ($w2) {
                                            $w2->whereColumn('v2.published_at', '=', 'videos.published_at')
                                                ->whereColumn('v2.id', '>', 'videos.id');
                                        });
                                });
                        });
                });
        });
    }

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'channel_is_favorite' => 'boolean',
        ];
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function youtubeUrl(): string
    {
        return 'https://www.youtube.com/watch?v='.$this->youtube_video_id;
    }
}
