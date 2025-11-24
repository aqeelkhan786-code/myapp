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

        // Check for conflicts
        $conflicts = [];
        $allBookings = Booking::where('status', 'confirmed')
            ->where('start_at', '>=', $today->startOfDay()->utc())
            ->with(['room'])
            ->get();

        foreach ($allBookings as $booking) {
            $conflicting = Booking::where('id', '!=', $booking->id)
                ->where('room_id', $booking->room_id)
                ->where('status', 'confirmed')
                ->where(function ($q) use ($booking) {
                    $q->where(function ($q2) use ($booking) {
                        $q2->where('start_at', '<', $booking->end_at)
                           ->where('end_at', '>', $booking->start_at);
                    });
                })
                ->first();

            if ($conflicting) {
                $conflicts[] = [
                    'booking1' => $booking,
                    'booking2' => $conflicting,
                ];
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
