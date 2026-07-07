<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BellSchedule extends Model
{
    protected $fillable = [
        'day_of_week',
        'name',
        'time',
        'audio_file',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'time' => 'datetime:H:i',
            'is_active' => 'boolean',
        ];
    }
}
