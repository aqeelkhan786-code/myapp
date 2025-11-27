<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $house->name }} - {{ config('app.name', 'Laravel') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        .house-card {
            transition: all 0.3s ease;
        }
        .house-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        .house-card img {
            transition: transform 0.7s ease;
        }
        .house-card:hover img {
            transform: scale(1.1);
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen py-12 px-4">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    {{ $house->name }}
                </h1>
                <p class="text-xl text-gray-600">{{ __('booking_flow.main_house') }}</p>
            </div>
            
            <!-- House Card -->
            <a href="{{ route('booking-flow.apartments', $house) }}" 
               class="house-card block bg-white rounded-2xl overflow-hidden shadow-lg group">
                <!-- House Image -->
                <div class="h-96 bg-gray-200 relative overflow-hidden">
                    @if($house->image)
                        <img src="{{ asset('storage/' . $house->image) }}" 
                             alt="{{ $house->name }}" 
                             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                             loading="lazy">
                    @else
                        <img src="https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=1200&h=800&fit=crop" 
                             alt="{{ $house->name }}" 
                             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                             loading="lazy">
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>
                    <div class="absolute bottom-6 left-6 right-6">
                        <h2 class="text-3xl md:text-4xl font-bold text-white mb-2 drop-shadow-lg">{{ $house->name }}</h2>
                        @if($house->description)
                        <p class="text-white/95 text-lg drop-shadow-md">{{ Str::limit($house->description, 150) }}</p>
                        @endif
                    </div>
                </div>
                
                <!-- House Info -->
                <div class="p-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ __('booking_flow.view_apartments') }}</h3>
                            <p class="text-gray-600">{{ __('booking_flow.view_apartments_description') }}</p>
                        </div>
                        <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </div>
            </a>
            
            <!-- Back Button -->
            <div class="text-center mt-8">
                <a href="{{ route('booking-flow.locations') }}" 
                   class="inline-flex items-center px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    {{ __('booking_flow.back_to_locations') }}
                </a>
            </div>
        </div>
    </div>
</body>
</html>

