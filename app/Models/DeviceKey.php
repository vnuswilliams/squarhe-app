<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceKey extends Model
{
    protected $fillable = [
        'user_id',
        'device_id',
        'secret',
        'last_sync_at',
    ];

    protected $casts = [
        'secret' => 'encrypted',
        'last_sync_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
