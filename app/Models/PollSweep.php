<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'started_at',
    'finished_at',
    'channels_polled',
    'fetched',
    'not_modified',
    'failed',
    'blocked',
    'cap_hit',
    'cooldown_triggered',
])]
class PollSweep extends Model
{
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'cap_hit' => 'boolean',
            'cooldown_triggered' => 'boolean',
        ];
    }
}
