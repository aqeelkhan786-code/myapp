<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\House;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class HouseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of houses
     */
    public function index()
    {
        $houses = House::with(['location', 'rooms'])->withCount('rooms')->get();
        return view('admin.houses.index', compact('houses'));
    }

    /**
     * Show the form for creating a new house
     */
    public function create()
    {
        $locations = Location::orderBy('sort_order')->get();
        return view('admin.houses.create', compact('locations'));
    }

    /**
     * Store a newly created house
     */
    public function store(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:5120', // 5MB max
        ]);

        $data = [
            'location_id' => $request->location_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            
            // Resize image if needed
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
                
                // Generate unique filename
                $filename = uniqid() . '.' . $image->getClientOriginalExtension();
                $path = 'house-images/' . $filename;
                
                // Save resized image
                $img->save(storage_path('app/public/' . $path), quality: 85);
                $data['image'] = $path;
            } catch (\Exception $e) {
                // Fallback: save original image if resize fails
                \Log::warning('Image resize failed, saving original', ['error' => $e->getMessage()]);
                $filename = uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('house-images', $filename, 'public');
                $data['image'] = $path;
            }
        }

        House::create($data);

        return redirect()->route('admin.houses.index')
            ->with('success', __('admin.house_created_successfully'));
    }

    /**
     * Display the specified house
     */
    public function show(House $house)
    {
        $house->load(['location', 'rooms']);
        return view('admin.houses.show', compact('house'));
    }

    /**
     * Show the form for editing the specified house
     */
    public function edit(House $house)
    {
        $locations = Location::orderBy('sort_order')->get();
        return view('admin.houses.edit', compact('house', 'locations'));
    }

    /**
     * Update the specified house
     */
    public function update(Request $request, House $house)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:5120', // 5MB max
        ]);

        $data = [
            'location_id' => $request->location_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($house->image && Storage::disk('public')->exists($house->image)) {
                Storage::disk('public')->delete($house->image);
            }

            $image = $request->file('image');
            
            // Resize image if needed
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
                
                // Generate unique filename
                $filename = uniqid() . '.' . $image->getClientOriginalExtension();
                $path = 'house-images/' . $filename;
                
                // Save resized image
                $img->save(storage_path('app/public/' . $path), quality: 85);
                $data['image'] = $path;
            } catch (\Exception $e) {
                // Fallback: save original image if resize fails
                \Log::warning('Image resize failed, saving original', ['error' => $e->getMessage()]);
                $filename = uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('house-images', $filename, 'public');
                $data['image'] = $path;
            }
        }

        $house->update($data);

        return redirect()->route('admin.houses.index')
            ->with('success', __('admin.house_updated_successfully'));
    }

    /**
     * Remove the specified house
     */
    public function destroy(House $house)
    {
        // Check if house has rooms
        if ($house->rooms()->count() > 0) {
            return redirect()->route('admin.houses.index')
                ->withErrors(['error' => __('admin.cannot_delete_house_with_rooms')]);
        }

        // Delete image if exists
        if ($house->image && Storage::disk('public')->exists($house->image)) {
            Storage::disk('public')->delete($house->image);
        }

        $house->delete();
        return redirect()->route('admin.houses.index')
            ->with('success', __('admin.house_deleted_successfully'));
    }
}

