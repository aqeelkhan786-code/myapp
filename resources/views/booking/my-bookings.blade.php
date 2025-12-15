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
            <p class="text-gray-600 mb-4">No bookings found for this email address.</p>
            <a href="{{ route('booking.lookup') }}" class="text-blue-600 hover:text-blue-700">Search again</a>
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
                        </div>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fa-solid fa-calendar-check w-5 mr-2 text-blue-600"></i>
                                <span>{{ \Carbon\Carbon::parse($booking->start_at)->format('M d, Y') }}</span>
                                @if($booking->end_at)
                                    <span class="mx-1">-</span>
                                    <span>{{ \Carbon\Carbon::parse($booking->end_at)->format('M d, Y') }}</span>
                                @else
                                    <span class="ml-2 text-xs text-gray-500">(Long-term)</span>
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
                                </div>
                            @endif
                        </div>
                        
                        <a href="{{ route('booking.view', $booking) }}" 
                           class="block w-full text-center bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors">
                            View Details
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="mt-8 text-center">
            <a href="{{ route('booking.lookup') }}" class="text-blue-600 hover:text-blue-700">
                <i class="fa-solid fa-arrow-left mr-2"></i> Search Another Email
            </a>
        </div>
    @endif
</div>
@endsection




