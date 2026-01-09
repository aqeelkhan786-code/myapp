@extends('layouts.app')

@section('title', __('booking.booking_details'))

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-6">
        <a href="{{ route('booking.lookup') }}" class="text-blue-600 hover:text-blue-700 mb-4 inline-block">
            <i class="fa-solid fa-arrow-left mr-2"></i> {{ __('booking.back_to_search') }}
        </a>
    </div>
    
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">{{ __('booking.booking_number') }} #{{ $booking->id }}</h1>
                    <p class="text-blue-100">{{ $booking->room->name }}</p>
                </div>
                <span class="px-4 py-2 text-sm font-semibold rounded-full bg-white 
                    @if($booking->status === 'confirmed') text-green-800
                    @elseif($booking->status === 'pending') text-yellow-800
                    @else text-gray-800
                    @endif">
                    @if($booking->status === 'confirmed')
                        {{ __('booking.status_confirmed') }}
                    @elseif($booking->status === 'pending')
                        {{ __('booking.status_pending') }}
                    @else
                        {{ __('booking.status_cancelled') }}
                    @endif
                </span>
            </div>
        </div>
        
        <div class="p-8">
            <!-- Booking Information -->
            <div class="grid md:grid-cols-2 gap-6 mb-8">
                <div class="bg-gray-50 rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('booking.booking_details') }}</h2>
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm text-gray-600">{{ __('booking.booking_id') }}:</span>
                            <p class="font-semibold text-gray-900">#{{ $booking->id }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-600">{{ __('booking.check_in_date') }}:</span>
                            <p class="font-semibold text-gray-900">
                                @if($booking->start_at)
                                    {{ \Carbon\Carbon::parse($booking->start_at)->format('F d, Y') }}
                                @else
                                    <span class="text-gray-500">{{ __('booking.not_set') }}</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-600">{{ __('booking.check_out_date') }}:</span>
                            <p class="font-semibold text-gray-900">
                                @if($booking->end_at)
                                    {{ \Carbon\Carbon::parse($booking->end_at)->format('F d, Y') }}
                                @else
                                    <span class="text-gray-500">{{ __('booking.long_term_rental') }}</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-600">{{ __('booking.duration') }}:</span>
                            <p class="font-semibold text-gray-900">
                                @if($booking->start_at && $booking->end_at)
                                    {{ \Carbon\Carbon::parse($booking->start_at)->diffInDays(\Carbon\Carbon::parse($booking->end_at)) }} {{ __('booking.days') }}
                                @else
                                    {{ __('booking.long_term_rental') }}
                                @endif
                            </p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-600">{{ __('booking.booking_type') }}:</span>
                            <p class="font-semibold text-gray-900">
                                {{ $booking->is_short_term ? __('booking.short_term') : __('booking.long_term') }}
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('booking.guest_information') }}</h2>
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm text-gray-600">{{ __('booking.name') }}:</span>
                            <p class="font-semibold text-gray-900">{{ $booking->guest_full_name }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-600">{{ __('booking.email') }}:</span>
                            <p class="font-semibold text-gray-900">{{ $booking->email }}</p>
                        </div>
                        @if($booking->phone)
                            <div>
                                <span class="text-sm text-gray-600">{{ __('booking.telephone') }}:</span>
                                <p class="font-semibold text-gray-900">{{ $booking->phone }}</p>
                            </div>
                        @endif
                        @if($booking->renter_address)
                            <div>
                                <span class="text-sm text-gray-600">{{ __('booking.address') }}:</span>
                                <p class="font-semibold text-gray-900">
                                    {{ $booking->renter_address }}<br>
                                    {{ $booking->renter_postal_code }} {{ $booking->renter_city }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Payment Information -->
            @if($booking->total_amount)
                <div class="bg-blue-50 rounded-lg p-6 mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('booking.payment_information') }}</h2>
                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <span class="text-sm text-gray-600">{{ __('booking.total_amount') }}:</span>
                            <p class="text-2xl font-bold text-gray-900">€{{ number_format($booking->total_amount, 2) }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-600">{{ __('booking.paid_amount') }}:</span>
                            <p class="text-2xl font-bold text-green-600">€{{ number_format($booking->paid_amount ?? 0, 2) }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-600">{{ __('booking.payment_status') }}:</span>
                            <p class="text-lg font-semibold 
                                @if($booking->payment_status === 'paid') text-green-600
                                @elseif($booking->payment_status === 'partial') text-yellow-600
                                @else text-red-600
                                @endif">
                                @if($booking->payment_status === 'paid')
                                    {{ __('booking.payment_status_paid') }}
                                @elseif($booking->payment_status === 'partial')
                                    {{ __('booking.payment_status_partial') }}
                                @else
                                    {{ __('booking.payment_status_pending') }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Documents -->
            @if($booking->documents->count() > 0 || isset($checkInPdfPath))
                <div class="bg-gray-50 rounded-lg p-6 mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('booking.documents') }}</h2>
                    <div class="space-y-3">
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
                                <div class="flex items-center justify-between p-4 bg-white rounded-md border border-gray-200">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $docTypeName }}</p>
                                        <p class="text-sm text-gray-500">
                                            {{ __('booking.version') }} {{ $document->version }}
                                            @if($document->signed_at)
                                                • {{ __('booking.signed_on') }} @if($document->signed_at){{ \Carbon\Carbon::parse($document->signed_at)->format('M d, Y') }}@else{{ __('booking.not_set') }}@endif
                                            @endif
                                        </p>
                                    </div>
                                    <a href="{{ route('documents.download', $document) }}" 
                                       class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors text-sm">
                                        <i class="fa-solid fa-download mr-2"></i> {{ __('booking.download_pdf') }}
                                    </a>
                                </div>
                            @endif
                        @endforeach
                        
                        @if(isset($checkInPdfPath) && $checkInPdfPath && Storage::exists($checkInPdfPath))
                            @php
                                // Convert storage path to public URL
                                // Path format: public/check-in-pdfs/filename.pdf
                                // URL format: /storage/check-in-pdfs/filename.pdf
                                $pdfUrl = str_replace('public/', 'storage/', $checkInPdfPath);
                            @endphp
                            <div class="flex items-center justify-between p-4 bg-white rounded-md border border-gray-200">
                                <div>
                                    <p class="font-medium text-gray-900">{{ __('booking.check_in_details') }}</p>
                                    <p class="text-sm text-gray-500">{{ __('booking.check_in_pdf_description') }}</p>
                                </div>
                                <a href="{{ asset($pdfUrl) }}" 
                                   target="_blank"
                                   class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors text-sm">
                                    <i class="fa-solid fa-download mr-2"></i> {{ __('booking.download_pdf') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
            
            <!-- Notes -->
            @if($booking->notes)
                <div class="bg-yellow-50 rounded-lg p-6 mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ __('booking.notes') }}</h2>
                    <p class="text-gray-700">{{ $booking->notes }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection




