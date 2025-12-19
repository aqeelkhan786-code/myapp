@extends('layouts.app')

@section('title', __('booking.booking_complete'))

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Success Image Header -->
        <div class="relative h-48 bg-gradient-to-r from-green-400 to-green-600">
            <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1200&h=400&fit=crop" 
                 alt="{{ __('booking.booking_confirmed') }}" 
                 class="w-full h-full object-cover opacity-50">
            <div class="absolute inset-0 flex items-center justify-center">
                <svg class="h-20 w-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
        
        <div class="p-8 text-center">
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                <p class="font-semibold">{{ session('success') }}</p>
            </div>
        @endif
        
        <h1 class="text-3xl font-bold text-gray-900 mb-4">✅ Buchungsbestätigung – Ihre Buchung war erfolgreich</h1>
        <p class="text-gray-600 mb-6 text-lg">Vielen Dank! Ihre Buchung wurde erfolgreich übermittelt und reserviert.</p>
        
        @if(!$booking->is_short_term)
        <!-- Deposit Payment Information for Long-term Rentals -->
        <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-6 mb-6 text-left">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">💳 Kaution überweisen</h2>
            <p class="text-gray-700 mb-4">
                Damit wir Ihre Buchung verbindlich abschließen können, bitten wir Sie, die Kaution zeitnah per Überweisung zu leisten:
            </p>
            <div class="bg-white p-4 rounded-md mb-4">
                <p class="text-sm text-gray-700 mb-1"><strong>Empfänger:</strong> Martin Assies</p>
                <p class="text-sm text-gray-700 mb-1"><strong>Bank:</strong> N26 Bank</p>
                <p class="text-sm text-gray-700 mb-1"><strong>IBAN:</strong> DE24 1001 1001 2623 5950 48</p>
                <p class="text-sm text-gray-700"><strong>BIC:</strong> NTSBDEB1XXX</p>
                <p class="text-sm text-gray-600 mt-3">
                    <strong>Verwendungszweck:</strong> {{ $booking->guest_full_name }} + {{ $booking->room->name }} + {{ \Carbon\Carbon::parse($booking->start_at)->format('d.m.Y') }}
                </p>
            </div>
            <div class="bg-green-50 p-4 rounded-md">
                <p class="text-sm text-gray-700 font-semibold mb-2">📬 Sobald die Kaution bei uns eingegangen ist, erhalten Sie von uns:</p>
                <ul class="text-sm text-gray-700 space-y-1 list-disc list-inside ml-4">
                    <li>den Mietvertrag zur Unterzeichnung sowie</li>
                    <li>die Check-in-Details (inkl. Zugang / PIN-Code und Schlüsselübergabeinformationen).</li>
                </ul>
            </div>
            <p class="text-sm text-gray-600 mt-4">
                💬 Bei Fragen melden Sie sich gerne jederzeit.
            </p>
        </div>
        @endif
        
        <div class="bg-gray-50 rounded-lg p-6 mb-6 text-left">
            <div class="flex items-center mb-4">
                <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=100&h=100&fit=crop" 
                     alt="{{ __('booking.room') }}" 
                     class="w-16 h-16 rounded-lg object-cover mr-4">
                <h2 class="text-xl font-semibold text-gray-900">{{ __('booking.booking_details') }}</h2>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">{{ __('booking.room') }}:</span>
                    <span class="font-semibold">{{ $booking->room->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">{{ __('booking.guest') }}:</span>
                    <span class="font-semibold">{{ $booking->guest_full_name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">{{ __('booking.check_in') }}:</span>
                    <span class="font-semibold">{{ \Carbon\Carbon::parse($booking->start_at)->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">{{ __('booking.check_out') }}:</span>
                    <span class="font-semibold">
                        @if($booking->end_at)
                            {{ \Carbon\Carbon::parse($booking->end_at)->format('M d, Y') }}
                        @else
                            <span class="text-gray-500">{{ __('booking.long_term_rental') }}</span>
                        @endif
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">{{ __('booking.booking_reference') }}:</span>
                    <span class="font-semibold">#{{ $booking->id }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">{{ __('booking.total_amount') }}:</span>
                    <span class="font-semibold">€{{ number_format($booking->total_amount, 2) }}</span>
                </div>
            </div>
        </div>
        
        <p class="text-sm text-gray-500 mb-6">{{ __('booking.confirmation_email_sent') }} {{ $booking->email }}</p>
        
        <div class="bg-blue-50 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('booking.view_booking_later') }}</h3>
            <p class="text-sm text-gray-600 mb-4">
                {{ __('booking.save_booking_reference') }} <strong>#{{ $booking->id }}</strong> {{ __('booking.or_use_email') }}
            </p>
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('booking.view', $booking) }}" 
                   class="inline-block bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors text-center">
                    <i class="fa-solid fa-eye mr-2"></i> {{ __('booking.view_booking_details') }}
                </a>
                <a href="{{ route('booking.lookup') }}" 
                   class="inline-block bg-white text-blue-600 border-2 border-blue-600 py-2 px-4 rounded-md hover:bg-blue-50 transition-colors text-center">
                    <i class="fa-solid fa-search mr-2"></i> {{ __('booking.find_my_bookings') }}
                </a>
            </div>
        </div>
        
        @if($booking->documents->count() > 0)
        <div class="bg-blue-50 rounded-lg p-6 mb-6 text-left">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('booking.download_documents') }}</h3>
            <div class="space-y-2">
                @foreach($booking->documents as $document)
                    @if($document->storage_path && Storage::exists($document->storage_path))
                        @php
                            $docTypeNames = [
                                'rental_agreement' => __('booking.rental_agreement'),
                                'landlord_confirmation' => __('booking.landlord_confirmation'),
                                'rent_arrears' => __('booking.rent_arrears_certificate'),
                            ];
                            $docTypeName = $docTypeNames[$document->doc_type] ?? $document->doc_type;
                        @endphp
                        <div class="flex items-center justify-between p-3 bg-white rounded-md">
                            <div>
                                <p class="font-medium text-gray-900">{{ $docTypeName }}</p>
                                <p class="text-sm text-gray-500">{{ __('booking.version') }} {{ $document->version }}</p>
                            </div>
                            <a href="{{ route('documents.download', $document) }}" 
                               class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors text-sm">
                                {{ __('booking.download_pdf') }}
                            </a>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif
        
        <a href="{{ route('booking.index') }}" class="inline-block bg-blue-600 text-white py-2 px-6 rounded-md hover:bg-blue-700 transition-colors">
            {{ __('booking.book_another_room') }}
        </a>
    </div>
</div>
@endsection

