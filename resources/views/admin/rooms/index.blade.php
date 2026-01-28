@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('title', __('admin.manage_rooms'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-12">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 sm:mb-8 gap-4">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ __('admin.manage_rooms') }}</h1>
        <a href="{{ route('admin.rooms.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors text-sm sm:text-base font-medium text-center">
            {{ __('admin.create_new_room') }}
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <p class="text-green-800">{{ session('success') }}</p>
    </div>
    @endif

    <!-- Date Filter Section -->
    <div class="bg-white shadow-lg rounded-xl p-5 sm:p-6 mb-6 border border-gray-100">
        <div class="flex items-center gap-2 mb-5">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            <h2 class="text-lg font-bold text-gray-900">{{ __('admin.filter_by_date') }}</h2>
        </div>
        <form method="GET" action="{{ route('admin.rooms.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="check_in" class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {{ __('admin.check_in_date') }}
                    </label>
                    <div class="relative">
                        <input type="date" 
                               name="check_in" 
                               id="check_in" 
                               value="{{ $checkIn ?? '' }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all shadow-sm hover:border-gray-400">
                    </div>
                </div>
                <div>
                    <label for="check_out" class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {{ __('admin.check_out_date') }}
                        <span class="text-gray-500 text-xs font-normal">({{ __('admin.optional') }})</span>
                    </label>
                    <div class="relative">
                        <input type="date" 
                               name="check_out" 
                               id="check_out" 
                               value="{{ $checkOut ?? '' }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all shadow-sm hover:border-gray-400">
                    </div>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <button type="submit" class="flex items-center justify-center gap-2 bg-blue-600 text-white py-4 px-6 rounded-lg hover:bg-blue-700 transition-all duration-200 font-semibold text-sm shadow-md hover:shadow-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    {{ __('admin.filter') }}
                </button>
                @if($checkIn ?? $checkOut)
                <a href="{{ route('admin.rooms.index') }}" 
                   class="flex items-center justify-center gap-2 bg-gray-100 text-gray-700 py-2.5 px-6 rounded-lg hover:bg-gray-200 transition-all duration-200 text-sm font-semibold border border-gray-300 shadow-sm hover:shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    {{ __('admin.clear') }}
                </a>
                @endif
            </div>
        </form>
        @if($checkIn ?? false)
        <div class="mt-5 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <p class="text-sm font-semibold text-blue-900 mb-1">{{ __('admin.filtering_by_date') }}</p>
                    <div class="flex flex-wrap items-center gap-2 text-sm text-blue-800">
                        <span class="flex items-center gap-1">
                            <span class="font-medium">{{ __('admin.check_in') }}:</span>
                            <span class="px-2 py-0.5 bg-blue-100 rounded font-semibold">{{ \Carbon\Carbon::parse($checkIn)->format('d.m.Y') }}</span>
                        </span>
                        @if($checkOut)
                            <span class="text-blue-400">|</span>
                            <span class="flex items-center gap-1">
                                <span class="font-medium">{{ __('admin.check_out') }}:</span>
                                <span class="px-2 py-0.5 bg-blue-100 rounded font-semibold">{{ \Carbon\Carbon::parse($checkOut)->format('d.m.Y') }}</span>
                            </span>
                        @else
                            <span class="text-blue-400">|</span>
                            <span class="px-2 py-0.5 bg-blue-100 rounded font-semibold">{{ __('admin.long_term_rental') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Mobile Card View (phones/tablets) -->
    <div class="block lg:hidden space-y-4">
        @forelse($rooms as $room)
        <div class="bg-white shadow-md rounded-lg p-4 border border-gray-200">
            <div class="flex items-start gap-4 mb-4">
                @if($room->images->count() > 0)
                @php
                    $firstImage = $room->images->first();
                    $imageUrl = route('storage.serve', ['path' => $firstImage->path]);
                @endphp
                <img src="{{ $imageUrl }}" 
                     alt="{{ $room->name }}" 
                     class="h-16 w-16 rounded-lg object-cover flex-shrink-0"
                     onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'64\' height=\'64\'%3E%3Crect width=\'64\' height=\'64\' fill=\'%23e5e7eb\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%239ca3af\' font-size=\'10\'%3ENo Image%3C/text%3E%3C/svg%3E';">
                @else
                <div class="h-16 w-16 rounded-lg bg-gray-200 flex items-center justify-center flex-shrink-0">
                    <span class="text-xs text-gray-400 text-center px-1">{{ __('admin.no_image') }}</span>
                </div>
                @endif
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-semibold text-gray-900 truncate">{{ $room->name }}</h3>
                    <p class="text-xs text-gray-500 truncate mt-1">{{ $room->slug }}</p>
                </div>
            </div>
            <div class="space-y-2.5 mb-4">
                <div class="flex items-center text-sm">
                    <span class="font-medium text-gray-700 w-24">{{ __('admin.property') }}:</span>
                    <span class="text-gray-900">{{ $room->property->name }}</span>
                </div>
                <div class="flex items-center text-sm">
                    <span class="font-medium text-gray-700 w-24">{{ __('admin.location_house') }}:</span>
                    <span class="text-gray-900">
                        @if($room->house && $room->house->location)
                            {{ $room->house->location->name }} - {{ $room->house->name }}
                        @else
                            <span class="text-gray-400 italic">{{ __('admin.not_assigned') }}</span>
                        @endif
                    </span>
                </div>
                <div class="flex items-center text-sm">
                    <span class="font-medium text-gray-700 w-24">{{ __('admin.capacity') }}:</span>
                    <span class="text-gray-900">{{ $room->capacity }}</span>
                </div>
                <div class="text-sm">
                    <span class="font-medium text-gray-700 block mb-1.5">{{ __('admin.price') }}:</span>
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">{{ __('admin.short_term') }}:</span>
                            <span class="font-semibold text-gray-900">€{{ number_format($room->base_price, 2) }}/{{ __('booking.night') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">{{ __('admin.long_term') }}:</span>
                            <span class="font-semibold text-gray-900">€{{ number_format($room->monthly_price ?? 700, 2) }}/{{ __('booking.month') }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center text-sm">
                    <span class="font-medium text-gray-700 w-24">{{ __('admin.short_term') }}:</span>
                    @if($room->short_term_allowed)
                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">{{ __('admin.yes') }}</span>
                    @else
                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ __('admin.no') }}</span>
                    @endif
                </div>
            </div>
            <div class="flex gap-2 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.rooms.show', $room) }}" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors text-center text-sm font-medium">
                    {{ __('admin.view') }}
                </a>
                <a href="{{ route('admin.rooms.edit', $room) }}" class="flex-1 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors text-center text-sm font-medium">
                    {{ __('admin.edit') }}
                </a>
                <form action="{{ route('admin.rooms.destroy', $room) }}" method="POST" class="flex-1" onsubmit="return confirm('{{ __('admin.are_you_sure_delete_room') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full text-red-600 hover:text-red-900 font-medium px-4 py-2 border border-red-300 rounded-md hover:bg-red-50 transition-colors text-sm">
                        {{ __('admin.delete') }}
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="bg-white shadow-md rounded-lg p-6 text-center text-gray-500">
            {{ __('admin.no_rooms_found') }}
        </div>
        @endforelse
    </div>

    <!-- Desktop Table View (lg and up) -->
    <div class="hidden lg:block bg-white shadow-md rounded-lg overflow-x-auto">
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
                <tr class="hover:bg-gray-50 transition-colors duration-150">
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
                                <div class="text-xs text-gray-500 mt-1">{{ $room->slug }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $room->property->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        @if($room->house && $room->house->location)
                            <div class="text-sm font-medium text-gray-900">{{ $room->house->location->name }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">{{ $room->house->name }}</div>
                        @else
                            <span class="text-sm text-gray-400 italic">{{ __('admin.not_assigned') }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $room->capacity }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-500">{{ __('admin.short_term') }}:</span>
                                <span class="font-semibold text-gray-900">€{{ number_format($room->base_price, 2) }}/{{ __('booking.night') }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-500">{{ __('admin.long_term') }}:</span>
                                <span class="font-semibold text-gray-900">€{{ number_format($room->monthly_price ?? 700, 2) }}/{{ __('booking.month') }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($room->short_term_allowed)
                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">{{ __('admin.yes') }}</span>
                        @else
                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ __('admin.no') }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.rooms.show', $room) }}" class="text-blue-600 hover:text-blue-800 transition-colors">{{ __('admin.view') }}</a>
                            <span class="text-gray-300">|</span>
                            <a href="{{ route('admin.rooms.edit', $room) }}" class="text-green-600 hover:text-green-800 transition-colors">{{ __('admin.edit') }}</a>
                            <span class="text-gray-300">|</span>
                            <form action="{{ route('admin.rooms.destroy', $room) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('admin.are_you_sure_delete_room') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 transition-colors">{{ __('admin.delete') }}</button>
                            </form>
                        </div>
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


