<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'channel_id', 'cap'])]
class UserChannelCap extends Model
{
    /**
     * Effective cap applied to a channel that has no custom row: at most one
     * unwatched video (matching the [004] cap MVP).
     */
    public const DEFAULT_CAP = 1;

    /**
     * Sentinel cap value meaning "no cap" (all unwatched videos shown).
     */
    public const UNLIMITED = 0;

    protected function casts(): array
    {
        return [
            'cap' => 'integer',
        ];
    }
}
