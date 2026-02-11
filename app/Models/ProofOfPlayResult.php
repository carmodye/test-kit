<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProofOfPlayResult extends Model
{
    protected $fillable = [
        'client',
        'slide_id',
        'slide_name',
        'device_id',
        'display_id',
        'site_id',
        'site_name',
        'duration_seconds',
        'play_count',
        'played_at',
        'duration',
    ];
}
