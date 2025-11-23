@extends('layouts.app')

@section('title', 'Booking Complete')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Success Image Header -->
        <div class="relative h-48 bg-gradient-to-r from-green-400 to-green-600">
            <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1200&h=400&fit=crop" 
                 alt="Booking Confirmed" 
                 class="w-full h-full object-cover opacity-50">
            <div class="absolute inset-0 flex items-center justify-center">
                <svg class="h-20 w-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
        
        <div class="p-8 text-center">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Booking Confirmed!</h1>
        <p class="text-gray-600 mb-6">Thank you for your booking. Your reservation has been confirmed.</p>
        
        <div class="bg-gray-50 rounded-lg p-6 mb-6 text-left">
            <div class="flex items-center mb-4">
                <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=100&h=100&fit=crop" 
                     alt="Room" 
                     class="w-16 h-16 rounded-lg object-cover mr-4">
                <h2 class="text-xl font-semibold text-gray-900">Booking Details</h2>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">Room:</span>
                    <span class="font-semibold">{{ $booking->room->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Guest:</span>
                    <span class="font-semibold">{{ $booking->guest_full_name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Check-in:</span>
                    <span class="font-semibold">{{ \Carbon\Carbon::parse($booking->start_at)->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Check-out:</span>
                    <span class="font-semibold">{{ \Carbon\Carbon::parse($booking->end_at)->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Amount:</span>
                    <span class="font-semibold">€{{ number_format($booking->total_amount, 2) }}</span>
                </div>
            </div>
        </div>
        
        <p class="text-sm text-gray-500 mb-6">A confirmation email has been sent to {{ $booking->email }}</p>
        
        <a href="{{ route('booking.index') }}" class="inline-block bg-blue-600 text-white py-2 px-6 rounded-md hover:bg-blue-700 transition-colors">
            Book Another Room
        </a>
    </div>
</div>
@endsection

