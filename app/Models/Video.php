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
     * Cap a feed query to at most N unwatched videos per channel (the newest by
     * published_at, id) while keeping all watched videos. N is each channel's
     * per-user `user_channel_caps.cap` (0 = unlimited), falling back to
     * UserChannelCap::DEFAULT_CAP when the channel has no custom row.
     *
     * Assumes the query already left joins `user_video_states` (aliased) for the
     * given user, so `user_video_states.state` is NULL for unwatched and
     * 'watched' for watched.
     *
     * @param  Builder<Video>  $query
     * @return Builder<Video>
     */
    public function scopeUnwatchedCappedPerChannel(Builder $query, int $userId): Builder
    {
        $default = UserChannelCap::DEFAULT_CAP;
        $capExpr = "COALESCE(ucc.cap, {$default})";

        return $query
            ->leftJoin('user_channel_caps as ucc', function ($j) use ($userId) {
                $j->on('ucc.channel_id', '=', 'videos.channel_id')
                    ->where('ucc.user_id', $userId);
            })
            ->where(function ($q) use ($userId, $capExpr) {
                // Watched videos always render (existing behavior).
                $q->where('user_video_states.state', 'watched')
                    // ...or this unwatched video is within its channel's cap:
                    // fewer newer unwatched videos of the same channel exist
                    // than the effective cap (0 = unlimited).
                    ->orWhere(function ($q2) use ($userId, $capExpr) {
                        $q2->whereNull('user_video_states.state')
                            ->where(function ($q3) use ($userId, $capExpr) {
                                $q3->whereRaw("{$capExpr} = 0")
                                    ->orWhereRaw(
                                        '(select count(*) from videos as v2 '
                                        .'left join user_video_states as uvs2 '
                                        .'on uvs2.youtube_video_id = v2.youtube_video_id and uvs2.user_id = ? '
                                        .'where v2.channel_id = videos.channel_id '
                                        .'and uvs2.state is null '
                                        .'and (v2.published_at > videos.published_at '
                                        .'or (v2.published_at = videos.published_at and v2.id > videos.id))'
                                        .") < {$capExpr}",
                                        [$userId]
                                    );
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
