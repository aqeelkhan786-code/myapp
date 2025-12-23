<?php

namespace App\Services;

use App\Models\Room;
use App\Models\Booking;
use Carbon\Carbon;

class BookingService
{
    /**
     * Check if a room is available for the given date range
     */
    public function isAvailable(Room $room, Carbon $startAt, ?Carbon $endAt = null, ?int $excludeBookingId = null): bool
    {
        // Check for conflicting bookings
        $query = Booking::where('room_id', $room->id)
            ->where('status', 'confirmed')
            ->where(function ($q) use ($startAt, $endAt) {
                if ($endAt) {
                    // Short-term: check for overlapping dates
                    $q->where(function ($q2) use ($startAt, $endAt) {
                        $q2->where('start_at', '<', $endAt)
                           ->where(function ($q3) use ($startAt) {
                               $q3->where('end_at', '>', $startAt)
                                  ->orWhereNull('end_at'); // Long-term bookings conflict with any date range
                           });
                    });
                } else {
                    // Long-term: check if any booking overlaps with start date
                    $q->where(function ($q2) use ($startAt) {
                        $q2->where('start_at', '<=', $startAt)
                           ->where(function ($q3) use ($startAt) {
                               $q3->where('end_at', '>', $startAt)
                                  ->orWhereNull('end_at');
                           });
                    });
                }
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        if ($query->count() > 0) {
            return false;
        }

        // Check for blackout dates (maintenance, etc.) - only if end_at is provided
        if ($endAt) {
            $blackoutDates = \App\Models\BlackoutDate::where('room_id', $room->id)
                ->where('start_date', '<=', $endAt->format('Y-m-d'))
                ->where('end_date', '>=', $startAt->format('Y-m-d'))
                ->exists();

            return !$blackoutDates;
        }

        // For long-term bookings, check if start date falls within any blackout period
        $blackoutDates = \App\Models\BlackoutDate::where('room_id', $room->id)
            ->where('start_date', '<=', $startAt->format('Y-m-d'))
            ->where('end_date', '>=', $startAt->format('Y-m-d'))
            ->exists();

        return !$blackoutDates;
    }

    /**
     * Get conflicting bookings for a date range
     */
    public function getConflicts(Room $room, Carbon $startAt, ?Carbon $endAt = null, ?int $excludeBookingId = null): array
    {
        $query = Booking::where('room_id', $room->id)
            ->where('status', 'confirmed')
            ->where(function ($q) use ($startAt, $endAt) {
                if ($endAt) {
                    // Short-term: check for overlapping dates
                    $q->where(function ($q2) use ($startAt, $endAt) {
                        $q2->where('start_at', '<', $endAt)
                           ->where(function ($q3) use ($startAt) {
                               $q3->where('end_at', '>', $startAt)
                                  ->orWhereNull('end_at'); // Long-term bookings conflict with any date range
                           });
                    });
                } else {
                    // Long-term: check if any booking overlaps with start date
                    $q->where(function ($q2) use ($startAt) {
                        // Conflicting if: booking starts before or on our start date and either has no end or ends after our start
                        $q2->where('start_at', '<=', $startAt)
                           ->where(function ($q3) use ($startAt) {
                               $q3->where('end_at', '>', $startAt)
                                  ->orWhereNull('end_at');
                           });
                    });
                }
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->get()->toArray();
    }

    /**
     * Calculate total amount for a booking
     */
    public function calculateTotal(Room $room, Carbon $startAt, ?Carbon $endAt = null): float
    {
        // Long-term rental: no end date or end date is more than 30 days away
        if (!$endAt) {
            // Long-term rental - return monthly price
            return $room->monthly_price ?? 700.00;
        }
        
        $nights = $startAt->diffInDays($endAt);
        
        // If more than 30 nights, it's considered long-term, use monthly price
        if ($nights > 30) {
            $months = ceil($nights / 30);
            return ($room->monthly_price ?? 700.00) * $months;
        }
        
        // Short-term rental - use nightly price
        return $room->base_price * $nights;
    }
}

