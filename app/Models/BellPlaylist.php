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
        'action_after',
        'action_after_delay',
        'custom_command',
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
            'action_after_delay' => 'integer',
            'order' => 'integer',
        ];
    }
}
