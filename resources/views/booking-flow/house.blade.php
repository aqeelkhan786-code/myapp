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
    </style>
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen py-12 px-4">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    {{ $house->name }}
                </h1>
                <p class="text-xl text-gray-600">{{ __('booking_flow.main_house') }}</p>
            </div>
            
            <!-- House Header -->
            <div class="mb-8 bg-white rounded-2xl overflow-hidden shadow-lg">
                <!-- House Image -->
                <div class="h-64 bg-gray-200 relative overflow-hidden">
                    @if($house->image)
                        <img src="{{ asset('storage/' . $house->image) }}" 
                             alt="{{ $house->name }}" 
                             class="w-full h-full object-cover"
                             loading="lazy">
                    @else
                        <img src="https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=1200&h=800&fit=crop" 
                             alt="{{ $house->name }}" 
                             class="w-full h-full object-cover"
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
            </div>
            
            <!-- Action Buttons -->
            <div class="text-center mt-8 flex flex-col sm:flex-row gap-4 justify-center items-center">
                @if($availableRoom)
                <a href="{{ route('booking.show', $availableRoom) }}" 
                   class="inline-flex items-center px-8 py-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold text-lg shadow-lg hover:shadow-xl">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ __('booking_flow.book_now') }}
                </a>
                @endif
                <a href="{{ route('booking-flow.locations') }}" 
                   class="inline-flex items-center px-6 py-4 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
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

