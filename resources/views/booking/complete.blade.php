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
                    <span class="font-semibold">
                        @if($booking->end_at)
                            {{ \Carbon\Carbon::parse($booking->end_at)->format('M d, Y') }}
                        @else
                            <span class="text-gray-500">Long-term rental</span>
                        @endif
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Booking Reference:</span>
                    <span class="font-semibold">#{{ $booking->id }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Amount:</span>
                    <span class="font-semibold">€{{ number_format($booking->total_amount, 2) }}</span>
                </div>
            </div>
        </div>
        
        <p class="text-sm text-gray-500 mb-6">A confirmation email has been sent to {{ $booking->email }}</p>
        
        <div class="bg-blue-50 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">View Your Booking Later</h3>
            <p class="text-sm text-gray-600 mb-4">
                Save your booking reference <strong>#{{ $booking->id }}</strong> or use your email address to view this booking anytime.
            </p>
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('booking.view', $booking) }}" 
                   class="inline-block bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors text-center">
                    <i class="fa-solid fa-eye mr-2"></i> View Booking Details
                </a>
                <a href="{{ route('booking.lookup') }}" 
                   class="inline-block bg-white text-blue-600 border-2 border-blue-600 py-2 px-4 rounded-md hover:bg-blue-50 transition-colors text-center">
                    <i class="fa-solid fa-search mr-2"></i> Find My Bookings
                </a>
            </div>
        </div>
        
        @if($booking->documents->count() > 0)
        <div class="bg-blue-50 rounded-lg p-6 mb-6 text-left">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Download Documents</h3>
            <div class="space-y-2">
                @foreach($booking->documents as $document)
                    @if($document->storage_path && Storage::exists($document->storage_path))
                        @php
                            $docTypeNames = [
                                'rental_agreement' => 'Rental Agreement',
                                'landlord_confirmation' => 'Landlord Confirmation',
                                'rent_arrears' => 'Rent Arrears Certificate',
                            ];
                            $docTypeName = $docTypeNames[$document->doc_type] ?? $document->doc_type;
                        @endphp
                        <div class="flex items-center justify-between p-3 bg-white rounded-md">
                            <div>
                                <p class="font-medium text-gray-900">{{ $docTypeName }}</p>
                                <p class="text-sm text-gray-500">Version {{ $document->version }}</p>
                            </div>
                            <a href="{{ route('documents.download', $document) }}" 
                               class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors text-sm">
                                Download PDF
                            </a>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif
        
        <a href="{{ route('booking.index') }}" class="inline-block bg-blue-600 text-white py-2 px-6 rounded-md hover:bg-blue-700 transition-colors">
            Book Another Room
        </a>
    </div>
</div>
@endsection

