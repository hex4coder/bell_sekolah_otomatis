<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolDay extends Model
{
    protected $fillable = [
        'day_of_week',
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
