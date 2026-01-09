@extends('layouts.app')

@section('title', __('admin.create_house'))

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('admin.create_new_house') }}</h1>

    <form action="{{ route('admin.houses.store') }}" method="POST" enctype="multipart/form-data" class="bg-white shadow-md rounded-lg p-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="location_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.location') }} *</label>
                <select name="location_id" id="location_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('admin.select_location') }}</option>
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
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.house_name') }} *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="image" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.image') }}</label>
                <input type="file" name="image" id="image" accept="image/*"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="mt-1 text-xs text-gray-500">{{ __('admin.image_help_text') }}</p>
                @error('image')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.description') }}</label>
                <textarea name="description" id="description" rows="4"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="amenities_text" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.amenities_text') }} ({{ __('admin.amenities_text_help') }})</label>
                <textarea name="amenities_text" id="amenities_text" rows="10"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm"
                          placeholder="📶 Kostenloses WLAN – stabil und zuverlässig
🍳 Voll ausgestattete Gemeinschaftsküche – alles vorhanden, was man braucht
🛏️ Bequeme Betten – für einen erholsamen Schlaf
📺 TV in jedem Zimmer
🛋️ Gemeinschaftsbereiche – perfekt zum Entspannen am Abend
🚗 Parkmöglichkeiten – direkt am Haus oder in unmittelbarer Nähe
📍 Zentrale Lage – gute Anbindung an Einkaufsmöglichkeiten & ÖPNV
📅 Flexible Mietdauer – kurz- oder langfristig möglich">{{ old('amenities_text') }}</textarea>
                @error('amenities_text')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">{{ __('admin.amenities_text_note') }}</p>
            </div>

            <div class="md:col-span-2">
                <label for="button_text" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.button_text') }}</label>
                <input type="text" name="button_text" id="button_text" value="{{ old('button_text') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="{{ __('booking_flow.view_available_rooms') }}">
                @error('button_text')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">{{ __('admin.button_text_note') }}</p>
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <a href="{{ route('admin.houses.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-300 transition-colors">
                {{ __('admin.cancel') }}
            </a>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                {{ __('admin.create_house') }}
            </button>
        </div>
    </form>
</div>
@endsection










