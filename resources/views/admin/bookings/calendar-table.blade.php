@extends('layouts.app')

@section('title', __('admin.table_calendar_view'))

@section('content')
<div class="max-w-full mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 sm:mb-6 gap-3">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ __('admin.table_calendar_view') }}</h1>
        <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 items-start sm:items-center w-full sm:w-auto">
            <form method="GET" action="{{ route('admin.bookings.calendar-table') }}" class="flex flex-col sm:flex-row gap-1 sm:gap-2 items-start sm:items-center w-full sm:w-auto">
                <label class="text-xs sm:text-sm font-medium text-gray-700 whitespace-nowrap">{{ __('admin.start_month') }}:</label>
                <input type="month" name="month" value="{{ $startMonth }}" 
                       class="border border-gray-300 rounded-md px-2 sm:px-3 py-2 text-xs sm:text-sm w-full sm:w-auto">
                <label class="text-xs sm:text-sm font-medium text-gray-700 whitespace-nowrap">{{ __('admin.months') }}:</label>
                <select name="months" class="border border-gray-300 rounded-md px-2 sm:px-3 py-2 text-xs sm:text-sm w-full sm:w-auto">
                    <option value="3" {{ $monthsCount == 3 ? 'selected' : '' }}>3</option>
                    <option value="5" {{ $monthsCount == 5 ? 'selected' : '' }}>5</option>
                    <option value="6" {{ $monthsCount == 6 ? 'selected' : '' }}>6</option>
                    <option value="12" {{ $monthsCount == 12 ? 'selected' : '' }}>12</option>
                </select>
                <button type="submit" class="bg-blue-600 text-white px-3 sm:px-4 py-2 rounded-md hover:bg-blue-700 text-xs sm:text-sm whitespace-nowrap w-full sm:w-auto">
                    {{ __('admin.update') }}
                </button>
            </form>
            <a href="{{ route('admin.bookings.calendar') }}" class="bg-gray-600 text-white px-3 sm:px-4 py-2 rounded-md hover:bg-gray-700 text-xs sm:text-sm whitespace-nowrap w-full sm:w-auto text-center">
                {{ __('admin.standard_calendar') }}
            </a>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden overflow-x-auto">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse border border-gray-300 text-xs sm:text-sm">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-300 px-2 sm:px-4 py-2 sm:py-3 text-left text-xs font-semibold text-gray-700">{{ __('admin.location') }}</th>
                        <th class="border border-gray-300 px-2 sm:px-4 py-2 sm:py-3 text-left text-xs font-semibold text-gray-700 hidden sm:table-cell">{{ __('admin.house') }}</th>
                        <th class="border border-gray-300 px-2 sm:px-4 py-2 sm:py-3 text-left text-xs font-semibold text-gray-700">{{ __('admin.room') }}</th>
                        @foreach($months as $month)
                        <th class="border border-gray-300 px-2 sm:px-4 py-2 sm:py-3 text-center text-xs font-semibold text-gray-700">
                            <span class="hidden sm:inline">{{ $month['name'] }} {{ $month['year'] }}</span>
                            <span class="sm:hidden">{{ substr($month['name'], 0, 3) }} {{ $month['year'] }}</span>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($locations as $location)
                        @php
                            $totalRowsForLocation = 0;
                            foreach($location->houses as $house) {
                                $totalRowsForLocation += $house->rooms->count();
                            }
                        @endphp
                        
                        @if($totalRowsForLocation > 0)
                            @foreach($location->houses as $houseIndex => $house)
                                @php
                                    $houseRooms = $house->rooms->sortBy('name');
                                    $isFirstHouseInLocation = $houseIndex === 0;
                                @endphp
                                
                                @if($houseRooms->count() > 0)
                                    @foreach($houseRooms as $roomIndex => $room)
                                        @php
                                            $roomBookings = $bookingsByRoomMonth[$room->id] ?? [];
                                            $isFirstRow = $isFirstHouseInLocation && $roomIndex === 0;
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            @if($isFirstRow)
                                                <td class="border border-gray-300 px-4 py-3 font-semibold text-gray-900 bg-gray-50" rowspan="{{ $totalRowsForLocation }}">
                                                    {{ $location->name }}
                                                </td>
                                            @endif
                                            @if($roomIndex === 0)
                                                <td class="border border-gray-300 px-4 py-2 text-sm font-medium text-gray-800 bg-gray-50" rowspan="{{ $houseRooms->count() }}">
                                                    {{ $house->name }}
                                                </td>
                                            @endif
                                            <td class="border border-gray-300 px-4 py-2 text-sm text-gray-700">
                                                {{ $room->name }}
                                            </td>
                                            @foreach($months as $monthIndex => $month)
                                                <td class="border border-gray-300 px-3 py-2 text-sm align-top">
                                                    @if(isset($roomBookings[$monthIndex]) && count($roomBookings[$monthIndex]) > 0)
                                                        @foreach($roomBookings[$monthIndex] as $booking)
                                                            @php
                                                                $bookingStart = \Carbon\Carbon::parse($booking->start_at)->setTimezone('Europe/Berlin');
                                                                $bookingEnd = $booking->end_at ? \Carbon\Carbon::parse($booking->end_at)->setTimezone('Europe/Berlin') : null;
                                                                $monthStart = $month['start'];
                                                                $monthEnd = $month['end'];
                                                                
                                                                // Determine display text
                                                                $displayText = $booking->guest_full_name;
                                                                
                                                                // Add date info if booking starts or ends in this month
                                                                $dateInfo = [];
                                                                if ($bookingStart->gte($monthStart) && $bookingStart->lte($monthEnd)) {
                                                                    $dateInfo[] = __('admin.from_date_short') . ' ' . $bookingStart->format('d.m');
                                                                }
                                                                if ($bookingEnd && $bookingEnd->gte($monthStart) && $bookingEnd->lte($monthEnd)) {
                                                                    $dateInfo[] = __('admin.to_date_short') . ' ' . $bookingEnd->format('d.m');
                                                                }
                                                                if (count($dateInfo) > 0) {
                                                                    $displayText .= ' ' . implode(' ', $dateInfo);
                                                                }
                                                                
                                                                // Check if booking ends in this month (for "zieht aus" indication)
                                                                $isMovingOut = $bookingEnd && $bookingEnd->gte($monthStart) && $bookingEnd->lte($monthEnd);
                                                            @endphp
                                                            <div class="mb-1 p-1 bg-blue-50 rounded text-xs hover:bg-blue-100">
                                                                <a href="{{ route('admin.bookings.edit', $booking) }}" 
                                                                   class="block text-blue-800 hover:text-blue-900 font-medium">
                                                                    {{ $displayText }}
                                                                </a>
                                                                @if($isMovingOut)
                                                                    <span class="text-gray-600 text-xs italic">{{ __('admin.moving_out') }}</span>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                            <!-- Empty row for spacing between locations -->
                            <tr>
                                <td colspan="{{ count($months) + 3 }}" class="border border-gray-300 h-2 bg-gray-50"></td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="text-sm font-semibold text-blue-900 mb-2">{{ __('admin.legend') }}:</h3>
        <ul class="text-sm text-blue-800 space-y-1">
            <li><strong>{{ __('admin.from_date_short') }} DD.MM</strong> - {{ __('admin.check_in_date_legend') }}</li>
            <li><strong>{{ __('admin.to_date_short') }} DD.MM</strong> - {{ __('admin.check_out_date_legend') }}</li>
            <li><strong>{{ __('admin.moving_out') }}</strong> - {{ __('admin.moving_out_legend') }}</li>
            <li>{{ __('admin.click_booking_edit') }}</li>
        </ul>
    </div>
</div>
@endsection

