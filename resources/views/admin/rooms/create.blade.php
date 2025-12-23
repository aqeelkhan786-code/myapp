@extends('layouts.app')

@section('title', __('admin.create_room'))

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('admin.create_new_room') }}</h1>

    <form action="{{ route('admin.rooms.store') }}" method="POST" class="bg-white shadow-md rounded-lg p-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="property_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.property') }} *</label>
                <select name="property_id" id="property_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('admin.select_property') }}</option>
                    @foreach($properties as $property)
                        <option value="{{ $property->id }}" {{ old('property_id') == $property->id ? 'selected' : '' }}>
                            {{ $property->name }}
                        </option>
                    @endforeach
                </select>
                @error('property_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="location_id" class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                <select name="location_id" id="location_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Location (Optional)</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
                @error('location_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="house_id" class="block text-sm font-medium text-gray-700 mb-2">House</label>
                <select name="house_id" id="house_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select House (Optional)</option>
                    @foreach($houses as $house)
                        <option value="{{ $house->id }}" 
                                data-location-id="{{ $house->location_id }}"
                                {{ old('house_id') == $house->id ? 'selected' : '' }}>
                            {{ $house->location->name ?? 'N/A' }} - {{ $house->name }}
                        </option>
                    @endforeach
                </select>
                @error('house_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Select a location first to filter houses</p>
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.room_name') }} *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="capacity" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.capacity') }} *</label>
                <input type="number" name="capacity" id="capacity" value="{{ old('capacity', 1) }}" required min="1"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('capacity')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="base_price" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.base_price_short_term') }} *</label>
                <input type="number" name="base_price" id="base_price" value="{{ old('base_price') }}" required step="0.01" min="0"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('base_price')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="monthly_price" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.monthly_price_long_term') }}</label>
                <input type="number" name="monthly_price" id="monthly_price" value="{{ old('monthly_price', 700) }}" step="0.01" min="0"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('monthly_price')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">{{ __('admin.default_monthly_price') }}</p>
            </div>
        </div>

        <div class="mb-6">
            <div class="flex items-center">
                <input type="checkbox" name="short_term_allowed" id="short_term_allowed" value="1" {{ old('short_term_allowed') ? 'checked' : '' }}
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="short_term_allowed" class="ml-2 block text-sm text-gray-900">
                    {{ __('admin.allow_short_term_bookings') }}
                </label>
            </div>
        </div>

        <div class="mb-6">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.description') }}</label>
            <textarea name="description" id="description" rows="4"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
            @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end gap-4">
            <a href="{{ route('admin.rooms.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-300 transition-colors">
                {{ __('admin.cancel') }}
            </a>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                {{ __('admin.create_room') }}
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const locationSelect = document.getElementById('location_id');
        const houseSelect = document.getElementById('house_id');
        const allHouses = Array.from(houseSelect.options).slice(1); // Get all houses except the first option

        locationSelect.addEventListener('change', function() {
            const selectedLocationId = this.value;
            
            // Clear house selection
            houseSelect.innerHTML = '<option value="">Select House (Optional)</option>';
            
            if (selectedLocationId) {
                // Filter houses by location
                allHouses.forEach(function(option) {
                    if (option.dataset.locationId === selectedLocationId) {
                        houseSelect.appendChild(option.cloneNode(true));
                    }
                });
            } else {
                // Show all houses if no location selected
                allHouses.forEach(function(option) {
                    houseSelect.appendChild(option.cloneNode(true));
                });
            }
        });

        // Trigger change on page load if location is pre-selected
        if (locationSelect.value) {
            locationSelect.dispatchEvent(new Event('change'));
        }
    });
</script>
@endpush
@endsection


