<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->middleware('auth');
        $this->bookingService = $bookingService;
    }

    public function index()
    {
        $today = Carbon::today('Europe/Berlin');
        $nextWeek = $today->copy()->addWeek();
        $nextMonth = $today->copy()->addMonth();

        // Upcoming arrivals (next 7 days)
        $arrivals = Booking::with(['room', 'room.property'])
            ->where('status', 'confirmed')
            ->whereBetween('start_at', [
                $today->startOfDay()->utc(),
                $today->copy()->addDays(7)->endOfDay()->utc()
            ])
            ->orderBy('start_at', 'asc')
            ->get();

        // Check for conflicts (optimized to avoid N+1 queries)
        $conflicts = [];
        $allBookings = Booking::where('status', 'confirmed')
            ->where('start_at', '>=', $today->startOfDay()->utc())
            ->with(['room'])
            ->get();

        if ($allBookings->isNotEmpty()) {
            // Get all booking IDs and room IDs
            $bookingIds = $allBookings->pluck('id')->toArray();
            $roomIds = $allBookings->pluck('room_id')->unique()->toArray();

            // Fetch all potential conflicting bookings in a single query
            $potentialConflicts = Booking::where('status', 'confirmed')
                ->where('start_at', '>=', $today->startOfDay()->utc())
                ->whereIn('room_id', $roomIds)
                ->with(['room'])
                ->get()
                ->groupBy('room_id');

            // Check for conflicts efficiently
            foreach ($allBookings as $booking) {
                $roomBookings = $potentialConflicts->get($booking->room_id, collect());
                
                $conflicting = $roomBookings->first(function ($otherBooking) use ($booking) {
                    return $otherBooking->id !== $booking->id
                        && $otherBooking->start_at < $booking->end_at
                        && $otherBooking->end_at > $booking->start_at;
                });

                if ($conflicting) {
                    $conflicts[] = [
                        'booking1' => $booking,
                        'booking2' => $conflicting,
                    ];
                }
            }
        }

        // Stats
        $totalBookings = Booking::count();
        $confirmedBookings = Booking::where('status', 'confirmed')->count();
        $pendingBookings = Booking::where('status', 'pending')->count();
        $totalRevenue = Booking::where('payment_status', 'paid')->sum('paid_amount');

        return view('dashboard', compact(
            'arrivals',
            'conflicts',
            'totalBookings',
            'confirmedBookings',
            'pendingBookings',
            'totalRevenue'
        ));
    }
}
