<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('booking_flow.select_location') }} - {{ config('app.name', 'Laravel') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        .location-card {
            transition: all 0.3s ease;
        }
        .location-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        .location-card img {
            transition: transform 0.5s ease;
        }
        .location-card:hover img {
            transform: scale(1.1);
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen py-12 px-4">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    {{ __('booking_flow.select_location') }}
                </h1>
                <p class="text-xl text-gray-600">{{ __('booking_flow.select_location_description') }}</p>
            </div>
            
            <!-- Locations Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @forelse($locations as $location)
                <a href="{{ route('booking-flow.house', $location) }}" 
                   class="location-card bg-white rounded-2xl overflow-hidden shadow-lg group">
                    <!-- Location Image -->
                    <div class="h-64 md:h-80 bg-gray-200 relative overflow-hidden">
                        @if($location->image)
                            <img src="{{ asset('storage/' . $location->image) }}" 
                                 alt="{{ $location->name }}" 
                                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                 loading="lazy">
                        @else
                            @php
                                $locationImages = [
                                    'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&h=600&fit=crop',
                                    'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800&h=600&fit=crop',
                                    'https://images.unsplash.com/photo-1493809842364-78817add7ffb?w=800&h=600&fit=crop',
                                ];
                                $imageIndex = isset($loop) ? ($loop->index % count($locationImages)) : (($locations->search($location) % count($locationImages)));
                            @endphp
                            <img src="{{ $locationImages[$imageIndex] }}" 
                                 alt="{{ $location->name }}" 
                                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                 loading="lazy">
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                        <div class="absolute bottom-4 left-4 right-4">
                            <h2 class="text-2xl font-bold text-white drop-shadow-lg">{{ $location->name }}</h2>
                        </div>
                    </div>
                    
                    <!-- Location Info -->
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $location->name }}</h3>
                        @if($location->description)
                        <p class="text-gray-600 line-clamp-2 mb-4">{{ Str::limit($location->description, 100) }}</p>
                        @endif
                        <div class="mt-4 flex items-center text-blue-600 font-semibold">
                            <span>{{ __('booking_flow.view_details') }}</span>
                            <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>
                @empty
                <div class="col-span-3 text-center py-12">
                    <p class="text-gray-500 text-lg">{{ __('booking_flow.no_locations') }}</p>
                </div>
                @endforelse
            </div>
            
            <!-- Back Button -->
            <div class="text-center mt-12">
                <a href="{{ route('booking-flow.home') }}" 
                   class="inline-flex items-center px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    {{ __('booking_flow.back_to_home') }}
                </a>
            </div>
        </div>
    </div>
</body>
</html>

