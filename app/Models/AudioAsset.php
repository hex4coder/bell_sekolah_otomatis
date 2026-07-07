<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AudioAsset extends Model
{
    protected $fillable = [
        'name',
        'filename',
        'path',
        'mime_type',
        'size',
    ];
}
