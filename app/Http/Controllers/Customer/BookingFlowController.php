<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\House;
use App\Models\Room;
use Illuminate\Http\Request;

class BookingFlowController extends Controller
{
    /**
     * Show booking home page with two big buttons
     */
    public function home()
    {
        return view('booking-flow.home');
    }

    /**
     * Show locations page
     */
    public function locations()
    {
        $locations = Location::orderBy('sort_order')->get();
        return view('booking-flow.locations', compact('locations'));
    }

    /**
     * Show house page for a location with rooms directly
     */
    public function house(Location $location)
    {
        $house = $location->houses()->first(); // One house per location
        if (!$house) {
            abort(404, 'House not found for this location');
        }
        
        // Get rooms assigned to this house
        $rooms = $house->rooms()->with('images')->get();
        
        // If no rooms are assigned to the house, get all available rooms as fallback
        if ($rooms->isEmpty()) {
            $rooms = Room::with('images')->get();
        }
        
        // Get first available room for booking button
        $availableRoom = $rooms->isNotEmpty() ? $rooms->first() : null;
        
        return view('booking-flow.house', compact('location', 'house', 'rooms', 'availableRoom'));
    }

    /**
     * Show apartments page for a house (redirects to house page)
     */
    public function apartments(House $house)
    {
        // Redirect to house page which now shows rooms directly
        return redirect()->route('booking-flow.house', $house->location);
    }

    /**
     * Show room details page (this will redirect to existing booking.show)
     */
    public function roomDetails(Room $room)
    {
        // Redirect to existing booking flow
        return redirect()->route('booking.show', $room);
    }

    /**
     * Show search/filter page with date picker and filtered rooms
     */
    public function search(Location $location, House $house, Request $request)
    {
        // Get all rooms for this house
        $rooms = $house->rooms()->with('images', 'property')->get();
        
        // Get filter parameters
        $checkIn = $request->get('check_in');
        $checkOut = $request->get('check_out');
        
        $filteredRooms = $rooms;
        
        // Filter by availability if dates are provided
        if ($checkIn && $checkOut) {
            try {
                $startAt = \Carbon\Carbon::parse($checkIn)->setTimezone('Europe/Berlin')->startOfDay();
                $endAt = \Carbon\Carbon::parse($checkOut)->setTimezone('Europe/Berlin')->startOfDay();
                
                // Get room IDs that have confirmed bookings for these dates
                $unavailableRoomIds = \App\Models\Booking::where('status', 'confirmed')
                    ->where(function ($q) use ($startAt, $endAt) {
                        $q->where(function ($q2) use ($startAt, $endAt) {
                            $q2->where('start_at', '<', $endAt->utc())
                               ->where('end_at', '>', $startAt->utc());
                        });
                    })
                    ->pluck('room_id')
                    ->unique();
                
                // Filter available rooms
                $filteredRooms = $rooms->filter(function ($room) use ($unavailableRoomIds) {
                    return !$unavailableRoomIds->contains($room->id);
                });
            } catch (\Exception $e) {
                // Invalid dates, show all rooms
            }
        }
        
        // Get blocked dates for JavaScript calendar
        $blockedDates = \App\Models\Booking::where('status', 'confirmed')
            ->whereHas('room', function($q) use ($house) {
                $q->where('house_id', $house->id);
            })
            ->get()
            ->map(function($booking) {
                return [
                    \Carbon\Carbon::parse($booking->start_at)->format('Y-m-d'),
                    \Carbon\Carbon::parse($booking->end_at)->format('Y-m-d')
                ];
            })
            ->toArray();
        
        return view('booking-flow.search', compact('location', 'house', 'rooms', 'filteredRooms', 'checkIn', 'checkOut', 'blockedDates'));
    }
}
