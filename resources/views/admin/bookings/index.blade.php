@extends('layouts.app')

@section('title', __('admin.manage_bookings'))

@push('styles')
<style>
/* Desktop (default): table visible, cards hidden. Mobile (<768px): cards visible, table hidden. */
.bookings-mobile-cards { display: none; }
.bookings-desktop-table { display: block; }
@media (max-width: 767px) {
  .bookings-mobile-cards { display: block !important; }
  .bookings-desktop-table { display: none !important; }
}
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-12">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 sm:mb-8 gap-4">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ __('admin.manage_bookings') }}</h1>
        <div class="flex flex-wrap gap-2 sm:gap-3">

<!-- Calendar View -->
<a href="{{ route('admin.bookings.calendar') }}"
   class="inline-flex items-center justify-center gap-2
          bg-purple-600 text-white
          px-4 py-3 sm:px-5 sm:py-3
          rounded-lg
          hover:bg-purple-700 active:bg-purple-800
          focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2
          transition-all duration-200
          font-semibold text-sm
          shadow-md hover:shadow-lg">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2z"/>
    </svg>
    {{ __('admin.calendar_view') }}
</a>

<!-- Table Calendar -->
<a href="{{ route('admin.bookings.calendar-table') }}"
class="inline-flex items-center justify-center gap-2
       bg-black text-white
       px-4 py-3 sm:px-5 sm:py-3
       rounded-lg
       hover:bg-gray-900 active:bg-gray-800
       focus:outline-none focus:ring-2 focus:ring-gray-700 focus:ring-offset-2
       transition-all duration-200
       font-semibold text-sm
       shadow-md hover:shadow-lg"
>
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
    </svg>
    {{ __('admin.table_calendar') }}
</a>

<!-- Sync iCal -->
<form action="{{ route('admin.ical.sync') }}" method="POST" class="inline">
    @csrf
    <button type="submit"
        class="inline-flex items-center justify-center gap-2
               bg-green-600 text-white
               px-4 py-3 sm:px-5 sm:py-3
               rounded-lg
               hover:bg-green-700 active:bg-green-800
               focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2
               transition-all duration-200
               font-semibold text-sm
               shadow-md hover:shadow-lg">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        {{ __('admin.sync_ical_now') }}
    </button>
</form>

<!-- Create Booking (Primary CTA) -->
<a href="{{ route('admin.bookings.create') }}"
   class="inline-flex items-center justify-center gap-2
          bg-blue-600 text-white
          px-4 py-3 sm:px-5 sm:py-3
          rounded-lg
          hover:bg-blue-700 active:bg-blue-800
          focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
          transition-all duration-200
          font-semibold text-sm
          shadow-lg hover:shadow-xl">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 4v16m8-8H4"/>
    </svg>
    {{ __('admin.create_new_booking') }}
</a>

