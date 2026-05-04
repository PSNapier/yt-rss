<?php

namespace App\Models;

use Database\Factories\ChannelGroupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['user_id', 'name'])]
class ChannelGroup extends Model
{
    /** @use HasFactory<ChannelGroupFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(Channel::class, 'channel_group_channel')
            ->withPivot('is_favorite')
            ->withTimestamps();
    }
}
