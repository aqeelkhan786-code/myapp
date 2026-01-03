@extends('layouts.app')

@section('title', __('admin.manage_bookings'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('admin.manage_bookings') }}</h1>
        <div class="flex gap-4">
            <a href="{{ route('admin.bookings.calendar') }}" class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 transition-colors">
                {{ __('admin.calendar_view') }}
            </a>
            <a href="{{ route('admin.bookings.calendar-table') }}" class="bg-red-700 text-white px-4 py-2 rounded-md hover:bg-orange-700 transition-colors font-medium shadow-sm">
                {{ __('admin.table_calendar') }}
            </a>
            <form action="{{ route('admin.ical.sync') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors">
                    {{ __('admin.sync_ical_now') }}
                </button>
            </form>
            <a href="{{ route('admin.bookings.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                {{ __('admin.create_new_booking') }}
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <p class="text-green-800">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('warning'))
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <p class="text-yellow-800">{{ session('warning') }}</p>
    </div>
    @endif

    <!-- Filters -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <form method="GET" action="{{ route('admin.bookings.index') }}" id="filterForm" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Optimized Status Filter with Quick Buttons -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.status') }}</label>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.bookings.index', request()->except('status')) }}" 
                       class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md transition-colors {{ !request('status') || request('status') == '' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        {{ __('admin.all') }}
                        <span class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full {{ !request('status') || request('status') == '' ? 'bg-blue-500' : 'bg-gray-200' }}">
                            {{ $statusCounts['all'] ?? 0 }}
                        </span>
                    </a>
                    <a href="{{ route('admin.bookings.index', array_merge(request()->except('status'), ['status' => 'pending'])) }}" 
                       class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md transition-colors {{ request('status') == 'pending' ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        {{ __('admin.pending') }}
                        <span class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full {{ request('status') == 'pending' ? 'bg-yellow-400' : 'bg-gray-200' }}">
                            {{ $statusCounts['pending'] ?? 0 }}
                        </span>
                    </a>
                    <a href="{{ route('admin.bookings.index', array_merge(request()->except('status'), ['status' => 'confirmed'])) }}" 
                       class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md transition-colors {{ request('status') == 'confirmed' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        {{ __('admin.confirmed') }}
                        <span class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full {{ request('status') == 'confirmed' ? 'bg-green-400' : 'bg-gray-200' }}">
                            {{ $statusCounts['confirmed'] ?? 0 }}
                        </span>
                    </a>
                    <a href="{{ route('admin.bookings.index', array_merge(request()->except('status'), ['status' => 'cancelled'])) }}" 
                       class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md transition-colors {{ request('status') == 'cancelled' ? 'bg-red-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        {{ __('admin.cancelled') }}
                        <span class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full {{ request('status') == 'cancelled' ? 'bg-red-400' : 'bg-gray-200' }}">
                            {{ $statusCounts['cancelled'] ?? 0 }}
                        </span>
                    </a>
                </div>
                <!-- Hidden input to preserve status in form submission -->
                <input type="hidden" name="status" id="status" value="{{ request('status') }}">
            </div>
            <div>
                <label for="room_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.room') }}</label>
                <select name="room_id" id="room_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.to_date') }}</label>
                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="source" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.source') }}</label>
                <select name="source" id="source" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('admin.all') }}</option>
                    <option value="manual" {{ request('source') == 'manual' ? 'selected' : '' }}>{{ __('admin.manual') }}</option>
                    <option value="website" {{ request('source') == 'website' ? 'selected' : '' }}>{{ __('admin.website') }}</option>
                    <option value="airbnb" {{ request('source') == 'airbnb' ? 'selected' : '' }}>{{ __('admin.airbnb') }}</option>
                </select>
            </div>
            <div class="md:col-span-5 flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    {{ __('admin.filter') }}
                </button>
                @if(request()->hasAny(['status', 'room_id', 'date_from', 'date_to', 'source']))
                <a href="{{ route('admin.bookings.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-300 transition-colors">
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

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.booking_id') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.room') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.guest') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.dates') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.status') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.source') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.amount') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($bookings as $booking)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $booking->id }}</td>
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
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
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
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">€{{ number_format($booking->total_amount ?? 0, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('admin.bookings.edit', $booking) }}" class="text-blue-600 hover:text-blue-900 mr-3">{{ __('admin.edit') }}</a>
                        <form action="{{ route('admin.bookings.destroy', $booking) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('admin.are_you_sure_delete') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">{{ __('admin.delete') }}</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">{{ __('admin.no_bookings_found') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $bookings->links() }}
    </div>
    
    <div class="mt-4 text-sm text-gray-500">
        {{ __('admin.showing') }} {{ $bookings->firstItem() ?? 0 }} {{ __('admin.to') }} {{ $bookings->lastItem() ?? 0 }} {{ __('admin.of') }} {{ $bookings->total() }} {{ __('admin.bookings') }}
    </div>
</div>
@endsection

