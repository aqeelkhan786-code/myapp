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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    
    <style>
        .apartment-card {
            transition: all 0.3s ease;
        }
        .apartment-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        .apartment-card .swiper-slide img {
            transition: transform 0.5s ease;
        }
        .apartment-card:hover .swiper-slide img {
            transform: scale(1.05);
        }
        .swiper-button-next,
        .swiper-button-prev {
            color: white;
            background: rgba(0, 0, 0, 0.5);
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }
        .swiper-button-next:after,
        .swiper-button-prev:after {
            font-size: 18px;
        }
        .swiper-pagination-bullet {
            background: white;
            opacity: 0.7;
        }
        .swiper-pagination-bullet-active {
            opacity: 1;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen py-12 px-4">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    {{ __('booking_flow.select_apartment') }}
                </h1>
                <p class="text-xl text-gray-600">{{ __('booking_flow.select_apartment_description') }}</p>
            </div>
            
            <!-- Apartments Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($apartments as $apartment)
                <a href="{{ route('booking.show', $apartment) }}" 
                   class="apartment-card bg-white rounded-2xl overflow-hidden shadow-lg group">
                    <!-- Apartment Image Slider -->
                    <div class="swiper apartment-swiper-{{ $apartment->id }} h-64">
                        <div class="swiper-wrapper">
                            @if($apartment->images->count() > 0)
                                @foreach($apartment->images as $image)
                                <div class="swiper-slide">
                                    <img src="{{ asset('storage/' . $image->path) }}" 
                                         alt="{{ $apartment->name }}" 
                                         class="w-full h-full object-cover"
                                         loading="lazy">
                                </div>
                                @endforeach
                            @else
                                @php
                                    $apartmentImages = [
                                        'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&h=600&fit=crop',
                                        'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800&h=600&fit=crop',
                                        'https://images.unsplash.com/photo-1493809842364-78817add7ffb?w=800&h=600&fit=crop',
                                        'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&h=600&fit=crop',
                                    ];
                                    $aptImageIndex = isset($loop) ? ($loop->index % count($apartmentImages)) : (($apartments->search($apartment) % count($apartmentImages)));
                                @endphp
                                <div class="swiper-slide">
                                    <img src="{{ $apartmentImages[$aptImageIndex] }}" 
                                         alt="{{ $apartment->name }}" 
                                         class="w-full h-full object-cover"
                                         loading="lazy">
                                </div>
                            @endif
                        </div>
                        @if($apartment->images->count() > 1)
                        <div class="swiper-pagination"></div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                        @endif
                    </div>
                    
                    <!-- Apartment Info -->
                    <div class="p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $apartment->name }}</h2>
                        @if($apartment->description)
                        <p class="text-gray-600 mb-4 line-clamp-2">{{ Str::limit($apartment->description, 100) }}</p>
                        @endif
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm text-gray-500 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                {{ $apartment->capacity }} {{ __('booking.guests') }}
                            </span>
                            <span class="text-lg font-bold text-blue-600">
                                €{{ number_format($apartment->base_price, 2) }}/{{ __('booking.night') }}
                            </span>
                        </div>
                        <div class="pt-4 border-t border-gray-200">
                            <span class="text-sm text-blue-600 font-semibold flex items-center group-hover:text-blue-700">
                                {{ __('booking_flow.view_details') }}
                                <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                        </div>
                    </div>
                </a>
                @empty
                <div class="col-span-3 text-center py-12">
                    <p class="text-gray-500 text-lg">{{ __('booking_flow.no_apartments') }}</p>
                </div>
                @endforelse
            </div>
            
            <!-- Back Button -->
            <div class="text-center mt-12">
                <a href="{{ route('booking-flow.house', $house->location) }}" 
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
        document.addEventListener('DOMContentLoaded', function() {
            @foreach($apartments as $apartment)
            new Swiper('.apartment-swiper-{{ $apartment->id }}', {
                slidesPerView: 1,
                spaceBetween: 0,
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
            });
            @endforeach
        });
    </script>
</body>
</html>

