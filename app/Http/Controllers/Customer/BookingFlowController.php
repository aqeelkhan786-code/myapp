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
     * Show houses page for a location
     */
    public function house(Location $location)
    {
        // Get all houses for this location with images and room counts
        $houses = $location->houses()->with(['images', 'rooms'])->withCount('rooms')->get();
        
        if ($houses->isEmpty()) {
            abort(404, 'No houses found for this location');
        }
        
        // Get first house with rooms for the book button
        $firstHouseWithRooms = $houses->first(function($house) {
            return $house->rooms->isNotEmpty();
        });
        
        // Prepare house images data for JavaScript
        $houseImagesData = $houses->flatMap(function($house) {
            $images = [];
            if ($house->images && $house->images->count() > 0) {
                foreach ($house->images as $img) {
                    $images[] = [
                        'id' => $img->id,
                        'path' => asset('storage/' . $img->path),
                        'house_id' => $house->id,
                        'house_name' => $house->name
                    ];
                }
            } elseif ($house->image) {
                $images[] = [
                    'id' => 'single',
                    'path' => asset('storage/' . $house->image),
                    'house_id' => $house->id,
                    'house_name' => $house->name
                ];
            }
            return $images;
        })->values();
        
        return view('booking-flow.house', compact('location', 'houses', 'firstHouseWithRooms', 'houseImagesData'));
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
        $rooms = $house->rooms()->with('images', 'property', 'house')->get();
        
        // Get filter parameters
        $checkIn = $request->get('check_in');
        $checkOut = $request->get('check_out');
        
        // Normalize empty check_out to null for consistent handling
        if (empty($checkOut) || trim($checkOut) === '') {
            $checkOut = null;
        }
        
        $filteredRooms = $rooms;
        
        // Filter by availability if check-in date is provided
        if ($checkIn) {
            try {
                $startAt = \Carbon\Carbon::parse($checkIn)->setTimezone('Europe/Berlin')->startOfDay();
                
                if ($checkOut) {
                    // Both dates provided - check availability for date range
                    $endAt = \Carbon\Carbon::parse($checkOut)->setTimezone('Europe/Berlin')->startOfDay();
                    
                    // Get room IDs that have confirmed bookings overlapping [startAt, endAt]
                    // Include long-term bookings (end_at = null) as conflicts
                    $unavailableRoomIds = \App\Models\Booking::where('status', 'confirmed')
                        ->where(function ($q) use ($startAt, $endAt) {
                            $q->where(function ($q2) use ($startAt, $endAt) {
                                $q2->where('start_at', '<', $endAt->utc())
                                   ->where(function ($q3) use ($startAt) {
                                       $q3->where('end_at', '>', $startAt->utc())
                                          ->orWhereNull('end_at');
                                   });
                            });
                        })
                        ->pluck('room_id')
                        ->unique();
                    
                    // Filter available rooms
                    $filteredRooms = $rooms->filter(function ($room) use ($unavailableRoomIds) {
                        return !$unavailableRoomIds->contains($room->id);
                    });
                } else {
                    // Only check-in provided - check if room is available on that date (for long-term rentals)
                    // Include long-term bookings (end_at = null) as conflicts
                    $unavailableRoomIds = \App\Models\Booking::where('status', 'confirmed')
                        ->where(function ($q) use ($startAt) {
                            $q->where('start_at', '<=', $startAt->utc())
                              ->where(function ($q2) use ($startAt) {
                                  $q2->where('end_at', '>', $startAt->utc())
                                     ->orWhereNull('end_at');
                              });
                        })
                        ->pluck('room_id')
                        ->unique();
                    
                    // Filter available rooms
                    $filteredRooms = $rooms->filter(function ($room) use ($unavailableRoomIds) {
                        return !$unavailableRoomIds->contains($room->id);
                    });
                }
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
        
        // Prepare room data for modal (with amenities)
        $roomsDataForModal = $filteredRooms->map(function($room) {
            $amenitiesText = $room->amenities_text ?? ($room->house ? $room->house->amenities_text : null);
            $isDe = app()->getLocale() === 'de';
            $defaultAmenities = $isDe ? [
                __('booking_flow.amenity_large_beds'),
                __('booking_flow.amenity_fast_wifi'),
                __('booking_flow.amenity_weekly_cleaning'),
                __('booking_flow.amenity_smart_tv'),
                __('booking_flow.amenity_prices_included'),
                __('booking_flow.amenity_washer_dryer'),
                __('booking_flow.amenity_central_location'),
                __('booking_flow.amenity_fully_equipped_kitchen'),
                __('booking_flow.amenity_parking'),
            ] : [
                __('booking_flow.amenity_large_beds'),
                __('booking_flow.amenity_fast_wifi'),
                __('booking_flow.amenity_weekly_cleaning'),
                __('booking_flow.amenity_smart_tv'),
                __('booking_flow.amenity_prices_included'),
                __('booking_flow.amenity_washer_dryer'),
                __('booking_flow.amenity_central_location'),
                __('booking_flow.amenity_fully_equipped_kitchen'),
                __('booking_flow.amenity_parking'),
            ];
            
            if ($amenitiesText) {
                $amenities = array_filter(array_map('trim', explode("\n", $amenitiesText)));
                // Remove emojis from amenities
                $amenities = array_map(function($item) {
                    return preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', $item);
                }, $amenities);
            } else {
                $amenities = $defaultAmenities;
            }
            
            return [
                'id' => $room->id,
                'name' => $room->name,
                'amenities' => array_values($amenities),
            ];
        });
        
        return view('booking-flow.search', compact('location', 'house', 'rooms', 'filteredRooms', 'checkIn', 'checkOut', 'blockedDates', 'roomsDataForModal'));
    }
}
