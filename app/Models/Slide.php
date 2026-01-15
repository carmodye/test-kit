<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slide extends Model
{
    protected $fillable = [
        'client',
        'slide_id',
        'name',
        'path',
        'type',
        'duration',
        'hold',
        'notbefore',
        'notafter',
        'deleted',
        'raw_data',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'deleted' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('deleted', false);
    }

    public function scopeContent($query)
    {
        return $query->where('type', '!=', 'folder');
    }
}