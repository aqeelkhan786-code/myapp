<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'start_at',
        'end_at',
        'source',
        'status',
        'guest_first_name',
        'guest_last_name',
        'job',
        'language',
        'communication_preference',
        'email',
        'phone',
        'renter_address',
        'renter_postal_code',
        'renter_city',
        'notes',
        'is_short_term',
        'total_amount',
        'paid_amount',
        'payment_status',
        'stripe_payment_intent_id',
        'external_uid',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'is_short_term' => 'boolean',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function paymentLogs(): HasMany
    {
        return $this->hasMany(PaymentLog::class)->orderBy('created_at', 'desc');
    }

    public function getGuestFullNameAttribute(): string
    {
        return "{$this->guest_first_name} {$this->guest_last_name}";
    }

    /**
     * Convert booking language to locale code
     * "Deutsch" -> "de", "Englisch" -> "en"
     */
    public function getLocaleFromLanguage(): string
    {
        return match(strtolower($this->language ?? '')) {
            'deutsch' => 'de',
            'englisch' => 'en',
            default => 'en', // Default to English
        };
    }
}
