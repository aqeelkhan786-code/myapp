<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'doc_type',
        'locale',
        'storage_path',
        'version',
        'generated_at',
        'signed_at',
        'signature_data',
        'sent_to_customer_at',
        'sent_to_owner_at',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'signed_at' => 'datetime',
        'sent_to_customer_at' => 'datetime',
        'sent_to_owner_at' => 'datetime',
        'signature_data' => 'array',
        'version' => 'integer',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
