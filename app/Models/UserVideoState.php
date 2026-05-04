<?php

namespace App\Models;

use Database\Factories\UserVideoStateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'youtube_video_id', 'state'])]
class UserVideoState extends Model
{
    /** @use HasFactory<UserVideoStateFactory> */
    use HasFactory;

    public const STATE_WATCHED = 'watched';

    public const STATE_HIDDEN = 'hidden';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
