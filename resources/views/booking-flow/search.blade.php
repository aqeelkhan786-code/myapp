<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('booking_flow.select_apartment') }} - {{ config('app.name', 'Laravel') }}</title>
    
    <!-- Favicon - Logo -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- External Libraries -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <!-- German locale for Flatpickr -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/de.js"></script>
    
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
        /* Modal Styles */
        .room-details-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow: auto;
        }
        .room-details-modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-content-wrapper {
            background-color: #fff;
            border-radius: 12px;
            max-width: 900px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        .modal-header {
            padding: 24px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-body {
            padding: 24px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 32px;
        }
        .close-modal {
            cursor: pointer;
            font-size: 28px;
            font-weight: bold;
            color: #6b7280;
            line-height: 1;
        }
        .close-modal:hover {
            color: #111827;
        }
        @media (max-width: 768px) {
            .modal-body {
                grid-template-columns: 1fr;
            }
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
                <div class="mb-4">
                    <p class="text-xl text-gray-600 mb-2">{{ __('booking_flow.select_apartment_description') }}</p>
                    <div class="flex items-center justify-center gap-4 text-lg text-gray-700">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="font-semibold">{{ $location->name }}</span>
                        </div>
                        <span class="text-gray-400">•</span>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span class="font-semibold">{{ $house->name }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Rental Information Section -->
                <div class="mt-8 max-w-4xl mx-auto">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="space-y-6">
                            <!-- Langzeitmiete Section -->
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __('booking_flow.long_term_rental') }}</h3>
                                <p class="text-gray-700 leading-relaxed">
                                    {{ __('booking_flow.long_term_rental_description') }}
                                </p>
                            </div>
                            
                            <!-- Kurzzeitmiete Section -->
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __('booking_flow.short_term_rental') }}</h3>
                                <p class="text-gray-700 leading-relaxed">
                                    {{ __('booking_flow.short_term_rental_description') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Search Filter Section -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <div class="mb-4 pb-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900 mb-2">{{ __('booking_flow.search_by_date') }}</h2>
                    <p class="text-sm text-gray-600">
                        <span class="font-medium">{{ $location->name }}</span> 
                        <span class="mx-2">→</span> 
                        <span class="font-medium">{{ $house->name }}</span>
                    </p>
                </div>
                <form method="GET" action="{{ route('booking-flow.search', ['location' => $location->id, 'house' => $house->id]) }}" class="flex flex-col md:flex-row gap-4 items-end">
                    <div class="flex-1">
                        <label for="check_in" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.check_in_date') }}</label>
                        <input type="text" 
                               name="check_in" 
                               id="check_in" 
                               value="{{ $checkIn }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="{{ __('booking_flow.select_checkin_placeholder') }}">
                    </div>
                    <div class="flex-1">
                        <label for="check_out" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('booking.check_out_date') }} <span class="text-gray-500 text-xs font-normal">{{ __('booking_flow.optional_long_term') }}</span>
                        </label>
                        <input type="text" 
                               name="check_out" 
                               id="check_out" 
                               value="{{ $checkOut }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="{{ __('booking_flow.select_checkout_placeholder') }}">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-blue-600 text-white py-2 px-6 rounded-md hover:bg-blue-700 transition-colors font-semibold">
                            {{ __('booking_flow.search') }}
                        </button>
                        @if($checkIn || $checkOut)
                        <a href="{{ route('booking-flow.search', ['location' => $location->id, 'house' => $house->id]) }}" 
                           class="bg-gray-200 text-gray-700 py-2 px-6 rounded-md hover:bg-gray-300 transition-colors">
                            {{ __('booking_flow.clear') }}
                        </a>
                        @endif
                    </div>
                </form>
            </div>
            
            <!-- Rooms Grid -->
            @if($checkIn)
                @if($filteredRooms->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($filteredRooms as $room)
                    <div class="room-card bg-white rounded-2xl overflow-hidden shadow-lg group">
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
                                    <span class="text-sm">{{ $room->capacity ?? 1 }} {{ ($room->capacity ?? 1) == 1 ? __('booking.guest') : __('booking.guests') }}</span>
                                </div>
                                <div class="text-right">
                                    @php
                                        $isLongTerm = empty($checkOut) || $checkOut === null || trim($checkOut) === '';
                                        $weeklyPrice = ($room->base_price ?? 0) * 7;
                                    @endphp
                                    @if($isLongTerm)
                                        {{-- Long-term rental - show monthly price --}}
                                        <div class="text-lg font-bold text-blue-600">
                                            €{{ number_format($room->monthly_price ?? 700, 2) }}/{{ __('booking.month') }}
                                        </div>
                                    @else
                                        {{-- Short-term rental - show nightly and weekly price --}}
                                        <div class="text-lg font-bold text-blue-600 mb-1">
                                            €{{ number_format($room->base_price, 2) }}/{{ __('booking.night') }}
                                        </div>
                                        <div class="text-sm font-semibold text-gray-600">
                                            €{{ number_format($weeklyPrice, 2) }}/{{ __('booking.week') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <button onclick="openRoomDetailsModal({{ $room->id }})" class="mt-4 w-full flex items-center justify-center text-blue-600 font-semibold hover:text-blue-700 transition-colors">
                                <span>{{ __('booking_flow.view_details') }}</span>
                                <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-12 bg-white rounded-lg shadow-md">
                    <p class="text-gray-500 text-lg mb-4">
                        {{ $checkOut ? __('booking_flow.no_rooms_available_dates') : __('booking_flow.no_rooms_available_date') }}.
                    </p>
                    <a href="{{ route('booking-flow.search', ['location' => $location->id, 'house' => $house->id]) }}" 
                       class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        {{ __('booking_flow.clear_filters') }}
                    </a>
                </div>
                @endif
            @else
            <div class="text-center py-12 bg-white rounded-lg shadow-md">
                <div class="max-w-md mx-auto">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('booking_flow.select_checkin_search') }}</h3>
                    <p class="text-gray-600 mb-6">
                        {{ __('booking_flow.select_checkin_description') }}
                    </p>
                </div>
            </div>
            @endif
            
            <!-- Room Details Modal -->
            <div id="roomDetailsModal" class="room-details-modal">
                <div class="modal-content-wrapper">
                    <div class="modal-header">
                        <h2 id="modalRoomName" class="text-2xl font-bold text-gray-900"></h2>
                        <span class="close-modal" onclick="closeRoomDetailsModal()">&times;</span>
                    </div>
                    <div class="modal-body">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('booking_flow.amenities_comfort') }}</h3>
                            <ul id="modalAmenities" class="space-y-2 text-sm text-gray-700">
                                <!-- Amenities will be populated by JavaScript -->
                            </ul>
                        </div>
                        <div class="flex flex-col justify-center items-center">
                            <a id="modalBookButton" href="#" class="bg-blue-600 text-white px-8 py-4 rounded-lg hover:bg-blue-700 transition-colors font-semibold text-lg shadow-lg hover:shadow-xl whitespace-nowrap">
                                Buchen
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Back Button -->
            <div class="text-center mt-12">
                <a href="{{ route('booking-flow.house', $location) }}" 
                   class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    {{ __('booking_flow.back_to_house') }}
                </a>
            </div>
        </div>
    </div>

    <script>
        // Room data for modal
        const roomsData = @json($roomsDataForModal);
        
        // Get blocked dates from bookings (passed from controller)
        const blockedDates = @json($blockedDates ?? []);
        
        // Set locale based on app locale
        const appLocale = "{{ app()->getLocale() }}";
        const flatpickrLocale = appLocale === 'de' ? flatpickr.l10ns.de : flatpickr.l10ns.en;
        
        // Initialize Flatpickr for check-in
        const checkIn = flatpickr("#check_in", {
            minDate: "today",
            dateFormat: "Y-m-d",
            locale: flatpickrLocale,
            placeholder: "{{ __('booking_flow.select_checkin_placeholder') }}",
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
        
        // Initialize Flatpickr for check-out (optional)
        const checkOut = flatpickr("#check_out", {
            minDate: "today",
            dateFormat: "Y-m-d",
            locale: flatpickrLocale,
            placeholder: "{{ __('booking_flow.select_checkout_placeholder') }}",
            allowInput: true,
            disable: blockedDates.map(function(range) {
                return {
                    from: range[0],
                    to: range[1]
                };
            }),
        });
        
        // Room Details Modal Functions
        function openRoomDetailsModal(roomId) {
            const room = roomsData.find(r => r.id === roomId);
            if (!room) return;
            
            // Set room name
            document.getElementById('modalRoomName').textContent = room.name;
            
            // Set amenities
            const amenitiesList = document.getElementById('modalAmenities');
            amenitiesList.innerHTML = '';
            room.amenities.forEach(amenity => {
                const li = document.createElement('li');
                li.className = 'flex items-start';
                li.innerHTML = '<span class="mr-2 text-blue-600">•</span><span>' + amenity.trim() + '</span>';
                amenitiesList.appendChild(li);
            });
            
            // Set book button link
            const bookButton = document.getElementById('modalBookButton');
            const checkIn = document.getElementById('check_in').value;
            const checkOut = document.getElementById('check_out').value;
            // Correct route structure: /booking/{room}/form
            let bookUrl = '{{ url("/booking") }}/' + roomId + '/form';
            const params = [];
            if (checkIn) {
                params.push('check_in=' + encodeURIComponent(checkIn));
            }
            if (checkOut) {
                params.push('check_out=' + encodeURIComponent(checkOut));
            }
            if (params.length > 0) {
                bookUrl += '?' + params.join('&');
            }
            bookButton.href = bookUrl;
            
            // Show modal
            document.getElementById('roomDetailsModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        function closeRoomDetailsModal() {
            document.getElementById('roomDetailsModal').classList.remove('active');
            document.body.style.overflow = 'auto';
        }
        
        // Close modal when clicking outside
        document.getElementById('roomDetailsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRoomDetailsModal();
            }
        });
        
        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeRoomDetailsModal();
            }
        });
    </script>
</body>
</html>

