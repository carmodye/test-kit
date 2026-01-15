<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobRunTime extends Model
{
    protected $casts = [
        'last_run_at' => 'datetime',
    ];
}