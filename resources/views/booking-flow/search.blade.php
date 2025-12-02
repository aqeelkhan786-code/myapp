<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('booking_flow.select_apartment') }} - {{ config('app.name', 'Laravel') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- External Libraries -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <style>
        .room-card {
            transition: all 0.3s ease;
        }
        .room-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        .room-card img {
            transition: transform 0.5s ease;
        }
        .room-card:hover img {
            transform: scale(1.1);
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen py-12 px-4">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    {{ __('booking_flow.select_apartment') }}
                </h1>
                <p class="text-xl text-gray-600">{{ __('booking_flow.select_apartment_description') }}</p>
            </div>
            
            <!-- Search Filter Section -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Search by Date</h2>
                <form method="GET" action="{{ route('booking-flow.search', ['location' => $location->id, 'house' => $house->id]) }}" class="flex flex-col md:flex-row gap-4 items-end">
                    <div class="flex-1">
                        <label for="check_in" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.check_in_date') }}</label>
                        <input type="text" 
                               name="check_in" 
                               id="check_in" 
                               value="{{ $checkIn }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Select check-in date">
                    </div>
                    <div class="flex-1">
                        <label for="check_out" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.check_out_date') }}</label>
                        <input type="text" 
                               name="check_out" 
                               id="check_out" 
                               value="{{ $checkOut }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Select check-out date">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-blue-600 text-white py-2 px-6 rounded-md hover:bg-blue-700 transition-colors font-semibold">
                            Search
                        </button>
                        @if($checkIn || $checkOut)
                        <a href="{{ route('booking-flow.search', ['location' => $location->id, 'house' => $house->id]) }}" 
                           class="bg-gray-200 text-gray-700 py-2 px-6 rounded-md hover:bg-gray-300 transition-colors">
                            Clear
                        </a>
                        @endif
                    </div>
                </form>
            </div>
            
            <!-- Rooms Grid -->
            @if($checkIn && $checkOut)
                @if($filteredRooms->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($filteredRooms as $room)
                    <a href="{{ route('booking.form', ['room' => $room->id, 'check_in' => $checkIn, 'check_out' => $checkOut]) }}" 
                       class="room-card bg-white rounded-2xl overflow-hidden shadow-lg group">
                        <!-- Room Image -->
                        <div class="h-64 bg-gray-200 relative overflow-hidden">
                            @if($room->images && $room->images->count() > 0)
                                <img src="{{ asset('storage/' . $room->images->first()->path) }}" 
                                     alt="{{ $room->name }}" 
                                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                     loading="lazy">
                            @else
                                <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&h=600&fit=crop" 
                                     alt="{{ $room->name }}" 
                                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                     loading="lazy">
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                            <div class="absolute bottom-4 left-4 right-4">
                                <h2 class="text-2xl font-bold text-white drop-shadow-lg">{{ $room->name }}</h2>
                            </div>
                        </div>
                        
                        <!-- Room Info -->
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $room->name }}</h3>
                            @if($room->description)
                            <p class="text-gray-600 line-clamp-2 mb-4">{{ Str::limit($room->description, 100) }}</p>
                            @endif
                            <div class="flex justify-between items-center mb-4">
                                <div class="flex items-center text-gray-600">
                                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <span class="text-sm">{{ $room->capacity }} guests</span>
                                </div>
                                <div class="text-lg font-bold text-blue-600">
                                    €{{ number_format($room->base_price, 2) }}/night
                                </div>
                            </div>
                            <div class="mt-4 flex items-center text-blue-600 font-semibold">
                                <span>{{ __('booking_flow.view_details') }}</span>
                                <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
                @else
                <div class="text-center py-12 bg-white rounded-lg shadow-md">
                    <p class="text-gray-500 text-lg mb-4">
                        No rooms available for the selected dates.
                    </p>
                    <a href="{{ route('booking-flow.search', ['location' => $location->id, 'house' => $house->id]) }}" 
                       class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Clear Filters
                    </a>
                </div>
                @endif
            @else
            <div class="text-center py-12 bg-white rounded-lg shadow-md">
                <div class="max-w-md mx-auto">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Select Dates to Search</h3>
                    <p class="text-gray-600 mb-6">
                        Please select check-in and check-out dates to see available rooms.
                    </p>
                </div>
            </div>
            @endif
            
            <!-- Back Button -->
            <div class="text-center mt-12">
                <a href="{{ route('booking-flow.house', $location) }}" 
                   class="inline-flex items-center px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    {{ __('booking_flow.back_to_house') }}
                </a>
            </div>
        </div>
    </div>

    <script>
        // Get blocked dates from bookings (passed from controller)
        const blockedDates = @json($blockedDates ?? []);
        
        // Initialize Flatpickr for check-in
        const checkIn = flatpickr("#check_in", {
            minDate: "today",
            dateFormat: "Y-m-d",
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    const nextDay = new Date(selectedDates[0]);
                    nextDay.setDate(nextDay.getDate() + 1);
                    checkOut.set('minDate', nextDay.toISOString().split('T')[0]);
                }
            },
            disable: blockedDates.map(function(range) {
                return {
                    from: range[0],
                    to: range[1]
                };
            }),
        });
        
        // Initialize Flatpickr for check-out
        const checkOut = flatpickr("#check_out", {
            minDate: "today",
            dateFormat: "Y-m-d",
            disable: blockedDates.map(function(range) {
                return {
                    from: range[0],
                    to: range[1]
                };
            }),
        });
    </script>
</body>
</html>

