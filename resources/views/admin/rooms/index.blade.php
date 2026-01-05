@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('title', __('admin.manage_rooms'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('admin.manage_rooms') }}</h1>
        <a href="{{ route('admin.rooms.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
            {{ __('admin.create_new_room') }}
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <p class="text-green-800">{{ session('success') }}</p>
    </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.room') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.property') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.location_house') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.capacity') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.price') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.short_term') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($rooms as $room)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            @if($room->images->count() > 0)
                            @php
                                $firstImage = $room->images->first();
                                // Use storage route which works regardless of symlink
                                $imageUrl = route('storage.serve', ['path' => $firstImage->path]);
                            @endphp
                            <img src="{{ $imageUrl }}" 
                                 alt="{{ $room->name }}" 
                                 class="h-10 w-10 rounded-lg object-cover mr-3"
                                 onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'40\' height=\'40\'%3E%3Crect width=\'40\' height=\'40\' fill=\'%23e5e7eb\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%239ca3af\' font-size=\'10\'%3ENo Image%3C/text%3E%3C/svg%3E';">
                            @else
                            <div class="h-10 w-10 rounded-lg bg-gray-200 flex items-center justify-center mr-3">
                                <span class="text-xs text-gray-400">{{ __('admin.no_image') }}</span>
                            </div>
                            @endif
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $room->name }}</div>
                                <div class="text-sm text-gray-500">{{ $room->slug }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $room->property->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        @if($room->house && $room->house->location)
                            <div class="text-sm font-medium">{{ $room->house->location->name }}</div>
                            <div class="text-xs text-gray-500">{{ $room->house->name }}</div>
                        @else
                            <span class="text-gray-400 italic">{{ __('admin.not_assigned') }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $room->capacity }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">€{{ number_format($room->base_price, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($room->short_term_allowed)
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">{{ __('admin.yes') }}</span>
                        @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ __('admin.no') }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('admin.rooms.show', $room) }}" class="text-blue-600 hover:text-blue-900 mr-3">{{ __('admin.view') }}</a>
                        <a href="{{ route('admin.rooms.edit', $room) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">{{ __('admin.edit') }}</a>
                        <form action="{{ route('admin.rooms.destroy', $room) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('admin.are_you_sure_delete_room') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">{{ __('admin.delete') }}</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">{{ __('admin.no_rooms_found') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection


