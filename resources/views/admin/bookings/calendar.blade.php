@extends('layouts.app')

@section('title', 'Booking Calendar')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Booking Calendar</h1>
        <div class="flex gap-4">
            <div class="flex gap-2 bg-gray-100 rounded-lg p-1">
                <a href="{{ route('admin.bookings.calendar', ['view' => 'month', 'date' => $date]) }}" 
                   class="px-4 py-2 rounded {{ $view === 'month' ? 'bg-white shadow' : '' }}">Month</a>
                <a href="{{ route('admin.bookings.calendar', ['view' => 'week', 'date' => $date]) }}" 
                   class="px-4 py-2 rounded {{ $view === 'week' ? 'bg-white shadow' : '' }}">Week</a>
                <a href="{{ route('admin.bookings.calendar', ['view' => 'day', 'date' => $date]) }}" 
                   class="px-4 py-2 rounded {{ $view === 'day' ? 'bg-white shadow' : '' }}">Day</a>
            </div>
            <div class="flex gap-2">
                @php
                    $prevDate = $startDate->copy();
                    if ($view === 'month') {
                        $prevDate->subMonth();
                    } elseif ($view === 'week') {
                        $prevDate->subWeek();
                    } else {
                        $prevDate->subDay();
                    }
                    
                    $nextDate = $startDate->copy();
                    if ($view === 'month') {
                        $nextDate->addMonth();
                    } elseif ($view === 'week') {
                        $nextDate->addWeek();
                    } else {
                        $nextDate->addDay();
                    }
                @endphp
                <a href="{{ route('admin.bookings.calendar', ['view' => $view, 'date' => $prevDate->format('Y-m-d')]) }}" 
                   class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300">
                    ← Previous
                </a>
                <a href="{{ route('admin.bookings.calendar', ['view' => $view, 'date' => now('Europe/Berlin')->format('Y-m-d')]) }}" 
                   class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300">
                    Today
                </a>
                <a href="{{ route('admin.bookings.calendar', ['view' => $view, 'date' => $nextDate->format('Y-m-d')]) }}" 
                   class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300">
                    Next →
                </a>
            </div>
            <a href="{{ route('admin.bookings.index') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                List View
            </a>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        @if($view === 'month')
            <!-- Month View -->
            <div class="p-6">
                <h2 class="text-2xl font-semibold text-gray-900 mb-6 text-center">
                    {{ $startDate->format('F Y') }}
                </h2>
                <div class="grid grid-cols-7 gap-1">
                    <!-- Day headers -->
                    @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                    <div class="p-2 text-center text-sm font-semibold text-gray-700 bg-gray-50">
                        {{ $day }}
                    </div>
                    @endforeach
                    
                    <!-- Calendar days -->
                    @php
                        $currentDate = $startDate->copy()->startOfMonth()->startOfWeek();
                        $endOfCalendar = $startDate->copy()->endOfMonth()->endOfWeek();
                    @endphp
                    @while($currentDate <= $endOfCalendar)
                        @php
                            $isCurrentMonth = $currentDate->month === $startDate->month;
                            $isToday = $currentDate->isToday();
                            $dayBookings = $bookings->filter(function($booking) use ($currentDate) {
                                $bookingStart = Carbon\Carbon::parse($booking->start_at)->setTimezone('Europe/Berlin')->startOfDay();
                                $bookingEnd = Carbon\Carbon::parse($booking->end_at)->setTimezone('Europe/Berlin')->startOfDay();
                                return $currentDate->between($bookingStart, $bookingEnd);
                            });
                        @endphp
                        <div class="min-h-24 p-1 border border-gray-200 {{ $isCurrentMonth ? 'bg-white' : 'bg-gray-50' }} {{ $isToday ? 'ring-2 ring-blue-500' : '' }}">
                            <div class="text-xs font-medium {{ $isCurrentMonth ? 'text-gray-900' : 'text-gray-400' }} mb-1">
                                {{ $currentDate->format('j') }}
                            </div>
                            <div class="space-y-1">
                                @foreach($dayBookings->take(3) as $booking)
                                <a href="{{ route('admin.bookings.edit', $booking) }}" 
                                   class="block text-xs p-1 bg-blue-100 text-blue-800 rounded truncate hover:bg-blue-200"
                                   title="{{ $booking->guest_full_name }} - {{ $booking->room->name }}">
                                    {{ $booking->guest_full_name }}
                                </a>
                                @endforeach
                                @if($dayBookings->count() > 3)
                                <div class="text-xs text-gray-500">+{{ $dayBookings->count() - 3 }} more</div>
                                @endif
                            </div>
                        </div>
                        @php $currentDate->addDay(); @endphp
                    @endwhile
                </div>
            </div>
        @elseif($view === 'week')
            <!-- Week View -->
            <div class="p-6">
                <h2 class="text-2xl font-semibold text-gray-900 mb-6 text-center">
                    {{ __('admin.week_of') }} {{ $startDate->format('M d') }} - {{ $endDate->format('M d, Y') }}
                </h2>
                <div class="grid grid-cols-7 gap-4">
                    @php $currentDate = $startDate->copy(); @endphp
                    @for($i = 0; $i < 7; $i++)
                        @php
                            $isToday = $currentDate->isToday();
                            $dayBookings = $bookings->filter(function($booking) use ($currentDate) {
                                $bookingStart = Carbon\Carbon::parse($booking->start_at)->setTimezone('Europe/Berlin')->startOfDay();
                                $bookingEnd = Carbon\Carbon::parse($booking->end_at)->setTimezone('Europe/Berlin')->startOfDay();
                                return $currentDate->between($bookingStart, $bookingEnd);
                            });
                        @endphp
                        <div class="border border-gray-200 rounded-lg p-3 {{ $isToday ? 'ring-2 ring-blue-500' : '' }}">
                            <div class="text-sm font-semibold text-gray-900 mb-2">
                                {{ $currentDate->format('D') }}<br>
                                <span class="text-lg">{{ $currentDate->format('j') }}</span>
                            </div>
                            <div class="space-y-2">
                                @foreach($dayBookings as $booking)
                                <a href="{{ route('admin.bookings.edit', $booking) }}" 
                                   class="block p-2 bg-blue-100 text-blue-800 rounded text-sm hover:bg-blue-200">
                                    <div class="font-medium">{{ $booking->guest_full_name }}</div>
                                    <div class="text-xs text-blue-600">{{ $booking->room->name }}</div>
                                </a>
                                @endforeach
                            </div>
                        </div>
                        @php $currentDate->addDay(); @endphp
                    @endfor
                </div>
            </div>
        @else
            <!-- Day View -->
            <div class="p-6">
                <h2 class="text-2xl font-semibold text-gray-900 mb-6 text-center">
                    {{ $startDate->format('l, F j, Y') }}
                </h2>
                <div class="space-y-4">
                    @php
                        $dayBookings = $bookings->filter(function($booking) use ($startDate) {
                            $bookingStart = Carbon\Carbon::parse($booking->start_at)->setTimezone('Europe/Berlin')->startOfDay();
                            $bookingEnd = Carbon\Carbon::parse($booking->end_at)->setTimezone('Europe/Berlin')->startOfDay();
                            return $startDate->between($bookingStart, $bookingEnd);
                        });
                    @endphp
                    @if($dayBookings->count() > 0)
                        @foreach($dayBookings as $booking)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $booking->guest_full_name }}</h3>
                                    <p class="text-sm text-gray-600">{{ $booking->room->property->name }} - {{ $booking->room->name }}</p>
                                    <p class="text-sm text-gray-500 mt-1">
                                        @if($booking->start_at)
                                            {{ Carbon\Carbon::parse($booking->start_at)->format('M d, Y') }}
                                        @else
                                            <span class="text-gray-400">{{ __('admin.not_set') }}</span>
                                        @endif
                                        @if($booking->start_at && $booking->end_at) - @endif
                                        @if($booking->end_at)
                                            {{ Carbon\Carbon::parse($booking->end_at)->format('M d, Y') }}
                                        @else
                                            <span class="text-gray-400">({{ __('admin.long_term') }})</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="flex gap-2">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                    <a href="{{ route('admin.bookings.edit', $booking) }}" 
                                       class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">
                                        {{ __('admin.edit') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-12 text-gray-500">
                            <p>{{ __('admin.no_bookings_for_day') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

