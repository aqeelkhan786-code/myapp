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
}
