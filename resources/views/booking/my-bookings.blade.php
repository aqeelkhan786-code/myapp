@extends('layouts.app')

@section('title', 'My Bookings')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">My Bookings</h1>
        <p class="text-gray-600">Bookings for: <strong>{{ $email }}</strong></p>
    </div>
    
    @if($bookings->isEmpty())
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <svg class="h-16 w-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p class="text-gray-600 mb-4">{{ (isset($fromAuth) && $fromAuth) ? __('booking.you_have_no_bookings') : __('No bookings found for this email address.') }}</p>
            <a href="{{ (isset($fromAuth) && $fromAuth) ? route('booking.index') : route('booking.lookup') }}" class="text-blue-600 hover:text-blue-700">{{ (isset($fromAuth) && $fromAuth) ? __('booking.view_rooms') : __('Search again') }}</a>
        </div>
    @else
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach($bookings as $booking)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Booking #{{ $booking->id }}</h3>
                                <p class="text-sm text-gray-500">{{ $booking->room->name }}</p>
                            </div>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                @if($booking->status === 'confirmed') bg-green-100 text-green-800
                                @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($booking->status) }}
                            </span>
                            @if($booking->is_short_term && $booking->payment_status !== 'paid')
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">{{ __('booking.payment_pending') }}</span>
                            @endif
                        </div>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fa-solid fa-calendar-check w-5 mr-2 text-blue-600"></i>
                                @if($booking->start_at)
                                    <span>{{ \Carbon\Carbon::parse($booking->start_at)->format('M d, Y') }}</span>
                                    @if($booking->end_at)
                                        <span class="mx-1">-</span>
                                        <span>{{ \Carbon\Carbon::parse($booking->end_at)->format('M d, Y') }}</span>
                                    @else
                                        <span class="ml-2 text-xs text-gray-500">({{ __('booking.long_term_rental') }})</span>
                                    @endif
                                @else
                                    <span class="text-gray-500">{{ __('booking.not_set') }}</span>
                                @endif
                            </div>
                            
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fa-solid fa-user w-5 mr-2 text-blue-600"></i>
                                <span>{{ $booking->guest_full_name }}</span>
                            </div>
                            
                            @if($booking->total_amount)
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fa-solid fa-euro-sign w-5 mr-2 text-blue-600"></i>
                                    <span class="font-semibold">€{{ number_format($booking->total_amount, 2) }}</span>
                                    @if($booking->is_short_term)
                                    <span class="ml-2 text-xs {{ $booking->payment_status === 'paid' ? 'text-green-600' : 'text-amber-600' }}">({{ ucfirst($booking->payment_status ?? 'pending') }})</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        
                        <div class="flex gap-2">
                            @if($booking->is_short_term && $booking->payment_status !== 'paid')
                            <a href="{{ route('booking.billing', $booking) }}" 
                               class="flex-1 text-center bg-amber-600 text-white py-2 px-4 rounded-md hover:bg-amber-700 transition-colors">
                                {{ __('booking.pay_now') }}
                            </a>
                            @endif
                            <a href="{{ route('booking.view', $booking) }}" 
                               class="{{ ($booking->is_short_term && $booking->payment_status !== 'paid') ? 'flex-1' : 'block w-full' }} text-center bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors">
                                {{ __('booking.view_details') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="mt-8 text-center">
            @if(isset($fromAuth) && $fromAuth)
            <a href="{{ route('booking.index') }}" class="text-blue-600 hover:text-blue-700">
                <i class="fa-solid fa-bed mr-2"></i> {{ __('booking.view_rooms') }}
            </a>
            @else
            <a href="{{ route('booking.lookup') }}" class="text-blue-600 hover:text-blue-700">
                <i class="fa-solid fa-arrow-left mr-2"></i> {{ __('booking.search_another_email') }}
            </a>
            @endif
        </div>
    @endif
</div>
@endsection











