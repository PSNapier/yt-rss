<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['date_pt', 'units_used'])]
class YoutubeApiUsage extends Model
{
    protected $table = 'youtube_api_usage';

    protected function casts(): array
    {
        return [
            'units_used' => 'integer',
        ];
    }
}
