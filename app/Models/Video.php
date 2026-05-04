<?php

namespace App\Models;

use Database\Factories\VideoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['channel_id', 'youtube_video_id', 'title', 'thumbnail_url', 'published_at'])]
class Video extends Model
{
    /** @use HasFactory<VideoFactory> */
    use HasFactory;

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