</div>

    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 shadow-sm flex items-center gap-3">
        <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <p class="text-green-800 font-medium">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('warning'))
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6 shadow-sm flex items-center gap-3">
        <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <p class="text-yellow-800 font-medium">{{ session('warning') }}</p>
    </div>
    @endif

    <!-- Filters -->
    <div class="bg-white shadow-md rounded-xl p-4 sm:p-6 mb-6 border border-gray-100">
        <h2 class="text-base font-bold text-gray-900 mb-4">{{ __('admin.filter') }}</h2>
        <form method="GET" action="{{ route('admin.bookings.index') }}" id="filterForm" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Optimized Status Filter with Quick Buttons -->
            <div class="md:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.status') }}</label>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.bookings.index', request()->except('status')) }}" 
                       class="inline-flex items-center px-2.5 py-2 sm:px-3 sm:py-1.5 text-xs sm:text-sm font-semibold rounded-lg transition-all duration-200 {{ !request('status') || request('status') == '' ? 'bg-blue-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        {{ __('admin.all') }}
                        <span class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full {{ !request('status') || request('status') == '' ? 'bg-blue-500' : 'bg-gray-200' }}">
                            {{ $statusCounts['all'] ?? 0 }}
                        </span>
                    </a>
                    <a href="{{ route('admin.bookings.index', array_merge(request()->except('status'), ['status' => 'pending'])) }}" 
                       class="inline-flex items-center px-2.5 py-2 sm:px-3 sm:py-1.5 text-xs sm:text-sm font-semibold rounded-lg transition-all duration-200 {{ request('status') == 'pending' ? 'bg-yellow-500 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        {{ __('admin.pending') }}
                        <span class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full {{ request('status') == 'pending' ? 'bg-yellow-400' : 'bg-gray-200' }}">
                            {{ $statusCounts['pending'] ?? 0 }}
                        </span>
                    </a>
                    <a href="{{ route('admin.bookings.index', array_merge(request()->except('status'), ['status' => 'confirmed'])) }}" 
                       class="inline-flex items-center px-2.5 py-2 sm:px-3 sm:py-1.5 text-xs sm:text-sm font-semibold rounded-lg transition-all duration-200 {{ request('status') == 'confirmed' ? 'bg-green-500 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        {{ __('admin.confirmed') }}
                        <span class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full {{ request('status') == 'confirmed' ? 'bg-green-400' : 'bg-gray-200' }}">
                            {{ $statusCounts['confirmed'] ?? 0 }}
                        </span>
                    </a>
                    <a href="{{ route('admin.bookings.index', array_merge(request()->except('status'), ['status' => 'cancelled'])) }}" 
                       class="inline-flex items-center px-2.5 py-2 sm:px-3 sm:py-1.5 text-xs sm:text-sm font-semibold rounded-lg transition-all duration-200 {{ request('status') == 'cancelled' ? 'bg-red-500 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        {{ __('admin.cancelled') }}
                        <span class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full {{ request('status') == 'cancelled' ? 'bg-red-400' : 'bg-gray-200' }}">
                            {{ $statusCounts['cancelled'] ?? 0 }}
                        </span>
                    </a>
                </div>
                <input type="hidden" name="status" id="status" value="{{ request('status') }}">
            </div>
            <div>
                <label for="room_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.room') }}</label>
                <select name="room_id" id="room_id" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm">
                    <option value="">{{ __('admin.all_rooms') }}</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                            {{ $room->property->name }} - {{ $room->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.from_date') }}</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm">
            </div>
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.to_date') }}</label>
                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm">
            </div>
            <div>
                <label for="source" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.source') }}</label>
                <select name="source" id="source" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm">
                    <option value="">{{ __('admin.all') }}</option>
                    <option value="manual" {{ request('source') == 'manual' ? 'selected' : '' }}>{{ __('admin.manual') }}</option>
                    <option value="website" {{ request('source') == 'website' ? 'selected' : '' }}>{{ __('admin.website') }}</option>
                    <option value="airbnb" {{ request('source') == 'airbnb' ? 'selected' : '' }}>{{ __('admin.airbnb') }}</option>
                </select>
            </div>
            <div class="md:col-span-5 flex flex-col sm:flex-row gap-2 pt-1">
                <button type="submit" class="inline-flex items-center justify-center gap-2 bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 font-semibold text-sm shadow-md hover:shadow-lg w-full sm:w-auto">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    {{ __('admin.filter') }}
                </button>
                @if(request()->hasAny(['status', 'room_id', 'date_from', 'date_to', 'source']))
                <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center justify-center gap-2 bg-slate-100 text-slate-700 py-3 px-6 rounded-lg hover:bg-slate-200 active:bg-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2 transition-all duration-200 font-semibold text-sm border border-slate-300 w-full sm:w-auto">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    {{ __('admin.clear_filters') }}
                </a>
                @endif
            </div>
        </form>
    </div>

    <script>
        // Auto-submit form when other filters change (status is handled by links)
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('filterForm');
            const inputs = form.querySelectorAll('select:not(#status), input[type="date"]');
            
            inputs.forEach(input => {
                input.addEventListener('change', function() {
                    form.submit();
                });
            });
        });
    </script>

    <!-- Mobile Card View (below 768px) -->
    <div class="bookings-mobile-cards space-y-4" role="region" aria-label="{{ __('admin.bookings') }}">
        @forelse($bookings as $booking)
        <div class="bg-white shadow-lg rounded-xl p-4 sm:p-5 border border-gray-100 hover:shadow-xl transition-shadow duration-300">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">#{{ $booking->id }}</h3>
                    <p class="text-sm text-gray-600 font-medium">{{ $booking->room->name }}</p>
                </div>
                <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                    @if($booking->status === 'confirmed') bg-green-100 text-green-800
                    @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                    @else bg-red-100 text-red-800
                    @endif">
                    @if($booking->status === 'confirmed') {{ __('admin.confirmed') }}
                    @elseif($booking->status === 'pending') {{ __('admin.pending') }}
                    @else {{ __('admin.cancelled') }}
                    @endif
                </span>
            </div>
            <div class="space-y-2 mb-4">
                <div class="flex items-center text-sm">
                    <span class="font-medium text-gray-700 w-20">{{ __('admin.guest') }}:</span>
                    <span class="text-gray-900">{{ $booking->guest_full_name }}</span>
                </div>
                <div class="flex items-center text-sm">
                    <span class="font-medium text-gray-700 w-20">{{ __('admin.dates') }}:</span>
                    <span class="text-gray-500">
                        @if($booking->start_at)
                            {{ \Carbon\Carbon::parse($booking->start_at)->format('M d, Y') }}
                        @else
                            <span class="text-gray-400">{{ __('admin.not_set') }}</span>
                        @endif
                        @if($booking->start_at && $booking->end_at) - @endif
                        @if($booking->end_at)
                            {{ \Carbon\Carbon::parse($booking->end_at)->format('M d, Y') }}
                        @else
                            <span class="text-gray-400">({{ __('admin.long_term') }})</span>
                        @endif
                    </span>
                </div>
                <div class="flex items-center text-sm">
                    <span class="font-medium text-gray-700 w-20">{{ __('admin.source') }}:</span>
                    <span class="text-gray-500">
                        @if($booking->source === 'manual') {{ __('admin.manual') }}
                        @elseif($booking->source === 'website') {{ __('admin.website') }}
                        @elseif($booking->source === 'airbnb') {{ __('admin.airbnb') }}
                        @else {{ ucfirst($booking->source) }}
                        @endif
                    </span>
                </div>
                <div class="flex items-center text-sm">
                    <span class="font-medium text-gray-700 w-20">{{ __('admin.amount') }}:</span>
                    <span class="text-gray-900 font-bold text-blue-600">€{{ number_format($booking->total_amount ?? 0, 2) }}</span>
                </div>
            </div>
            <div class="flex gap-2 pt-3 border-t border-gray-200">
                <a href="{{ route('admin.bookings.edit', $booking) }}" class="flex-1 inline-flex items-center justify-center gap-2 bg-blue-600 text-white px-4 py-2.5 rounded-lg hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 text-sm font-semibold shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    {{ __('admin.edit') }}
                </a>
                <form action="{{ route('admin.bookings.destroy', $booking) }}" method="POST" class="flex-1" onsubmit="return confirm('{{ __('admin.are_you_sure_delete') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 text-red-600 hover:text-red-700 hover:bg-red-50 font-semibold px-4 py-2.5 border border-red-300 rounded-lg transition-all duration-200 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        {{ __('admin.delete') }}
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="bg-white shadow-md rounded-xl p-8 text-center border border-gray-100">
            <svg class="w-14 h-14 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
            <p class="text-gray-600 font-medium">{{ __('admin.no_bookings_found') }}</p>
        </div>
        @endforelse
    </div>

    <!-- Desktop Table View (768px and up) -->
    <div class="bookings-desktop-table bg-white shadow-lg rounded-xl overflow-hidden border border-gray-100" role="region" aria-label="{{ __('admin.bookings') }}">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('admin.booking_id') }}</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('admin.room') }}</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('admin.guest') }}</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('admin.dates') }}</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('admin.status') }}</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('admin.source') }}</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('admin.amount') }}</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($bookings as $booking)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">#{{ $booking->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $booking->room->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $booking->guest_full_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($booking->start_at)
                                {{ \Carbon\Carbon::parse($booking->start_at)->format('M d, Y') }}
                            @else
                                <span class="text-gray-400">{{ __('admin.not_set') }}</span>
                            @endif
                            @if($booking->start_at && $booking->end_at) - @endif
                            @if($booking->end_at)
                                {{ \Carbon\Carbon::parse($booking->end_at)->format('M d, Y') }}
                            @else
                                <span class="text-gray-400">({{ __('admin.long_term') }})</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                @if($booking->status === 'confirmed') bg-green-100 text-green-800
                                @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800
                                @endif">
                                @if($booking->status === 'confirmed') {{ __('admin.confirmed') }}
                                @elseif($booking->status === 'pending') {{ __('admin.pending') }}
                                @else {{ __('admin.cancelled') }}
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($booking->source === 'manual') {{ __('admin.manual') }}
                            @elseif($booking->source === 'website') {{ __('admin.website') }}
                            @elseif($booking->source === 'airbnb') {{ __('admin.airbnb') }}
                            @else {{ ucfirst($booking->source) }}
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-blue-600">€{{ number_format($booking->total_amount ?? 0, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.bookings.edit', $booking) }}" class="inline-flex items-center gap-1.5 bg-blue-600 text-white px-3 py-2 rounded-lg hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 text-sm font-semibold shadow-md">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    {{ __('admin.edit') }}
                                </a>
                                <form action="{{ route('admin.bookings.destroy', $booking) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('admin.are_you_sure_delete') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1.5 text-red-600 hover:text-red-700 hover:bg-red-50 font-semibold px-3 py-2 rounded-lg border border-red-200 transition-all duration-200 text-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        {{ __('admin.delete') }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <svg class="w-14 h-14 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                            <p class="text-gray-600 font-medium">{{ __('admin.no_bookings_found') }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="text-xs sm:text-sm text-gray-500 text-center sm:text-left">
            {{ __('admin.showing') }} {{ $bookings->firstItem() ?? 0 }} {{ __('admin.to') }} {{ $bookings->lastItem() ?? 0 }} {{ __('admin.of') }} {{ $bookings->total() }} {{ __('admin.bookings') }}
        </div>
        <div class="flex justify-center sm:justify-end">
            {{ $bookings->links() }}
        </div>
    </div>
</div>
@endsection

