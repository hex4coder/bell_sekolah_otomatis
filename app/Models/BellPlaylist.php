<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BellPlaylist extends Model
{
    protected $fillable = [
        'type',
        'name',
        'audio_assets',
        'time_range_start',
        'time_range_end',
        'is_active',
        'day_of_week',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'audio_assets' => 'array',
            'day_of_week' => 'array',
            'time_range_start' => 'datetime:H:i',
            'time_range_end' => 'datetime:H:i',
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }
}
