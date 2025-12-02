@extends('layouts.app')

@section('title', ($room && $room->name ? $room->name : 'Room') . ' - Booking')

@section('content')
@if(!$room)
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
            <p class="text-red-800">Room not found.</p>
        </div>
    </div>
@else
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Room Images -->
        <div>
            <div class="swiper room-detail-swiper h-96 mb-4">
                <div class="swiper-wrapper">
                    @if($room->images && $room->images->count() > 0)
                        @foreach($room->images as $image)
                        <div class="swiper-slide">
                            <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $room->name ?? 'Room' }}" class="w-full h-full object-cover rounded-lg">
                        </div>
                        @endforeach
                    @else
                        <div class="swiper-slide">
                            <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=1200&h=800&fit=crop" 
                                 alt="{{ $room->name ?? 'Room' }}" 
                                 class="w-full h-full object-cover rounded-lg">
                        </div>
                    @endif
                </div>
                @if($room->images && $room->images->count() > 1)
                <div class="swiper-pagination"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                @endif
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ $room->name ?? 'Room' }}</h1>
                <p class="text-gray-600 mb-4">{{ $room->description ?? '' }}</p>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Capacity:</span>
                        <span class="font-semibold">{{ $room->capacity ?? 0 }} guests</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Price per night:</span>
                        <span class="font-semibold">€{{ number_format($room->base_price ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Booking Button -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Book This Room</h2>
            <p class="text-gray-600 mb-6">Start your booking by filling out our simple 3-step form.</p>
            
            <a href="{{ route('booking.form', $room) }}" 
               class="w-full bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 transition-colors text-center block font-semibold">
                Start Booking
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Initialize Swiper
    const swiper = new Swiper('.room-detail-swiper', {
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
</script>
@endpush
@endif
@endsection

