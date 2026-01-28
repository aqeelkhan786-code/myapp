@extends('layouts.app')

@section('title', __('booking.select_apartment'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-12">
    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6 sm:mb-8 text-left">{{ __('booking.select_apartment') }}</h1>
    
    <!-- Date Range Filter -->
    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-6 sm:mb-8">
        <h2 class="text-lg sm:text-xl font-bold text-gray-900 mb-4 text-left">{{ __('booking.select_apartment') }}</h2>
        <form method="GET" action="{{ route('booking.index') }}" class="flex flex-col lg:flex-row gap-4 lg:gap-6 items-start lg:items-end">
            <div class="w-full lg:flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="lg:w-full">
                    <label for="check_in" class="block text-sm font-medium text-gray-700 mb-2 text-left">{{ __('booking.check_in_date') }}</label>
                    <input type="date" 
                           name="check_in" 
                           id="check_in" 
                           value="{{ request('check_in') }}"
                           min="{{ date('Y-m-d') }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-colors">
                </div>
                <div class="lg:w-full">
                    <label for="check_out" class="block text-sm font-medium text-gray-700 mb-2 text-left">
                        {{ __('booking.check_out_date') }} 
                        <span class="text-xs text-gray-500 font-normal">({{ __('booking.optional_long_term') ?? 'Optional for long-term' }})</span>
                    </label>
                    <input type="date" 
                           name="check_out" 
                           id="check_out" 
                           value="{{ request('check_out') }}"
                           min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-colors">
                </div>
            </div>
            <div class="w-full lg:w-auto flex flex-col sm:flex-row gap-2 lg:gap-2 mt-2 lg:mt-0">
                <button type="submit" class="inline-flex items-center justify-center gap-2 bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 font-semibold text-sm shadow-md hover:shadow-lg transform hover:scale-[1.02] active:scale-[0.98] w-full sm:w-auto lg:w-auto lg:whitespace-nowrap">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    {{ __('booking.filter_available_rooms') }}
                </button>
                @if(request('check_in') || request('check_out'))
                <a href="{{ route('booking.index') }}" class="inline-flex items-center justify-center gap-2 bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 font-semibold text-sm shadow-md hover:shadow-lg transform hover:scale-[1.02] active:scale-[0.98] w-full sm:w-auto lg:w-auto lg:whitespace-nowrap">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    {{ __('booking.clear') }}
                </a>
                @endif
            </div>
        </form>
        @if(request('check_in'))
        <p class="mt-4 text-sm text-gray-600 text-left">
            {{ __('booking.showing') }} {{ $rooms->count() }} {{ __('booking.available_rooms') }} 
            @if(isset($isLongTerm) && $isLongTerm)
                {{ __('booking.from') ?? 'from' }} <strong>{{ \Carbon\Carbon::parse(request('check_in'))->format('M d, Y') }}</strong> 
                <span class="text-blue-600 font-semibold">({{ __('booking.long_term_rental') ?? 'Long-term rental' }})</span>
            @elseif(request('check_out'))
                <strong>{{ \Carbon\Carbon::parse(request('check_in'))->format('M d, Y') }}</strong> {{ __('booking.to') }} 
                <strong>{{ \Carbon\Carbon::parse(request('check_out'))->format('M d, Y') }}</strong>
            @endif
        </p>
        @endif
    </div>
    
    @if($rooms->count() === 0)
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-8 text-center shadow-md">
        <svg class="w-16 h-16 mx-auto text-yellow-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-yellow-800 text-lg font-semibold">
            @if(request('check_in') && request('check_out'))
                {{ __('booking.no_rooms_selected_dates') }}
            @else
                {{ __('booking.no_rooms_available') }}
            @endif
        </p>
    </div>
    @endif
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 lg:gap-8">
        @foreach($rooms as $room)
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 group">
            <!-- Image Slider -->
            <div class="swiper room-swiper-{{ $room->id }} h-48 sm:h-64 relative overflow-hidden">
                <div class="swiper-wrapper">
                    @if($room->images->count() > 0)
                        @foreach($room->images as $image)
                        <div class="swiper-slide">
                            <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $room->name }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                        </div>
                        @endforeach
                    @else
                        <div class="swiper-slide">
                            <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&h=600&fit=crop" 
                                 alt="{{ $room->name }}" 
                                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                        </div>
                    @endif
                </div>
                @if($room->images->count() > 1)
                <div class="swiper-pagination"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                @endif
            </div>
            
            <div class="p-4 sm:p-6">
                <h2 class="text-lg sm:text-xl font-bold text-gray-900 mb-2 text-left">{{ $room->name }}</h2>
                <p class="text-sm sm:text-base text-gray-600 mb-3 sm:mb-4 text-left line-clamp-2">{{ Str::limit($room->description, 100) }}</p>
                <div class="flex flex-col gap-3 mb-3 sm:mb-4">
                    <div class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 mr-1.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span class="text-sm text-gray-700 font-medium">{{ $room->capacity }} {{ $room->capacity == 1 ? __('booking.guest') : __('booking.guests') }}</span>
                    </div>
                    @php
                        $isLongTermBooking = isset($isLongTerm) && $isLongTerm;
                        $displayPrice = $isLongTermBooking ? ($room->monthly_price ?? 700.00) : $room->base_price;
                        $priceLabel = $isLongTermBooking ? (__('booking.month') ?? '/Monat') : (__('booking.night') ?? '/Nacht');
                    @endphp
                    <div class="text-left">
                        <span class="text-xl font-bold text-blue-600">€{{ number_format($displayPrice, 2) }}{{ $priceLabel }}</span>
                    </div>
                </div>
                <a href="{{ route('booking.form', $room) }}{{ request('check_in') ? '?check_in=' . request('check_in') . (request('check_out') ? '&check_out=' . request('check_out') : '') : '' }}" 
                   class="inline-flex items-center justify-center gap-2 w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 font-semibold text-sm shadow-md hover:shadow-lg transform hover:scale-[1.02] active:scale-[0.98]">
                    <span>{{ __('booking.view_details_book') }}</span>
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>

@push('styles')
<style>
    @media (max-width: 768px) {
        [class*="room-swiper-"] {
            height: 200px !important;
        }
    }
    
    .room-card {
        transition: all 0.3s ease;
    }
    
    .room-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }
</style>
@endpush

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

