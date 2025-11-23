<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IcalFeed extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'direction',
        'url',
        'token',
        'active',
        'last_synced_at',
        'sync_log',
    ];

    protected $casts = [
        'active' => 'boolean',
        'last_synced_at' => 'datetime',
        'sync_log' => 'array',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
