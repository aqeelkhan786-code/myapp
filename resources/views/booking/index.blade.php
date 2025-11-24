@extends('layouts.app')

@section('title', 'Select Apartment')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Select Your Apartment</h1>
    
    <!-- Date Range Filter -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <form method="GET" action="{{ route('booking.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-1">
                <label for="check_in" class="block text-sm font-medium text-gray-700 mb-2">Check-in Date</label>
                <input type="date" 
                       name="check_in" 
                       id="check_in" 
                       value="{{ request('check_in') }}"
                       min="{{ date('Y-m-d') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex-1">
                <label for="check_out" class="block text-sm font-medium text-gray-700 mb-2">Check-out Date</label>
                <input type="date" 
                       name="check_out" 
                       id="check_out" 
                       value="{{ request('check_out') }}"
                       min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    Filter Available Rooms
                </button>
                @if(request('check_in') || request('check_out'))
                <a href="{{ route('booking.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-300 transition-colors">
                    Clear
                </a>
                @endif
            </div>
        </form>
        @if(request('check_in') && request('check_out'))
        <p class="mt-4 text-sm text-gray-600">
            Showing {{ $rooms->count() }} available room(s) from 
            <strong>{{ \Carbon\Carbon::parse(request('check_in'))->format('M d, Y') }}</strong> to 
            <strong>{{ \Carbon\Carbon::parse(request('check_out'))->format('M d, Y') }}</strong>
        </p>
        @endif
    </div>
    
    @if($rooms->count() === 0)
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
        <p class="text-yellow-800">
            @if(request('check_in') && request('check_out'))
                No rooms available for the selected dates. Please try different dates.
            @else
                No rooms available at the moment.
            @endif
        </p>
    </div>
    @endif
    
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

