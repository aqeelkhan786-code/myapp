<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Property;
use App\Models\House;
use App\Models\Location;
use App\Models\IcalFeed;
use App\Services\IcalService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    protected $icalService;

    public function __construct(IcalService $icalService)
    {
        $this->middleware('auth');
        $this->icalService = $icalService;
    }

    /**
     * Display a listing of rooms
     */
    public function index(Request $request)
    {
        $query = Room::with(['property', 'house.location', 'images']);
        
        // Filter by availability if dates are provided
        $checkIn = $request->get('check_in');
        $checkOut = $request->get('check_out');
        
        if ($checkIn) {
            try {
                $startAt = \Carbon\Carbon::parse($checkIn)->setTimezone('Europe/Berlin')->startOfDay();
                
                if ($checkOut && trim($checkOut) !== '') {
                    // Both dates provided - check availability for date range
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
                    $query->whereNotIn('id', $unavailableRoomIds);
                } else {
                    // Only check-in provided - check if room is available on that date (for long-term rentals)
                    $unavailableRoomIds = \App\Models\Booking::where('status', 'confirmed')
                        ->where(function ($q) use ($startAt) {
                            $q->where('start_at', '<=', $startAt->utc())
                              ->where(function($q2) use ($startAt) {
                                  $q2->whereNull('end_at')
                                     ->orWhere('end_at', '>', $startAt->utc());
                              });
                        })
                        ->pluck('room_id')
                        ->unique();
                    
                    // Filter available rooms
                    $query->whereNotIn('id', $unavailableRoomIds);
                }
            } catch (\Exception $e) {
                // Invalid dates, show all rooms
            }
        }
        
        $rooms = $query->get();
        return view('admin.rooms.index', compact('rooms', 'checkIn', 'checkOut'));
    }

    /**
     * Show the form for creating a new room
     */
    public function create()
    {
        $properties = Property::all();
        $locations = Location::orderBy('sort_order')->get();
        $houses = House::with('location')->get();
        return view('admin.rooms.create', compact('properties', 'locations', 'houses'));
    }

    /**
     * Store a newly created room
     */
    public function store(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'house_id' => 'nullable|exists:houses,id',
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'base_price' => 'required|numeric|min:0',
            'monthly_price' => 'nullable|numeric|min:0',
            'short_term_allowed' => 'boolean',
            'description' => 'nullable|string',
            'amenities_text' => 'nullable|string',
        ]);

        $room = Room::create([
            'property_id' => $request->property_id,
            'house_id' => $request->house_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'capacity' => $request->capacity ?? 1,
            'base_price' => $request->base_price,
            'monthly_price' => $request->monthly_price ?? 700.00,
            'short_term_allowed' => $request->has('short_term_allowed'),
            'description' => $request->description,
            'amenities_text' => $request->amenities_text,
        ]);

        return redirect()->route('admin.rooms.index')
            ->with('success', __('admin.room_created_successfully'));
    }

    /**
     * Display the specified room
     */
    public function show(Room $room)
    {
        $room->load(['property', 'images', 'icalFeeds', 'blackoutDates']);
        return view('admin.rooms.show', compact('room'));
    }

    /**
     * Upload image(s) for room (supports bulk upload with auto-resize)
     */
    public function uploadImage(Request $request, Room $room)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|max:10240', // 10MB max per image
        ]);

        $maxSortOrder = $room->images()->max('sort_order') ?? 0;
        $uploaded = 0;
        
        // Ensure directory exists
        Storage::disk('public')->makeDirectory('room-images');
        
        foreach ($request->file('images') as $image) {
            // Generate unique filename
            $filename = uniqid() . '.' . $image->getClientOriginalExtension();
            $path = 'room-images/' . $filename;
            
            // Resize image to max 1920x1080 while maintaining aspect ratio
            // Use GD driver if Imagick is not available
            try {
                if (extension_loaded('imagick')) {
                    $manager = \Intervention\Image\ImageManager::imagick();
                } else {
                    $manager = \Intervention\Image\ImageManager::gd();
                }
                
                $img = $manager->read($image->getRealPath());
                
                // Resize only if larger than max dimensions
                $maxWidth = 1920;
                $maxHeight = 1080;
                
                if ($img->width() > $maxWidth || $img->height() > $maxHeight) {
                    $img->scaleDown($maxWidth, $maxHeight);
                }
                
                // Save resized image using Storage facade for consistency
                $img->save(Storage::disk('public')->path($path), quality: 85);
            } catch (\Exception $e) {
                // Fallback: save original image if resize fails
                \Log::warning('Image resize failed, saving original', ['error' => $e->getMessage()]);
                $path = $image->storeAs('room-images', $filename, 'public');
            }
            
            // Verify file exists before saving to database
            if (Storage::disk('public')->exists($path)) {
                $maxSortOrder++;
                $room->images()->create([
                    'path' => $path,
                    'sort_order' => $maxSortOrder,
                ]);
                $uploaded++;
            } else {
                \Log::error('Image file not found after upload', ['path' => $path]);
            }
        }

        $message = $uploaded === 1 ? 'Image uploaded successfully.' : "{$uploaded} images uploaded successfully.";
        return back()->with('success', $message);
    }

    /**
     * Update image sort order
     */
    public function updateImageOrder(Request $request, Room $room)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'exists:room_images,id',
        ]);

        foreach ($request->images as $index => $imageId) {
            $room->images()->where('id', $imageId)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Delete image
     */
    public function deleteImage(Room $room, $imageId)
    {
        $image = $room->images()->findOrFail($imageId);
        
        if (Storage::disk('public')->exists($image->path)) {
            Storage::disk('public')->delete($image->path);
        }
        
        $image->delete();

        return back()->with('success', 'Image deleted successfully.');
    }

    /**
     * Show the form for editing the specified room
     */
    public function edit(Room $room)
    {
        $properties = Property::all();
        $locations = Location::orderBy('sort_order')->get();
        $houses = House::with('location')->get();
        $room->load(['icalFeeds']);
        return view('admin.rooms.edit', compact('room', 'properties', 'locations', 'houses'));
    }

    /**
     * Update the specified room
     */
    public function update(Request $request, Room $room)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'house_id' => 'nullable|exists:houses,id',
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'base_price' => 'required|numeric|min:0',
            'monthly_price' => 'nullable|numeric|min:0',
            'short_term_allowed' => 'boolean',
            'description' => 'nullable|string',
            'amenities_text' => 'nullable|string',
        ]);

        $room->update([
            'property_id' => $request->property_id,
            'house_id' => $request->house_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'capacity' => $request->capacity ?? 1,
            'base_price' => $request->base_price,
            'monthly_price' => $request->monthly_price ?? 700.00,
            'short_term_allowed' => $request->has('short_term_allowed'),
            'description' => $request->description,
            'amenities_text' => $request->amenities_text,
        ]);

        return redirect()->route('admin.rooms.index')
            ->with('success', __('admin.room_updated_successfully'));
    }

    /**
     * Remove the specified room
     */
    public function destroy(Room $room)
    {
        $room->delete();
        return redirect()->route('admin.rooms.index')
            ->with('success', __('admin.room_deleted_successfully'));
    }

    /**
     * Update iCal import URL
     */
    public function updateIcalImport(Request $request, Room $room)
    {
        $request->validate([
            'import_url' => 'nullable|url',
        ]);

        $feed = IcalFeed::firstOrNew([
            'room_id' => $room->id,
            'direction' => 'import',
        ]);

        $feed->url = $request->import_url;
        $feed->active = $request->has('import_active');
        $feed->save();

        return back()->with('success', 'Import URL updated successfully.');
    }

    /**
     * Generate or revoke export token
     */
    public function manageExportToken(Request $request, Room $room)
    {
        if ($request->action === 'generate') {
            $feed = IcalFeed::firstOrNew([
                'room_id' => $room->id,
                'direction' => 'export',
            ]);

            if (!$feed->token) {
                $feed->token = Str::random(32);
            }
            $feed->active = true;
            $feed->save();

            return back()->with('success', 'Export token generated successfully.');
        } elseif ($request->action === 'revoke') {
            $feed = IcalFeed::where('room_id', $room->id)
                ->where('direction', 'export')
                ->first();

            if ($feed) {
                $feed->token = null;
                $feed->active = false;
                $feed->save();
            }

            return back()->with('success', 'Export token revoked successfully.');
        }

        return back()->withErrors(['action' => 'Invalid action']);
    }

    /**
     * Sync iCal for a specific room
     */
    public function syncIcal(Room $room)
    {
        $importFeed = $room->icalImportFeed;
        
        if (!$importFeed || !$importFeed->active) {
            return back()->withErrors(['sync' => 'No active import feed configured for this room.']);
        }

        // Dispatch sync job to queue
        \App\Jobs\SyncIcalFeed::dispatch($importFeed, auth()->id());

        return back()->with('success', 'iCal sync has been queued. Please wait a moment and refresh to see results.');
    }
}
