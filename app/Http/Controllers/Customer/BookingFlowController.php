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
     * Show house page for a location
     */
    public function house(Location $location)
    {
        $house = $location->houses()->first(); // One house per location
        if (!$house) {
            abort(404, 'House not found for this location');
        }
        return view('booking-flow.house', compact('location', 'house'));
    }

    /**
     * Show apartments page for a house
     */
    public function apartments(House $house)
    {
        $apartments = $house->rooms()->with('images')->get();
        return view('booking-flow.apartments', compact('house', 'apartments'));
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
