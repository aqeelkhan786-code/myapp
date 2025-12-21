<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class LocationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of locations
     */
    public function index()
    {
        $locations = Location::withCount('houses')->orderBy('sort_order')->get();
        return view('admin.locations.index', compact('locations'));
    }

    /**
     * Show the form for creating a new location
     */
    public function create()
    {
        return view('admin.locations.create');
    }

    /**
     * Store a newly created location
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:10240', // 10MB max
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;
        
        // Ensure slug is unique
        while (Location::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $data = [
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
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
                $path = 'location-images/' . $filename;
                
                // Save resized image
                $img->save(storage_path('app/public/' . $path), quality: 85);
                $data['image'] = $path;
            } catch (\Exception $e) {
                // Fallback: save original image if resize fails
                \Log::warning('Image resize failed, saving original', ['error' => $e->getMessage()]);
                $filename = uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('location-images', $filename, 'public');
                $data['image'] = $path;
            }
        }

        Location::create($data);

        return redirect()->route('admin.locations.index')
            ->with('success', __('admin.location_created_successfully'));
    }

    /**
     * Display the specified location
     */
    public function show(Location $location)
    {
        $location->load('houses');
        return view('admin.locations.show', compact('location'));
    }

    /**
     * Show the form for editing the specified location
     */
    public function edit(Location $location)
    {
        return view('admin.locations.edit', compact('location'));
    }

    /**
     * Update the specified location
     */
    public function update(Request $request, Location $location)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:10240', // 10MB max
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;
        
        // Ensure slug is unique (excluding current location)
        while (Location::where('slug', $slug)->where('id', '!=', $location->id)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $data = [
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($location->image && Storage::disk('public')->exists($location->image)) {
                Storage::disk('public')->delete($location->image);
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
                $path = 'location-images/' . $filename;
                
                // Save resized image
                $img->save(storage_path('app/public/' . $path), quality: 85);
                $data['image'] = $path;
            } catch (\Exception $e) {
                // Fallback: save original image if resize fails
                \Log::warning('Image resize failed, saving original', ['error' => $e->getMessage()]);
                $filename = uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('location-images', $filename, 'public');
                $data['image'] = $path;
            }
        }

        $location->update($data);

        return redirect()->route('admin.locations.index')
            ->with('success', __('admin.location_updated_successfully'));
    }

    /**
     * Remove the specified location
     */
    public function destroy(Location $location)
    {
        // Check if location has houses
        if ($location->houses()->count() > 0) {
            return redirect()->route('admin.locations.index')
                ->withErrors(['error' => __('admin.cannot_delete_location_with_houses')]);
        }

        // Delete image if exists
        if ($location->image && Storage::disk('public')->exists($location->image)) {
            Storage::disk('public')->delete($location->image);
        }

        $location->delete();
        return redirect()->route('admin.locations.index')
            ->with('success', __('admin.location_deleted_successfully'));
    }
}

