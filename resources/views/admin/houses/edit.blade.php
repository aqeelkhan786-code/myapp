@extends('layouts.app')

@section('title', __('admin.edit_house'))

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('admin.edit_house') }}</h1>

    <form action="{{ route('admin.houses.update', $house) }}" method="POST" enctype="multipart/form-data" class="bg-white shadow-md rounded-lg p-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="location_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.location') }} *</label>
                <select name="location_id" id="location_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('admin.select_location') }}</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}" {{ old('location_id', $house->location_id) == $location->id ? 'selected' : '' }}>
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
                <input type="text" name="name" id="name" value="{{ old('name', $house->name) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="image" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.image') }}</label>
                @if($house->image)
                <div class="mb-3">
                    <img src="{{ asset('storage/' . $house->image) }}" alt="{{ $house->name }}" class="h-32 w-auto rounded-lg object-cover">
                    <p class="mt-1 text-xs text-gray-500">{{ __('admin.current_image') }}</p>
                </div>
                @endif
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
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $house->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <a href="{{ route('admin.houses.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-300 transition-colors">
                {{ __('admin.cancel') }}
            </a>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                {{ __('admin.save') }}
            </button>
        </div>
    </form>
</div>
@endsection

