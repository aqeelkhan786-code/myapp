<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\House;
use App\Models\HouseImage;
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
            'image' => 'nullable|image|max:10240', // 10MB max
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
        $house->load(['location', 'rooms', 'images']);
        return view('admin.houses.show', compact('house'));
    }

    /**
     * Upload image(s) for house (supports bulk upload with auto-resize)
     */
    public function uploadImage(Request $request, House $house)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|max:10240', // 10MB max per image
        ]);

        $maxSortOrder = $house->images()->max('sort_order') ?? 0;
        $uploaded = 0;
        
        // Ensure directory exists
        Storage::disk('public')->makeDirectory('house-images');
        
        foreach ($request->file('images') as $image) {
            // Generate unique filename
            $filename = uniqid() . '.' . $image->getClientOriginalExtension();
            $path = 'house-images/' . $filename;
            
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
                $path = $image->storeAs('house-images', $filename, 'public');
            }
            
            // Verify file exists before saving to database
            if (Storage::disk('public')->exists($path)) {
                $maxSortOrder++;
                $house->images()->create([
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
    public function updateImageOrder(Request $request, House $house)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'exists:house_images,id',
        ]);

        foreach ($request->images as $index => $imageId) {
            $house->images()->where('id', $imageId)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Delete image
     */
    public function deleteImage(House $house, $imageId)
    {
        $image = $house->images()->findOrFail($imageId);
        
        if (Storage::disk('public')->exists($image->path)) {
            Storage::disk('public')->delete($image->path);
        }
        
        $image->delete();

        return back()->with('success', 'Image deleted successfully.');
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
            'image' => 'nullable|image|max:10240', // 10MB max
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











