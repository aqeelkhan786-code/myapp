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
    public function isAvailable(Room $room, Carbon $startAt, Carbon $endAt, ?int $excludeBookingId = null): bool
    {
        // Check for conflicting bookings
        $query = Booking::where('room_id', $room->id)
            ->where('status', 'confirmed')
            ->where(function ($q) use ($startAt, $endAt) {
                $q->where(function ($q2) use ($startAt, $endAt) {
                    // Check for overlapping bookings
                    $q2->where('start_at', '<', $endAt)
                       ->where('end_at', '>', $startAt);
                });
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        if ($query->count() > 0) {
            return false;
        }

        // Check for blackout dates (maintenance, etc.)
        $blackoutDates = \App\Models\BlackoutDate::where('room_id', $room->id)
            ->where('start_date', '<=', $endAt->format('Y-m-d'))
            ->where('end_date', '>=', $startAt->format('Y-m-d'))
            ->exists();

        return !$blackoutDates;
    }

    /**
     * Get conflicting bookings for a date range
     */
    public function getConflicts(Room $room, Carbon $startAt, Carbon $endAt, ?int $excludeBookingId = null): array
    {
        $query = Booking::where('room_id', $room->id)
            ->where('status', 'confirmed')
            ->where(function ($q) use ($startAt, $endAt) {
                $q->where(function ($q2) use ($startAt, $endAt) {
                    $q2->where('start_at', '<', $endAt)
                       ->where('end_at', '>', $startAt);
                });
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->get()->toArray();
    }

    /**
     * Calculate total amount for a booking
     */
    public function calculateTotal(Room $room, Carbon $startAt, Carbon $endAt): float
    {
        $nights = $startAt->diffInDays($endAt);
        return $room->base_price * $nights;
    }
}

