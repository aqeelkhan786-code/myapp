<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of properties
     */
    public function index()
    {
        $properties = Property::withCount('rooms')->get();
        return view('admin.properties.index', compact('properties'));
    }

    /**
     * Show the form for creating a new property
     */
    public function create()
    {
        return view('admin.properties.create');
    }

    /**
     * Store a newly created property
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
        ]);

        Property::create([
            'name' => $request->name,
            'address' => $request->address,
            'city' => $request->city,
            'postal_code' => $request->postal_code,
        ]);

        return redirect()->route('admin.properties.index')
            ->with('success', __('admin.property_created_successfully'));
    }

    /**
     * Display the specified property
     */
    public function show(Property $property)
    {
        $property->load('rooms');
        return view('admin.properties.show', compact('property'));
    }

    /**
     * Show the form for editing the specified property
     */
    public function edit(Property $property)
    {
        return view('admin.properties.edit', compact('property'));
    }

    /**
     * Update the specified property
     */
    public function update(Request $request, Property $property)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
        ]);

        $property->update([
            'name' => $request->name,
            'address' => $request->address,
            'city' => $request->city,
            'postal_code' => $request->postal_code,
        ]);

        return redirect()->route('admin.properties.index')
            ->with('success', __('admin.property_updated_successfully'));
    }

    /**
     * Remove the specified property
     */
    public function destroy(Property $property)
    {
        // Check if property has rooms
        if ($property->rooms()->count() > 0) {
            return redirect()->route('admin.properties.index')
                ->withErrors(['error' => __('admin.cannot_delete_property_with_rooms')]);
        }

        $property->delete();
        return redirect()->route('admin.properties.index')
            ->with('success', __('admin.property_deleted_successfully'));
    }
}



