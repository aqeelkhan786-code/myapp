@extends('layouts.app')

@section('title', 'Select Apartment')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Select Your Apartment</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        @foreach($rooms as $room)
        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
            <!-- Image Slider -->
            <div class="swiper room-swiper-{{ $room->id }} h-64">
                <div class="swiper-wrapper">
                    @if($room->images->count() > 0)
                        @foreach($room->images as $image)
                        <div class="swiper-slide">
                            <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $room->name }}" class="w-full h-full object-cover">
                        </div>
                        @endforeach
                    @else
                        <div class="swiper-slide">
                            <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&h=600&fit=crop" 
                                 alt="{{ $room->name }}" 
                                 class="w-full h-full object-cover">
                        </div>
                    @endif
                </div>
                @if($room->images->count() > 1)
                <div class="swiper-pagination"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                @endif
            </div>
            
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ $room->name }}</h2>
                <p class="text-gray-600 mb-4">{{ Str::limit($room->description, 100) }}</p>
                <div class="flex justify-between items-center mb-4">
                    <span class="text-sm text-gray-500">Capacity: {{ $room->capacity }} guests</span>
                    <span class="text-lg font-bold text-gray-900">€{{ number_format($room->base_price, 2) }}/night</span>
                </div>
                <a href="{{ route('booking.show', $room) }}" class="block w-full text-center bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors">
                    View Details & Book
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
    @foreach($rooms as $room)
    new Swiper('.room-swiper-{{ $room->id }}', {
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
</script>
@endpush
@endsection

