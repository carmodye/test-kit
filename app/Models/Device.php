<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'client',
        'device_id',
        'display_id',
        'site_name',
        'app_name',
        'site_id',
        'other_data',
    ];

    protected $casts = [
        'other_data' => 'json',
    ];
}