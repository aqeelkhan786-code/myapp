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
        
        <!-- Booking Form -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Select Dates</h2>
            <form action="{{ route('booking.store', $room) }}" method="POST" id="booking-form">
                @csrf
                <div class="mb-4">
                    <label for="dates" class="block text-sm font-medium text-gray-700 mb-2">Check-in / Check-out</label>
                    <input type="text" id="dates" name="dates" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" readonly required>
                    <input type="hidden" name="start_at" id="start_at">
                    <input type="hidden" name="end_at" id="end_at">
                </div>
                
                <div id="booking-summary" class="mb-4 p-4 bg-gray-50 rounded-md hidden">
                    <div class="flex justify-between mb-2">
                        <span>Nights:</span>
                        <span id="nights-count">0</span>
                    </div>
                    <div class="flex justify-between font-bold text-lg">
                        <span>Total:</span>
                        <span id="total-amount">€0.00</span>
                    </div>
                </div>
                
                @if($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
                        <ul class="list-disc list-inside text-sm text-red-600">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <button type="submit" id="submit-btn" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed" disabled>
                    Continue to Booking
                </button>
            </form>
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
    
    // Get blocked dates from bookings
    const blockedDates = @json(($bookings ?? collect())->map(function($booking) {
        return [
            \Carbon\Carbon::parse($booking->start_at)->format('Y-m-d'),
            \Carbon\Carbon::parse($booking->end_at)->format('Y-m-d')
        ];
    }));
    
    // Initialize Flatpickr
    const fp = flatpickr("#dates", {
        mode: "range",
        minDate: "today",
        dateFormat: "Y-m-d",
        disable: blockedDates.map(function(range) {
            return {
                from: range[0],
                to: range[1]
            };
        }),
        onChange: function(selectedDates, dateStr, instance) {
            const submitBtn = document.getElementById('submit-btn');
            if (selectedDates.length === 2) {
                const start = selectedDates[0];
                const end = selectedDates[1];
                const nights = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
                const pricePerNight = {{ $room->base_price ?? 0 }};
                const total = nights * pricePerNight;
                
                // Format dates as YYYY-MM-DD
                const startDate = start.getFullYear() + '-' + 
                    String(start.getMonth() + 1).padStart(2, '0') + '-' + 
                    String(start.getDate()).padStart(2, '0');
                const endDate = end.getFullYear() + '-' + 
                    String(end.getMonth() + 1).padStart(2, '0') + '-' + 
                    String(end.getDate()).padStart(2, '0');
                
                document.getElementById('start_at').value = startDate;
                document.getElementById('end_at').value = endDate;
                document.getElementById('nights-count').textContent = nights;
                document.getElementById('total-amount').textContent = '€' + total.toFixed(2);
                document.getElementById('booking-summary').classList.remove('hidden');
                submitBtn.disabled = false;
            } else {
                document.getElementById('booking-summary').classList.add('hidden');
                document.getElementById('start_at').value = '';
                document.getElementById('end_at').value = '';
                submitBtn.disabled = true;
            }
        }
    });
    
    // Prevent form submission if dates are not selected
    document.getElementById('booking-form').addEventListener('submit', function(e) {
        const startAt = document.getElementById('start_at').value;
        const endAt = document.getElementById('end_at').value;
        
        if (!startAt || !endAt) {
            e.preventDefault();
            alert('Please select check-in and check-out dates');
            return false;
        }
    });
</script>
@endpush
@endif
@endsection

