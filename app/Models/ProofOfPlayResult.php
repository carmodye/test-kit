<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProofOfPlayResult extends Model
{
    protected $fillable = [
        'client',
        'slide_id',
        'slide_name',
        'device_id',
        'display_id',
        'display_name',
        'device_name',
        'site_id',
        'site_name',
        'duration_seconds',
        'play_count',
        'played_at',
        'duration',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id', 'device_id')
            ->where('client', $this->client);
    }

    public function slide(): BelongsTo
    {
        return $this->belongsTo(Slide::class, 'slide_id', 'slide_id');
    }
}
