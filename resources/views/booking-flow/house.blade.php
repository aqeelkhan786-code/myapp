<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Living in {{ $location->name }} - {{ config('app.name', 'Laravel') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Figtree', sans-serif;
        }
        .room-card {
            transition: all 0.3s ease;
        }
        .room-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .amenity-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
        }
        .amenity-item::before {
            content: "•";
            color: #4caf50;
            font-weight: bold;
            font-size: 1.5rem;
            margin-right: 0.75rem;
        }
    </style>
</head>
<body class="font-sans antialiased bg-white">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 py-4 px-6">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="{{ route('booking-flow.home') }}" class="text-gray-700 hover:text-gray-900">
                <span class="font-semibold">{{ __('booking_flow.home') }}</span>
            </a>
            <button class="text-gray-700 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </header>

    <div class="min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Main Title -->
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
                    {{ __('booking_flow.living_in') }} {{ $location->name }}
                </h1>
            </div>

            <!-- Description Section -->
            <div class="mb-12 max-w-4xl mx-auto">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('booking_flow.description') }}</h2>
                <div class="prose prose-lg max-w-none">
                    <p class="text-gray-700 leading-relaxed mb-6">
                        <strong>{{ __('booking_flow.furnished_rooms_brandenburg') }} {{ $location->name }}</strong>
                    </p>
                    <p class="text-gray-700 leading-relaxed mb-6">
                        {!! str_replace([':location', ':count'], [$location->name, $rooms->count()], __('booking_flow.at_haus_offer')) !!}
                    </p>
                </div>
                
                <!-- Check Availability Button -->
                <div class="text-center mb-12">
                    <a href="{{ route('booking-flow.search', ['location' => $location->id, 'house' => $house->id]) }}" 
                       class="inline-flex items-center px-8 py-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold text-lg shadow-lg hover:shadow-xl">
                        {{ __('booking_flow.check_availability') }}
                    </a>
                </div>
            </div>

            <!-- Room Amenities Section -->
            <div class="mb-12 max-w-4xl mx-auto">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('booking_flow.room_amenities') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    <div class="amenity-item">
                        <span class="text-gray-700">{{ __('booking_flow.wifi_free') }}</span>
                    </div>
                    <div class="amenity-item">
                        <span class="text-gray-700">{{ __('booking_flow.fully_equipped_kitchen') }}</span>
                    </div>
                    <div class="amenity-item">
                        <span class="text-gray-700">{{ __('booking_flow.comfortable_beds') }}</span>
                    </div>
                    <div class="amenity-item">
                        <span class="text-gray-700">{{ __('booking_flow.tv_every_room') }}</span>
                    </div>
                    <div class="amenity-item">
                        <span class="text-gray-700">{{ __('booking_flow.common_areas') }}</span>
                    </div>
                    <div class="amenity-item">
                        <span class="text-gray-700">{{ __('booking_flow.parking') }}</span>
                    </div>
                    <div class="amenity-item">
                        <span class="text-gray-700">{{ __('booking_flow.central_location') }}</span>
                    </div>
                    <div class="amenity-item">
                        <span class="text-gray-700">{{ __('booking_flow.flexible_rental') }}</span>
                    </div>
                </div>

                <!-- Interested? Book Now Section -->
                <div class="bg-gray-50 rounded-lg p-6 mb-8">
                    <p class="text-lg font-semibold text-gray-900 mb-4"><strong>{{ __('booking_flow.interested_book_now') }}</strong></p>
                    <div class="text-center">
                        <a href="{{ route('booking-flow.search', ['location' => $location->id, 'house' => $house->id]) }}" 
                           class="inline-flex items-center px-8 py-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold text-lg shadow-lg hover:shadow-xl">
                            {{ __('booking_flow.check_availability') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Location Name -->
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-900">{{ $location->name }}</h2>
            </div>

            <!-- Rooms Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                @foreach($rooms as $room)
                <div class="room-card bg-white rounded-lg overflow-hidden shadow-md">
                    <!-- Room Image -->
                    <div class="h-48 bg-gray-200 relative overflow-hidden">
                        @if($room->images && $room->images->count() > 0)
                            <img src="{{ asset('storage/' . $room->images->first()->path) }}" 
                                 alt="{{ $room->name }}" 
                                 class="w-full h-full object-cover"
                                 loading="lazy">
                        @else
                            <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=400&h=300&fit=crop" 
                                 alt="{{ $room->name }}" 
                                 class="w-full h-full object-cover"
                                 loading="lazy">
                        @endif
                    </div>
                    
                    <!-- Room Name -->
                    <div class="p-4 text-center">
                        <h3 class="text-xl font-bold text-gray-900">{{ $room->name }}</h3>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Guest Favorite Badge -->
            <div class="max-w-4xl mx-auto text-center mb-12">
                <div class="inline-block bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                    <div class="text-3xl font-bold text-yellow-600 mb-2">4.65</div>
                    <div class="text-lg font-semibold text-gray-900 mb-2">
                        <strong>{{ __('booking_flow.guest_favorite') }}</strong>
                    </div>
                    <p class="text-sm text-gray-600">
                        {{ __('booking_flow.guest_favorite_description') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-100 border-t border-gray-200 py-8 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center text-gray-600 text-sm">
                <p>{{ __('booking_flow.copyright') }}</p>
                <div class="mt-4 space-x-4">
                    <a href="#" class="hover:text-gray-900">{{ __('booking_flow.imprint') }}</a>
                    <span>|</span>
                    <a href="#" class="hover:text-gray-900">{{ __('booking_flow.privacy_policy') }}</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
