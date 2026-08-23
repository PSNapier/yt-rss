<?php

namespace App\Models;

use App\Enums\WebSubSubscriptionStatus;
use Database\Factories\ChannelSubscriptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'channel_id',
    'topic_url',
    'callback_token',
    'secret',
    'status',
    'lease_seconds',
    'expires_at',
    'last_verified_at',
])]
class ChannelSubscription extends Model
{
    /** @use HasFactory<ChannelSubscriptionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => WebSubSubscriptionStatus::class,
            'lease_seconds' => 'integer',
            'expires_at' => 'datetime',
            'last_verified_at' => 'datetime',
        ];
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function callbackUrl(): string
    {
        $base = rtrim(
            (string) (config('services.websub.callback_base') ?: config('app.url')),
            '/',
        );

        return $base.'/websub/'.$this->callback_token;
    }
}
