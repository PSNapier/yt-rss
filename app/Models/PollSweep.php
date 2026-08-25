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
    'failure_categories',
    'shorts_flagged',
    'cap_hit',
    'cooldown_triggered',
    'failure_alert',
])]
class PollSweep extends Model
{
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'failure_categories' => 'array',
            'cap_hit' => 'boolean',
            'cooldown_triggered' => 'boolean',
            'failure_alert' => 'boolean',
        ];
    }
}
