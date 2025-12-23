@extends('layouts.app')

@section('title', __('booking.booking_form') . ' - ' . __('booking.step') . ' ' . $step)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Progress Steps - Only Step 1 visible to users -->
    <div class="mb-8">
        <div class="flex items-center justify-center">
            <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-600 text-white">
                    1
                </div>
                <span class="ml-2 text-sm font-medium text-blue-600">{{ __('booking.rental_agreement_title') }}</span>
            </div>
        </div>
        <p class="text-center text-sm text-gray-500 mt-2">{{ __('booking.complete_booking_request') }}</p>
    </div>
    
    <!-- Room Preview -->
    <div class="mb-8 bg-white rounded-lg shadow-md overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-0">
            <!-- Room Images Carousel -->
            <div class="relative">
                <div class="swiper room-preview-swiper h-64 md:h-full min-h-[300px]">
                    <div class="swiper-wrapper">
                        @if($room->images && $room->images->count() > 0)
                            @foreach($room->images as $image)
                            <div class="swiper-slide">
                                <img src="{{ asset('storage/' . $image->path) }}" 
                                     alt="{{ $room->name }}" 
                                     class="w-full h-full object-cover">
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
                    @if($room->images && $room->images->count() > 1)
                    <div class="swiper-pagination"></div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                    @endif
                </div>
            </div>
            
            <!-- Room Info -->
            <div class="p-6 flex flex-col justify-center">
                <h3 class="text-2xl font-semibold mb-2">{{ $room->name }}</h3>
                @php
                    $endAt = $formData['step2']['end_at'] ?? request()->get('check_out');
                    $isLongTerm = empty($endAt) || $endAt === null || trim($endAt) === '';
                @endphp
                @if($isLongTerm)
                    <p class="text-xl text-gray-600">€{{ number_format($room->monthly_price ?? 700, 2) }} {{ __('booking.month') ?? '/Monat' }}</p>
                @else
                    <p class="text-xl text-gray-600">€{{ number_format($room->base_price, 2) }} {{ __('booking.per_night') }}</p>
                @endif
                @if($room->description)
                    <p class="text-gray-600 mt-4">{{ Str::limit($room->description, 150) }}</p>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Room Amenities Section -->
    <div class="mb-8 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">✨ {{ app()->getLocale() === 'de' ? 'Ausstattung & Komfort' : 'Amenities & Comfort' }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="amenity-item">
                <span class="text-gray-700">📶 {{ app()->getLocale() === 'de' ? 'Kostenloses WLAN – stabil und zuverlässig' : 'WiFi – free and reliable' }}</span>
            </div>
            <div class="amenity-item">
                <span class="text-gray-700">🍳 {{ app()->getLocale() === 'de' ? 'Voll ausgestattete Gemeinschaftsküche – alles vorhanden, was man braucht' : 'Fully equipped kitchen – for shared use' }}</span>
            </div>
            <div class="amenity-item">
                <span class="text-gray-700">🛏️ {{ app()->getLocale() === 'de' ? 'Bequeme Betten – für einen erholsamen Schlaf' : 'Comfortable beds – restful sleep guaranteed' }}</span>
            </div>
            <div class="amenity-item">
                <span class="text-gray-700">📺 {{ app()->getLocale() === 'de' ? 'TV in jedem Zimmer' : 'TV in every room' }}</span>
            </div>
            <div class="amenity-item">
                <span class="text-gray-700">🛋️ {{ app()->getLocale() === 'de' ? 'Gemeinschaftsbereiche – perfekt zum Entspannen am Abend' : 'Common areas – for relaxed evenings' }}</span>
            </div>
            <div class="amenity-item">
                <span class="text-gray-700">🚗 {{ app()->getLocale() === 'de' ? 'Parkmöglichkeiten – direkt am Haus oder in unmittelbarer Nähe' : 'Parking – directly at the house or nearby' }}</span>
            </div>
            <div class="amenity-item">
                <span class="text-gray-700">📍 {{ app()->getLocale() === 'de' ? 'Zentrale Lage – gute Anbindung an Einkaufsmöglichkeiten & ÖPNV' : 'Central location – good connection to shopping and public transport' }}</span>
            </div>
            <div class="amenity-item">
                <span class="text-gray-700">📅 {{ app()->getLocale() === 'de' ? 'Flexible Mietdauer – kurz- oder langfristig möglich' : 'Flexible rental period – short and long-term stays possible' }}</span>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-8">
        @if($step == 1)
            <!-- Step 1: Rental Agreement Form -->
            <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('booking.rental_agreement_title') }}</h2>
            
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif
            
            @if($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <strong>{{ __('booking.please_fix_errors') }}</strong>
                    <ul class="list-disc list-inside mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form action="{{ route('booking.form-step', ['room' => $room->id, 'step' => 1]) }}" method="POST" id="step1-form">
                @csrf
                
                <!-- Personal Information Section -->
                <div class="mb-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="guest_first_name" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.first_name_required') }}</label>
                            <input type="text" name="guest_first_name" id="guest_first_name" 
                                   value="{{ old('guest_first_name', $formData['step1']['guest_first_name'] ?? '') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            @error('guest_first_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="guest_last_name" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.last_name_required') }}</label>
                            <input type="text" name="guest_last_name" id="guest_last_name" 
                                   value="{{ old('guest_last_name', $formData['step1']['guest_last_name'] ?? '') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            @error('guest_last_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        @php
                            // Determine if this is a short-term rental for hiding job field
                            $startAtForJob = request()->get('check_in') ?? $formData['step2']['start_at'] ?? null;
                            $endAtForJob = request()->get('check_out') ?? $formData['step2']['end_at'] ?? null;
                            $isShortTermForJob = false;
                            
                            // Only short-term if end_at exists, is not empty, and nights <= 30
                            if ($endAtForJob && trim($endAtForJob) !== '' && $startAtForJob && $room->short_term_allowed) {
                                $startDateForJob = \Carbon\Carbon::parse($startAtForJob);
                                $endDateForJob = \Carbon\Carbon::parse($endAtForJob);
                                $nightsForJob = $startDateForJob->diffInDays($endDateForJob);
                                $isShortTermForJob = $nightsForJob <= 30;
                            }
                        @endphp
                        @if(!$isShortTermForJob)
                        <div>
                            <label for="job" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.job_required') }}</label>
                            <input type="text" name="job" id="job" 
                                   value="{{ old('job', $formData['step1']['job'] ?? '') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            @error('job')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        @endif
                        <div>
                            <label for="language" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.language') ?? 'Sprache' }} {{ __('common.required') ?? '*' }}</label>
                            <select name="language" id="language" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">{{ __('booking.select_language') }}</option>
                                <option value="Deutsch" {{ old('language', $formData['step1']['language'] ?? '') == 'Deutsch' ? 'selected' : '' }}>Deutsch</option>
                                <option value="Englisch" {{ old('language', $formData['step1']['language'] ?? '') == 'Englisch' ? 'selected' : '' }}>Englisch</option>
                            </select>
                            @error('language')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="communication_preference" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.communication') ?? 'Kommunikation' }} {{ __('common.required') ?? '*' }}</label>
                            <select name="communication_preference" id="communication_preference" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">{{ __('booking.select_communication') }}</option>
                                <option value="Mail" {{ old('communication_preference', $formData['step1']['communication_preference'] ?? '') == 'Mail' ? 'selected' : '' }}>Mail</option>
                                <option value="Whatsapp" {{ old('communication_preference', $formData['step1']['communication_preference'] ?? '') == 'Whatsapp' ? 'selected' : '' }}>Whatsapp</option>
                            </select>
                            @error('communication_preference')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.phone') ?? 'Handynummer' }} {{ __('common.required') ?? '*' }}</label>
                            <input type="tel" name="phone" id="phone" 
                                   value="{{ old('phone', $formData['step1']['phone'] ?? '') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.email') ?? 'E-Mail-Adresse' }} {{ __('common.required') ?? '*' }}</label>
                            <input type="email" name="email" id="email" 
                                   value="{{ old('email', $formData['step1']['email'] ?? '') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>


                <!-- Apartment -->
                <div class="mb-6">
                    <label for="room_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.apartment') ?? __('booking.select_apartment') }}</label>
                    <select name="room_id" id="room_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed" 
                            disabled>
                        <option value="">{{ __('booking.apartment') ?? __('booking.select_apartment') }}</option>
                        @foreach($allRooms ?? [] as $apartment)
                            <option value="{{ $apartment->id }}" {{ old('room_id', $room->id) == $apartment->id ? 'selected' : '' }}>
                                {{ $apartment->name }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="room_id" value="{{ $room->id }}">
                    <p class="mt-1 text-xs text-gray-500">{{ __('booking.apartment_cannot_change') ?? 'Dieses Feld kann nicht geändert werden, da die Wohnung bereits ausgewählt wurde.' }}</p>
                </div>

                <!-- Date / Rental Period -->
                <div class="mb-8">
                    @php
                        // Get dates from formData or request parameters
                        $startAt = $formData['step2']['start_at'] ?? request()->get('check_in');
                        $endAt = $formData['step2']['end_at'] ?? request()->get('check_out');
                        
                        // Determine if this is a long-term rental
                        $isLongTermForDisplay = empty($endAt) || $endAt === null || trim($endAt) === '';
                        if (!$isLongTermForDisplay && $room->short_term_allowed && $startAt) {
                            try {
                                $startDateCheck = \Carbon\Carbon::parse($startAt);
                                $endDateCheck = \Carbon\Carbon::parse($endAt);
                                $nightsCheck = $startDateCheck->diffInDays($endDateCheck);
                                $isLongTermForDisplay = $nightsCheck > 30;
                            } catch (\Exception $e) {
                                $isLongTermForDisplay = true;
                            }
                        }
                        
                        $dateDisplay = '';
                        $startDateFormatted = '';
                        if ($startAt) {
                            $startDate = \Carbon\Carbon::parse($startAt)->format('Y-m-d');
                            $startDateFormatted = $startDate;
                            // Check if end_at exists and is not empty/null
                            if (!empty($endAt) && $endAt !== null && trim($endAt) !== '') {
                                $endDate = \Carbon\Carbon::parse($endAt)->format('Y-m-d');
                                $dateDisplay = $startDate . ' ' . __('booking.to') . ' ' . $endDate;
                            }
                        }
                    @endphp
                    
                    @if($isLongTermForDisplay)
                        <label for="rental_period" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.rental_period') ?? 'Mietdauer' }}</label>
                        <div class="space-y-3">
                            @if($startDateFormatted)
                            <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100">
                                <p class="text-sm text-gray-700 font-medium mb-1">{{ __('booking.date') ?? 'Datum' }}: {{ $startDateFormatted }}</p>
                            </div>
                            @endif
                            <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100">
                                <p class="text-sm text-gray-700">{{ __('booking.rental_duration_long_term') }}</p>
                            </div>
                        </div>
                    @else
                        <label for="booking_dates" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.date') ?? 'Datum' }}</label>
                        <input type="text" id="booking_dates" name="booking_dates" 
                               value="{{ old('booking_dates', $dateDisplay) }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed" 
                               readonly disabled>
                    @endif
                    <input type="hidden" name="start_at" id="start_at" value="{{ old('start_at', $formData['step2']['start_at'] ?? '') }}">
                    <input type="hidden" name="end_at" id="end_at" value="{{ old('end_at', $formData['step2']['end_at'] ?? '') }}">
                    <p class="mt-1 text-xs text-gray-500">{{ __('booking.date_cannot_change') ?? 'Dieses Feld kann nicht geändert werden, da das Datum bereits ausgewählt wurde.' }}</p>
                </div>

                @php
                    // Determine if this is a long-term rental
                    // Always check request parameters first (they take precedence)
                    $startAt = request()->query('check_in') ?? request()->get('check_in');
                    $endAt = request()->query('check_out') ?? request()->get('check_out');
                    
                    // If not in request, check form data
                    if (empty($startAt)) {
                        $startAt = $formData['step2']['start_at'] ?? null;
                    }
                    if (empty($endAt)) {
                        $endAt = $formData['step2']['end_at'] ?? null;
                    }
                    
                    // Clean up empty strings and null values
                    if (empty($endAt) || $endAt === '' || $endAt === null) {
                        $endAt = null;
                    } else {
                        $endAt = trim($endAt);
                        if ($endAt === '') {
                            $endAt = null;
                        }
                    }
                    
                    // If end_at is null or empty, it's definitely long-term
                    $isLongTermRental = ($endAt === null || $endAt === '');
                    
                    // If end_at exists and is not empty, check if it's short-term or long-term
                    if (!$isLongTermRental && $endAt && $room->short_term_allowed && $startAt) {
                        try {
                            $startDate = \Carbon\Carbon::parse($startAt);
                            $endDate = \Carbon\Carbon::parse($endAt);
                            $nights = $startDate->diffInDays($endDate);
                            // If more than 30 nights, it's long-term; otherwise short-term
                            $isLongTermRental = $nights > 30;
                        } catch (\Exception $e) {
                            // If parsing fails, treat as long-term
                            $isLongTermRental = true;
                        }
                    }
                    
                    // Ensure that if check_out is not provided in URL, it's always long-term
                    if (!request()->has('check_out') && !isset($formData['step2']['end_at'])) {
                        $isLongTermRental = true;
                    }
                @endphp

                {{-- Debug: Uncomment to check values
                <!-- Debug: startAt={{ $startAt }}, endAt={{ $endAt ?? 'null' }}, isLongTermRental={{ $isLongTermRental ? 'true' : 'false' }} -->
                --}}

                @if($isLongTermRental)
                <!-- RENTAL AGREEMENT Section - Only for Long-term Rentals -->
                <div class="mb-8 border-t pt-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ strtoupper(__('booking.rental_agreement_title')) }}</h2>
                    
                    <!-- Important Notice -->
                    <div class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">⚠️ Wichtiger Hinweis zur Buchung</h3>
                        <p class="text-sm text-gray-700 leading-relaxed">
                            Mit dem Absenden der Buchung schließen Sie einen Mietvertrag (Untermietvertrag) ab.<br>
                            Der Vertrag ist verbindlich und wird digital unterschrieben bzw. mit Ihrer Unterschrift bestätigt.
                        </p>
                    </div>
                    
                    <!-- Landlord Section (Hidden/Prefilled) -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-md">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('booking.landlord') }}</h3>
                        <p class="text-sm text-gray-600 mb-2"><strong>{{ __('booking.this_field_hidden') }}</strong></p>
                        <div class="space-y-2 text-sm">
                            <p><strong>{{ __('booking.surname_name') ?? 'Nachname, Name' }}:</strong> <span class="text-gray-600">{{ config('landlord.name', 'Martin Assies') }}</span></p>
                            <p><strong>{{ __('booking.address') }}:</strong> <span class="text-gray-600">{{ config('landlord.address') ?: '[' . __('booking.landlord_address') ?? 'Vermieteradresse' . ']' }}</span></p>
                            <p><strong>{{ __('booking.postcode_city') ?? 'Postleitzahl, Stadt' }}:</strong> <span class="text-gray-600">
                                @if(config('landlord.postal_code') || config('landlord.city'))
                                    {{ config('landlord.postal_code') }} {{ config('landlord.city') }}
                                @else
                                    [{{ __('booking.postcode_city_placeholder') ?? 'Postleitzahl, Stadt' }}]
                                @endif
                            </span></p>
                            <p><strong>{{ __('booking.telephone') ?? 'Telefon' }}:</strong> <span class="text-gray-600">{{ config('landlord.phone') ?: '[' . __('booking.phone') . ']' }}</span></p>
                            <p><strong>{{ __('booking.email') }}:</strong> <span class="text-gray-600">{{ config('landlord.email') ?: '[' . __('booking.email') . ']' }}</span></p>
                        </div>
                    </div>

                    <!-- Renter Section -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('booking.renter') }}</h3>
                        <p class="text-sm text-gray-600 mb-4"><strong>{{ __('booking.this_field_hidden') }}</strong></p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="renter_address" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.address_required') }}</label>
                                <input type="text" name="renter_address" id="renter_address" 
                                       value="{{ old('renter_address', $formData['step2']['renter_address'] ?? '') }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                @error('renter_address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="renter_postal_code" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.postcode_city_required') }}</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="text" name="renter_postal_code" id="renter_postal_code" 
                                           value="{{ old('renter_postal_code', $formData['step2']['renter_postal_code'] ?? '') }}" 
                                           placeholder="{{ __('booking.postal_code') ?? 'Postleitzahl' }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                    <input type="text" name="renter_city" id="renter_city" 
                                           value="{{ old('renter_city', $formData['step2']['renter_city'] ?? '') }}" 
                                           placeholder="{{ __('booking.city') ?? 'Stadt' }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                </div>
                                @error('renter_postal_code')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                @error('renter_city')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="renter_phone" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.telephone_required') }}</label>
                                <input type="tel" name="renter_phone" id="renter_phone" 
                                       value="{{ old('renter_phone', $formData['step2']['renter_phone'] ?? '') }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                @error('renter_phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Rental property -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">🏠 Mietobjekt</h3>
                        <p class="text-sm text-gray-600 mb-2"><strong>{{ __('booking.this_field_hidden') }}</strong></p>
                        <p class="text-sm text-gray-700 mb-2"><strong id="selected-room-name">{{ $room->name ?? 'N/A' }}</strong></p>
                        <p class="text-sm text-gray-600 mb-4">Inklusive gemeinsamer Nutzung von: 🍳 Küche, 🚿 Badezimmer, 🪑 Möbel</p>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.address_required') }}</label>
                            <input type="text" id="room-address" value="{{ $room->property->address ?? 'Sample Address' }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                        </div>
                        
                        <!-- Keys & Access -->
                        <div class="mt-6">
                            <h4 class="text-md font-semibold text-gray-900 mb-3">🔑 Schlüssel & Zugang</h4>
                            <p class="text-sm text-gray-700 mb-3">Für die Dauer der Mietzeit erhält der Mieter:</p>
                            <ul class="text-sm text-gray-700 space-y-1 list-disc list-inside ml-4 mb-3">
                                <li>1 Pin-Code für die Haustür</li>
                                <li>1 Wohnungsschlüssel</li>
                                <li>1 Zimmerschlüssel</li>
                            </ul>
                            <p class="text-sm text-gray-700 font-semibold mb-2">⚠️ Wichtig: Das Anfertigen von Schlüsseln ist untersagt.</p>
                            <p class="text-sm text-gray-600">
                                Bei Verlust eines oder mehrerer Schlüssel ist der Vermieter berechtigt, betroffene Schlösser auf Kosten des Mieters auszutauschen.
                            </p>
                        </div>
                    </div>

                    <!-- Rental Period -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">📅 Mietdauer</h3>
                        <p class="text-sm text-gray-600 mb-2"><strong>{{ __('booking.this_field_hidden') }}</strong></p>
                        <p class="text-sm text-gray-700 mb-2">
                            <strong>Mietbeginn:</strong> ab <span id="tenancy-from">{{ isset($formData['step2']['start_at']) ? \Carbon\Carbon::parse($formData['step2']['start_at'])->format('d.m.Y') : '[Datum auswählen]' }}</span>
                        </p>
                        @php
                            $endAt = $formData['step2']['end_at'] ?? request()->get('check_out');
                            $isLongTerm = empty($endAt) || $endAt === null || trim($endAt) === '';
                        @endphp
                        <p class="text-sm text-gray-700 mb-4">
                            <strong>Mietdauer:</strong> {{ $isLongTerm ? '1 Monat' : 'Variabel' }}
                        </p>
                        
                        <div class="mt-4">
                            <h4 class="text-md font-semibold text-gray-900 mb-2">📝 Kündigungsfrist</h4>
                            <p class="text-sm text-gray-600 leading-relaxed">
                                Mieter und Vermieter können den Mietvertrag mit einer Frist von 1 Monat zum Monatsende kündigen.<br>
                                Die Kündigung muss schriftlich erfolgen (WhatsApp oder E-Mail) und spätestens am letzten Tag des Vormonats bei der anderen Partei eingehen.
                            </p>
                        </div>
                    </div>

                    <!-- Rental Fee -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">💰 Miete</h3>
                        <p class="text-sm text-gray-600 mb-2"><strong>{{ __('booking.this_field_hidden') }}</strong></p>
                        @php
                            $endAt = $formData['step2']['end_at'] ?? request()->get('check_out');
                            $isLongTerm = empty($endAt) || $endAt === null || trim($endAt) === '';
                        @endphp
                        <p class="text-sm text-gray-700 mb-4">
                            Die Miete beträgt <strong id="rent-per-night">€{{ number_format($isLongTerm ? ($room->monthly_price ?? 700) : ($room->base_price ?? 0), 2) }}</strong> {{ $isLongTerm ? 'pro Monat' : __('booking.per_night_text') }}.
                        </p>
                        
                        <div class="mb-4">
                            <h4 class="text-md font-semibold text-gray-900 mb-2">✅ In der Miete enthalten:</h4>
                            <ul class="text-sm text-gray-700 space-y-1 list-disc list-inside ml-4">
                                <li>🔥 Heizung, Warmwasser, Wasser, Abwasser</li>
                                <li>⚡ Strom, Gas</li>
                                <li>📶 Internet</li>
                                <li>🧹 Reinigung der Gemeinschaftsräume</li>
                                <li>🛏️ Bettwäsche, Handtücher</li>
                            </ul>
                        </div>
                        
                        <div class="mt-4 p-3 bg-blue-50 border-l-4 border-blue-500 rounded">
                            <p class="text-sm text-gray-700">
                                <strong>📢 Hinweis zur Anpassung:</strong><br>
                                Aufgrund steigender Gas- und Energiepreise kann die Gesamtmiete steigen. Eine Erhöhung wird mindestens 1 Monat im Voraus angekündigt.
                            </p>
                        </div>
                    </div>

                    <!-- Payment of the rent -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">💳 Zahlung der Miete</h3>
                        <p class="text-sm text-gray-600 mb-2"><strong>{{ __('booking.this_field_hidden') }}</strong></p>
                        <p class="text-sm text-gray-700 mb-3">
                            Die Miete ist monatlich im Voraus zu zahlen, spätestens bis zum 1. des Monats, per Überweisung an:
                        </p>
                        <div class="bg-gray-50 p-4 rounded-md">
                            <p class="text-sm text-gray-700"><strong>Empfänger:</strong> Martin Assies</p>
                            <p class="text-sm text-gray-700"><strong>Bank:</strong> N26 Bank</p>
                            <p class="text-sm text-gray-700"><strong>IBAN:</strong> DE24 1001 1001 2623 5950 48</p>
                            <p class="text-sm text-gray-700"><strong>BIC:</strong> NTSBDEB1XXX</p>
                        </div>
                    </div>

                    <!-- Deposit -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">🔒 Kaution</h3>
                        <p class="text-sm text-gray-600 mb-2"><strong>{{ __('booking.this_field_hidden') }}</strong></p>
                        <p class="text-sm text-gray-700 mb-3">
                            Die Kaution beträgt <strong>780 €</strong> und ist per Überweisung zu zahlen an:
                        </p>
                        <div class="bg-gray-50 p-4 rounded-md">
                            <p class="text-sm text-gray-700"><strong>Empfänger:</strong> Martin Assies</p>
                            <p class="text-sm text-gray-700"><strong>IBAN:</strong> DE24 1001 1001 2623 5950 48</p>
                            <p class="text-sm text-gray-700"><strong>BIC:</strong> NTSBDEB1XXX</p>
                        </div>
                    </div>

                    <!-- Renter's Rights, Obligations and Liability -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">📋 Rechte, Pflichten & Hausregeln</h3>
                        <p class="text-sm text-gray-700 mb-4">Der Mieter verpflichtet sich insbesondere zu folgenden Punkten:</p>
                        <ul class="text-sm text-gray-700 space-y-3 list-none">
                            <li class="flex items-start">
                                <span class="mr-2">🧹</span>
                                <span><strong>Sauberkeit:</strong> Wohnung, Küche und Bad sind sauber und ordentlich zu halten.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">🛡️</span>
                                <span><strong>Sorgfalt:</strong> Möbel und Gegenstände sind pfleglich zu behandeln und im ursprünglichen Zustand zu hinterlassen.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">🗑️</span>
                                <span><strong>Hausordnung & Müll:</strong> Hausordnung beachten, Mülltrennung einhalten (Mülltonnen im Hof).</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">⚠️</span>
                                <span><strong>Schäden melden:</strong> Schäden sind sofort zu melden. Bei verspäteter Meldung haftet der Mieter für Folgeschäden.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">🚭</span>
                                <span><strong>Rauchverbot:</strong> Im gesamten Apartment und Treppenhaus gilt absolutes Rauchverbot.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">👥</span>
                                <span><strong>Keine weiteren Personen:</strong> Unterbringung anderer Personen ist untersagt; längere Besuche nur nach Absprache.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">🔍</span>
                                <span><strong>Zutritt Vermieter:</strong> Vermieter/Beauftragte dürfen die Räume bis zu 2× pro Monat zur Zustandsprüfung betreten.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">🔇</span>
                                <span><strong>Ruhezeiten:</strong> 12–14 Uhr sowie 22–6 Uhr ist Lärm untersagt.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">🏠</span>
                                <span><strong>Bodenpflege:</strong> Böden trocken halten und sachgemäß behandeln, um Schäden zu vermeiden.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">⚖️</span>
                                <span><strong>Konsequenz bei Verstoß:</strong> Regelverstöße können zur sofortigen Kündigung führen.</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Confirmation -->
                    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">✅ Bestätigung</h3>
                        <p class="text-sm text-gray-700">
                            Mit Ihrer Unterschrift bestätigen Sie, dass Sie den Mietvertrag vollständig gelesen haben und mit allen Bedingungen einverstanden sind.
                        </p>
                    </div>

                    <!-- Signatures -->
                    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-md font-semibold text-gray-900 mb-2">{{ __('booking.landlord') }}</h4>
                            <div class="border-t border-gray-300 pt-4">
                                <p class="text-sm text-gray-600">{{ config('landlord.name', 'Martin Assies') }}</p>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-md font-semibold text-gray-900 mb-2">{{ __('booking.renter') }}:</h4>
                            <div class="border-t border-gray-300 pt-4">
                                <p class="text-sm text-gray-600" id="renter-full-name">{{ old('guest_first_name', $formData['step1']['guest_first_name'] ?? '') }} {{ old('guest_last_name', $formData['step1']['guest_last_name'] ?? '') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.landlord') }} - {{ __('booking.place_date') }}:</label>
                            <input type="text" value="{{ \Carbon\Carbon::now()->format('d.m.Y') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.renter') }} - {{ __('booking.place_date') }}: {{ __('common.required') ?? '*' }}</label>
                            <input type="text" value="{{ \Carbon\Carbon::now()->format('d.m.Y') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                        </div>
                    </div>

                    <!-- Signature Pad -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.signature_required') }}</label>
                        <div class="border border-gray-300 rounded-md bg-white" style="max-width: 600px; position: relative;">
                            <canvas id="signature-pad" style="width: 100%; height: 200px; display: block; touch-action: none; cursor: crosshair; -webkit-user-select: none; user-select: none; pointer-events: auto; -webkit-tap-highlight-color: transparent;"></canvas>
                        </div>
                        <button type="button" id="clear-signature" class="mt-2 text-sm text-blue-600 hover:text-blue-800 underline">{{ __('booking.clear_signature') }}</button>
                        <input type="hidden" name="signature" id="signature-data">
                        <p class="mt-1 text-xs text-gray-500">{{ __('booking.signature_instruction') }}</p>
                    </div>
                </div>
                @else
                <!-- Short-term Rental: Payment Section Only (No Rental Contract, Address already shown above) -->
                @php
                    // Recalculate isShortTerm here to ensure it matches the view logic
                    // Use the same logic as above - check request parameters first
                    $startAtForPayment = request()->query('check_in') ?? request()->get('check_in');
                    $endAtForPayment = request()->query('check_out') ?? request()->get('check_out');
                    
                    // If not in request, check form data
                    if (empty($startAtForPayment)) {
                        $startAtForPayment = $formData['step2']['start_at'] ?? null;
                    }
                    if (empty($endAtForPayment)) {
                        $endAtForPayment = $formData['step2']['end_at'] ?? null;
                    }
                    
                    // Clean up empty strings
                    if (empty($endAtForPayment) || $endAtForPayment === '' || $endAtForPayment === null) {
                        $endAtForPayment = null;
                    } else {
                        $endAtForPayment = trim($endAtForPayment);
                        if ($endAtForPayment === '') {
                            $endAtForPayment = null;
                        }
                    }
                    
                    $isShortTermForPayment = false;
                    
                    // Only short-term if end_at exists, is not empty, and nights <= 30
                    if ($endAtForPayment !== null && $startAtForPayment && $room->short_term_allowed) {
                        try {
                            $startDateForPayment = \Carbon\Carbon::parse($startAtForPayment);
                            $endDateForPayment = \Carbon\Carbon::parse($endAtForPayment);
                            $nightsForPayment = $startDateForPayment->diffInDays($endDateForPayment);
                            $isShortTermForPayment = $nightsForPayment <= 30;
                        } catch (\Exception $e) {
                            $isShortTermForPayment = false;
                        }
                    }
                @endphp
                @if($isShortTermForPayment)
                <!-- Payment Section for Short-term Bookings -->
                <div class="mb-8 border-t pt-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('booking.payment_information') ?? 'Zahlungsinformationen' }}</h2>
                    
                    <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                        <p class="text-sm text-yellow-800 font-semibold mb-2">{{ __('booking.payment_required_short_term') }}</p>
                        <p class="text-sm text-yellow-700">{{ __('booking.total_amount') }}: <strong>€{{ number_format($totalAmount, 2) }}</strong></p>
                        @if(config('services.stripe.key'))
                        <p class="text-xs text-yellow-600 mt-1">{{ __('booking.payment_processed_stripe') }}</p>
                        @endif
                    </div>
                    
                    @if(config('services.stripe.key'))
                    <!-- Stripe Payment Element -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.payment_information') }} *</label>
                        <div id="payment-element" class="p-4 border border-gray-300 rounded-md bg-white">
                            <!-- Stripe Elements will create form elements here -->
                        </div>
                        <div id="payment-message" class="mt-2 text-sm text-red-600 hidden"></div>
                        <input type="hidden" name="payment_method_id" id="payment_method_id">
                    </div>
                    @else
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-md">
                        <p class="text-sm text-red-800 font-semibold mb-2">{{ __('booking.payment_processing_not_configured') }}</p>
                        <p class="text-sm text-red-700">{{ __('booking.contact_support_complete_booking') }}</p>
                    </div>
                    @endif
                </div>
                @endif
                @endif
                
                <div class="flex justify-end mt-4">
                    <button type="submit" id="submit-btn" class="bg-green-600 text-white py-2 px-6 rounded-md hover:bg-green-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed">
                        {{ __('booking.complete_booking') }}
                    </button>
                </div>
            </form>
        @else
            <!-- Steps 2 and 3 are admin-only and not visible to regular users -->
            <div class="text-center py-12">
                <p class="text-gray-500 text-lg">{{ __('booking.step_not_available') }}</p>
            </div>
        @endif
        
        @if(false)
            <!-- Step 2: Booking Details (Admin Only) -->
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Booking Details</h2>
            
            <form action="{{ route('booking.form-step', ['room' => $room->id, 'step' => 2]) }}" method="POST">
                @csrf
                
                <div class="mb-6">
                    <label for="dates" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.select_date') ?? 'Datum auswählen' }} *</label>
                    <input type="text" id="dates" name="dates" 
                           value="{{ old('dates', isset($formData['step2']['start_at']) ? \Carbon\Carbon::parse($formData['step2']['start_at'])->format('Y-m-d') . ' ' . __('booking.to') . ' ' . \Carbon\Carbon::parse($formData['step2']['end_at'])->format('Y-m-d') : '') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" readonly required>
                    <input type="hidden" name="start_at" id="start_at" value="{{ old('start_at', $formData['step2']['start_at'] ?? '') }}">
                    <input type="hidden" name="end_at" id="end_at" value="{{ old('end_at', $formData['step2']['end_at'] ?? '') }}">
                    @error('start_at')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('end_at')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('dates')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="renter_address" class="block text-sm font-medium text-gray-700 mb-2">Address *</label>
                        <input type="text" name="renter_address" id="renter_address" 
                               value="{{ old('renter_address', $formData['step2']['renter_address'] ?? '') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        @error('renter_address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="renter_postal_code" class="block text-sm font-medium text-gray-700 mb-2">Postal Code *</label>
                        <input type="text" name="renter_postal_code" id="renter_postal_code" 
                               value="{{ old('renter_postal_code', $formData['step2']['renter_postal_code'] ?? '') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        @error('renter_postal_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="renter_city" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.city') ?? 'Stadt' }} *</label>
                        <input type="text" name="renter_city" id="renter_city" 
                               value="{{ old('renter_city', $formData['step2']['renter_city'] ?? '') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        @error('renter_city')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div id="booking-summary" class="mb-6 p-4 bg-gray-50 rounded-md {{ isset($formData['step2']['start_at']) ? '' : 'hidden' }}">
                    @if(isset($formData['step2']['start_at']))
                        @php
                            $start = \Carbon\Carbon::parse($formData['step2']['start_at']);
                            $endAt = $formData['step2']['end_at'] ?? null;
                            $isLongTerm = empty($endAt) || $endAt === null || trim($endAt) === '';
                            
                            if ($isLongTerm) {
                                $total = $room->monthly_price ?? 700.00;
                            } else {
                                $end = \Carbon\Carbon::parse($endAt);
                                $nights = $start->diffInDays($end);
                                if ($nights > 30) {
                                    $months = ceil($nights / 30);
                                    $total = ($room->monthly_price ?? 700.00) * $months;
                                } else {
                                    $total = $nights * $room->base_price;
                                }
                            }
                        @endphp
                        @if(!$isLongTerm)
                        <div class="flex justify-between mb-2">
                            <span>Nights:</span>
                            <span>{{ $nights ?? 0 }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between font-bold text-lg">
                            <span>Total:</span>
                            <span>€{{ number_format($total, 2) }}</span>
                        </div>
                    @endif
                </div>
                
                <div class="flex justify-between">
                    <a href="{{ route('booking.form', ['room' => $room->id, 'step' => 1]) }}" 
                       class="bg-gray-200 text-gray-700 py-2 px-6 rounded-md hover:bg-gray-300 transition-colors">
                        Previous
                    </a>
                    <button type="submit" class="bg-blue-600 text-white py-2 px-6 rounded-md hover:bg-blue-700 transition-colors">
                        Next
                    </button>
                </div>
            </form>
        @endif
            
        @if(false && $step == 3)
            <!-- Step 3: Confirmation & Signature -->
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Review & Confirm</h2>
            
            <!-- Summary -->
            <div class="mb-6 p-4 bg-gray-50 rounded-md">
                <h3 class="font-semibold mb-3">Booking Summary</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('booking.room') }}:</span>
                        <span class="font-semibold">{{ $room->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('booking.check_in') }}:</span>
                        <span class="font-semibold">{{ \Carbon\Carbon::parse($formData['step2']['start_at'])->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('booking.check_out') }}:</span>
                        <span class="font-semibold">{{ \Carbon\Carbon::parse($formData['step2']['end_at'])->format('M d, Y') }}</span>
                    </div>
                    @php
                        $start = \Carbon\Carbon::parse($formData['step2']['start_at']);
                        $end = \Carbon\Carbon::parse($formData['step2']['end_at']);
                        $nights = $start->diffInDays($end);
                        $total = $nights * $room->base_price;
                    @endphp
                    <div class="flex justify-between">
                        <span class="text-gray-600">Nights:</span>
                        <span class="font-semibold">{{ $nights }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-lg pt-2 border-t">
                        <span>Total:</span>
                        <span>€{{ number_format($total, 2) }}</span>
                    </div>
                </div>
            </div>
            
            <form action="{{ route('booking.form-step', ['room' => $room->id, 'step' => 3]) }}" method="POST" id="step3-form">
                @csrf
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.signature') }} *</label>
                    <canvas id="signature-pad" class="border border-gray-300 rounded-md" width="600" height="200"></canvas>
                    <button type="button" id="clear-signature" class="mt-2 text-sm text-gray-600 hover:text-gray-800">{{ __('booking.clear_signature') }}</button>
                    <input type="hidden" name="signature" id="signature-data">
                    @error('signature')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex justify-between">
                    <a href="{{ route('booking.form', ['room' => $room->id, 'step' => 2]) }}" 
                       class="bg-gray-200 text-gray-700 py-2 px-6 rounded-md hover:bg-gray-300 transition-colors">
                        {{ __('booking.previous') }}
                    </a>
                    <button type="submit" class="bg-green-600 text-white py-2 px-6 rounded-md hover:bg-green-700 transition-colors">
                        {{ __('booking.complete_booking') }}
                    </button>
                </div>
            </form>
        @endif
        
        @if(false)
            <!-- Step 2: Booking Details (Admin Only) - Hidden from users -->
        @endif
        
        @if(false && $step == 3)
            <!-- Step 3: Confirmation & Signature (Admin Only) - Hidden from users -->
        @endif
    </div>
</div>

@push('scripts')
@if($step == 1)
<!-- Flatpickr for date selection -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<!-- SignaturePad is already loaded in layout, no need to load again -->

<script>
    // Room data for updates (passed from controller)
    @php
        try {
            $roomsDataJson = json_encode($roomsData ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            $roomsDataJson = '[]';
        }
    @endphp
    const roomsData = {!! $roomsDataJson !!};
    
    // Date field is disabled/readonly - no need to initialize Flatpickr
    // But we still need to update the tenancy from date if dates are pre-filled
    const startAtInput = document.getElementById('start_at');
    if (startAtInput && startAtInput.value) {
        const startDate = new Date(startAtInput.value);
        const tenancyFrom = document.getElementById('tenancy-from');
        if (tenancyFrom) {
            const formattedDate = String(startDate.getDate()).padStart(2, '0') + '.' + 
                String(startDate.getMonth() + 1).padStart(2, '0') + '.' + 
                startDate.getFullYear();
            tenancyFrom.textContent = formattedDate;
        }
    }
    
    // Apartment selection is disabled - no need for change handler
    // But initialize the display with the selected room
    const roomSelect = document.getElementById('room_id');
    if (roomSelect) {
        const selectedRoomId = roomSelect.value;
        const selectedRoom = roomsData.find(r => r.id == selectedRoomId);
        
        if (selectedRoom) {
            const selectedRoomNameEl = document.getElementById('selected-room-name');
            const roomAddressEl = document.getElementById('room-address');
            const rentPerNightEl = document.getElementById('rent-per-night');
            
            // Determine if long-term based on check_out parameter or end_at
            const urlParams = new URLSearchParams(window.location.search);
            const checkOut = urlParams.get('check_out');
            const isLongTerm = !checkOut || checkOut === '';
            
            if (selectedRoomNameEl) selectedRoomNameEl.textContent = selectedRoom.name;
            if (roomAddressEl) roomAddressEl.value = selectedRoom.address;
            if (rentPerNightEl) {
                // Use monthly_price for long-term, base_price for short-term
                const price = isLongTerm ? (selectedRoom.monthly_price || 700) : (selectedRoom.base_price || 0);
                rentPerNightEl.textContent = '€' + parseFloat(price).toFixed(2);
            }
        }
    }
    
    // Update renter full name in signature section only (not the input field)
    const firstNameInput = document.getElementById('guest_first_name');
    const lastNameInput = document.getElementById('guest_last_name');
    const renterFullName = document.getElementById('renter-full-name');
    
    function updateRenterFullName() {
        const firstName = firstNameInput ? firstNameInput.value || '' : '';
        const lastName = lastNameInput ? lastNameInput.value || '' : '';
        const fullName = (firstName + ' ' + lastName).trim();
        if (renterFullName) renterFullName.textContent = fullName;
    }
    
    // Only update the signature section name, not the renter input fields
    if (firstNameInput) {
        firstNameInput.addEventListener('input', updateRenterFullName);
        firstNameInput.addEventListener('change', updateRenterFullName);
    }
    if (lastNameInput) {
        lastNameInput.addEventListener('input', updateRenterFullName);
        lastNameInput.addEventListener('change', updateRenterFullName);
    }
    
    // Initialize signature section name on page load
    updateRenterFullName();
    
    @php
        // Use the same logic as the main PHP block above
        // Always check request parameters first (they take precedence)
        $startAt = request()->query('check_in') ?? request()->get('check_in');
        $endAt = request()->query('check_out') ?? request()->get('check_out');
        
        // If not in request, check form data
        if (empty($startAt)) {
            $startAt = $formData['step2']['start_at'] ?? null;
        }
        if (empty($endAt)) {
            $endAt = $formData['step2']['end_at'] ?? null;
        }
        
        // Clean up empty strings and null values
        if (empty($endAt) || $endAt === '' || $endAt === null) {
            $endAt = null;
        } else {
            $endAt = trim($endAt);
            if ($endAt === '') {
                $endAt = null;
            }
        }
        
        // If end_at is null or empty, it's definitely long-term
        $isLongTermRental = ($endAt === null || $endAt === '');
        
        // If end_at exists and is not empty, check if it's short-term or long-term
        if (!$isLongTermRental && $endAt && $room->short_term_allowed && $startAt) {
            try {
                $startDate = \Carbon\Carbon::parse($startAt);
                $endDate = \Carbon\Carbon::parse($endAt);
                $nights = $startDate->diffInDays($endDate);
                // If more than 30 nights, it's long-term; otherwise short-term
                $isLongTermRental = $nights > 30;
            } catch (\Exception $e) {
                // If parsing fails, treat as long-term
                $isLongTermRental = true;
            }
        }
        
        // Ensure that if check_out is not provided in URL, it's always long-term
        if (!request()->has('check_out') && !isset($formData['step2']['end_at'])) {
            $isLongTermRental = true;
        }
    @endphp
    
    @if($isLongTermRental)
    // Initialize Signature Pad only for long-term rentals
    // Make signaturePad available globally for form submission
    let signaturePad = null;
    
    // Translation strings for JavaScript
    const translations = {
        pleaseProvideSignature: @json(__('booking.please_provide_signature')),
        signaturePadError: @json(__('booking.signature_pad_error')),
        signaturePadNotInitialized: @json(__('booking.signature_pad_not_initialized')),
        signatureInputNotFound: @json(__('booking.signature_input_not_found')),
        errorCapturingSignature: @json(__('booking.error_capturing_signature'))
    };
    
    // Wait for both DOM and SignaturePad library to be ready
    function waitForSignaturePad(callback, maxAttempts = 50) {
        let attempts = 0;
        const checkInterval = setInterval(function() {
            attempts++;
            if (typeof SignaturePad !== 'undefined') {
                clearInterval(checkInterval);
                callback();
            } else if (attempts >= maxAttempts) {
                clearInterval(checkInterval);
                console.error('SignaturePad library failed to load after ' + maxAttempts + ' attempts');
            }
        }, 100);
    }
    
    function initializeSignaturePad() {
        const canvas = document.getElementById('signature-pad');
        if (!canvas) {
            console.error('Signature pad canvas not found');
            return;
        }
        
        // Set up canvas size properly before initializing SignaturePad
        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const rect = canvas.getBoundingClientRect();
            
            // Ensure canvas has valid dimensions
            if (rect.width === 0 || rect.height === 0) {
                // Use fallback size
                const fallbackWidth = 600;
                const fallbackHeight = 200;
                canvas.width = fallbackWidth * ratio;
                canvas.height = fallbackHeight * ratio;
                canvas.style.width = fallbackWidth + 'px';
                canvas.style.height = fallbackHeight + 'px';
            } else {
                canvas.width = rect.width * ratio;
                canvas.height = rect.height * ratio;
            }
            
            const ctx = canvas.getContext("2d");
            ctx.scale(ratio, ratio);
            
            // Only clear if SignaturePad is already initialized (on resize)
            if (signaturePad) {
                signaturePad.clear();
            }
        }
        
        // Small delay to ensure canvas is fully rendered
        setTimeout(function() {
            // Initial resize before creating SignaturePad
            resizeCanvas();
            
            // Initialize SignaturePad after canvas is properly sized
            try {
                signaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'rgb(255, 255, 255)',
                    penColor: 'rgb(0, 0, 0)',
                    minWidth: 1,
                    maxWidth: 3,
                    throttle: 16,
                    velocityFilterWeight: 0.7
                });
                
                console.log('SignaturePad initialized successfully');
                
                // Make signaturePad available globally for form submission
                window.signaturePad = signaturePad;
                
                // Handle window resize - clear signature on resize
                let resizeTimeout;
                window.addEventListener("resize", function() {
                    clearTimeout(resizeTimeout);
                    resizeTimeout = setTimeout(function() {
                        resizeCanvas();
                    }, 100);
                });
                
                const clearBtn = document.getElementById('clear-signature');
                if (clearBtn) {
                    clearBtn.addEventListener('click', function() {
                        if (signaturePad) {
                            signaturePad.clear();
                        }
                    });
                }
                
                // Ensure canvas is interactive
                canvas.style.pointerEvents = 'auto';
                canvas.setAttribute('tabindex', '0');
                
                // Add visual feedback
                const container = canvas.parentElement;
                if (container) {
                    container.style.border = '2px solid #3b82f6';
                }
                
                console.log('SignaturePad initialized successfully. Canvas dimensions:', canvas.width, 'x', canvas.height);
                console.log('Canvas is ready for signature. Try drawing on it with mouse or touch.');
                
                // Test drawing capability
                canvas.addEventListener('pointerdown', function(e) {
                    console.log('Pointer down detected at:', e.clientX, e.clientY);
                });
            } catch (error) {
                console.error('Error initializing SignaturePad:', error);
                alert(translations.signaturePadError);
            }
        }, 100);
    }
    
    // Wait for DOM to be ready, then wait for SignaturePad library
    function startInitialization() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                waitForSignaturePad(initializeSignaturePad);
            });
        } else {
            waitForSignaturePad(initializeSignaturePad);
        }
    }
    
    startInitialization();
    @endif
    
    // Handle form submission
    const form = document.getElementById('step1-form');
    if (form) {
        form.addEventListener('submit', async function(e) {
            @if($isLongTermRental)
            // Check signature only for long-term rentals
            // Use window.signaturePad if available, otherwise try to get from scope
            const currentSignaturePad = window.signaturePad || signaturePad;
            
            if (!currentSignaturePad) {
                e.preventDefault();
                alert(translations.signaturePadNotInitialized);
                return false;
            }
            
            if (currentSignaturePad.isEmpty()) {
                e.preventDefault();
                alert(translations.pleaseProvideSignature);
                return false;
            }
            
            // Capture signature data before form submission
            try {
                const signatureData = currentSignaturePad.toDataURL('image/png');
                const signatureInput = document.getElementById('signature-data');
                if (signatureInput) {
                    signatureInput.value = signatureData;
                } else {
                    e.preventDefault();
                    alert(translations.signatureInputNotFound);
                    return false;
                }
            } catch (error) {
                console.error('Error capturing signature:', error);
                e.preventDefault();
                alert(translations.errorCapturingSignature);
                return false;
            }
            @endif
            
            @php
            // Recalculate isShortTerm for form submission JavaScript
            // Use the same logic as other sections
            $startAtForSubmit = request()->query('check_in') ?? request()->get('check_in');
            $endAtForSubmit = request()->query('check_out') ?? request()->get('check_out');
            
            // If not in request, check form data
            if (empty($startAtForSubmit)) {
                $startAtForSubmit = $formData['step2']['start_at'] ?? null;
            }
            if (empty($endAtForSubmit)) {
                $endAtForSubmit = $formData['step2']['end_at'] ?? null;
            }
            
            // Clean up empty strings
            if (empty($endAtForSubmit) || $endAtForSubmit === '' || $endAtForSubmit === null) {
                $endAtForSubmit = null;
            } else {
                $endAtForSubmit = trim($endAtForSubmit);
                if ($endAtForSubmit === '') {
                    $endAtForSubmit = null;
                }
            }
            
            $isShortTermForSubmit = false;
            
            // Only short-term if end_at exists, is not empty, and nights <= 30
            if ($endAtForSubmit !== null && $startAtForSubmit && $room->short_term_allowed) {
                try {
                    $startDateForSubmit = \Carbon\Carbon::parse($startAtForSubmit);
                    $endDateForSubmit = \Carbon\Carbon::parse($endAtForSubmit);
                    $nightsForSubmit = $startDateForSubmit->diffInDays($endDateForSubmit);
                    // Must be at least 1 night and <= 30 nights to be short-term
                    $isShortTermForSubmit = $nightsForSubmit >= 1 && $nightsForSubmit <= 30;
                } catch (\Exception $e) {
                    $isShortTermForSubmit = false;
                }
            }
            @endphp
            @if($isShortTermForSubmit && config('services.stripe.key'))
                // Process payment for short-term bookings
                e.preventDefault();
                const submitBtn = document.getElementById('submit-btn');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Processing Payment...';
                
                // Hide any previous error messages
                const paymentMessage = document.getElementById('payment-message');
                if (paymentMessage) {
                    paymentMessage.classList.add('hidden');
                    paymentMessage.textContent = '';
                }
                
                try {
                    // Confirm payment with Stripe
                    const {error: submitError, paymentIntent} = await stripe.confirmPayment({
                        elements,
                        confirmParams: {
                            return_url: window.location.href,
                        },
                        redirect: 'if_required'
                    });
                    
                    if (submitError) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                        if (paymentMessage) {
                            paymentMessage.textContent = submitError.message;
                            paymentMessage.classList.remove('hidden');
                        }
                        return false;
                    }
                    
                    // Check payment status
                    let finalPaymentIntent = paymentIntent;
                    
                    if (!paymentIntent || paymentIntent.status !== 'succeeded') {
                        // Retrieve payment intent to check status
                        const {error: retrieveError, paymentIntent: retrievedIntent} = await stripe.retrievePaymentIntent(clientSecret);
                        if (retrieveError) {
                            submitBtn.disabled = false;
                            submitBtn.textContent = originalText;
                            if (paymentMessage) {
                                paymentMessage.textContent = 'Failed to verify payment. Please try again.';
                                paymentMessage.classList.remove('hidden');
                            }
                            return false;
                        }
                        finalPaymentIntent = retrievedIntent;
                    }
                    
                    if (finalPaymentIntent && finalPaymentIntent.status === 'succeeded') {
                        // Store payment intent ID for form submission
                        const paymentMethodIdInput = document.getElementById('payment_method_id');
                        if (paymentMethodIdInput) {
                            paymentMethodIdInput.value = finalPaymentIntent.id || window.paymentIntentId;
                        }
                        
                        // Update button to show processing
                        submitBtn.textContent = '{{ __('booking.creating_booking') }}';
                        submitBtn.disabled = true;
                        
                        // Prevent double submission
                        if (form.dataset.submitting === 'true') {
                            return false;
                        }
                        form.dataset.submitting = 'true';
                        
                        // Submit the form - this will trigger a POST request
                        // The server will process and redirect
                        form.submit();
                    } else {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                        if (paymentMessage) {
                            paymentMessage.textContent = @json(__('booking.payment_not_successful')) + ': ' + (finalPaymentIntent ? finalPaymentIntent.status : @json(__('booking.unknown')));
                            paymentMessage.classList.remove('hidden');
                        }
                    }
                } catch (error) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    if (paymentMessage) {
                        paymentMessage.textContent = @json(__('booking.payment_processing_error')) + ': ' + error.message;
                        paymentMessage.classList.remove('hidden');
                    }
                }
            @else
            // For long-term bookings or when payment is not required, allow normal form submission
            // No payment processing needed
            @endif
        });
    }
    
    @php
        // Recalculate isShortTerm for Stripe initialization
        $startAtForStripeInit = request()->get('check_in') ?? $formData['step2']['start_at'] ?? null;
        $endAtForStripeInit = request()->get('check_out') ?? $formData['step2']['end_at'] ?? null;
        $isShortTermForStripeInit = false;
        
        if ($endAtForStripeInit && trim($endAtForStripeInit) !== '' && $startAtForStripeInit && $room->short_term_allowed) {
            try {
                $startDateForStripeInit = \Carbon\Carbon::parse($startAtForStripeInit);
                $endDateForStripeInit = \Carbon\Carbon::parse($endAtForStripeInit);
                $nightsForStripeInit = $startDateForStripeInit->diffInDays($endDateForStripeInit);
                $isShortTermForStripeInit = $nightsForStripeInit <= 30;
            } catch (\Exception $e) {
                $isShortTermForStripeInit = false;
            }
        }
    @endphp
    @if($isShortTermForStripeInit && config('services.stripe.key'))
    // Initialize Stripe Payment for short-term bookings
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        const stripeKey = '{{ config("services.stripe.key") }}';
        let stripe;
        let elements;
        let paymentElement;
        let clientSecret;
        
        if (stripeKey) {
            stripe = Stripe(stripeKey);
            
            // Initialize payment element
            (async function() {
                try {
                    // Get booking details for payment intent
                    const startAt = document.getElementById('start_at').value;
                    const endAt = document.getElementById('end_at').value;
                    const roomId = document.getElementById('room_id').value || {{ $room->id }};
                    
                    if (!startAt || !endAt) {
                        console.error('Start and end dates are required for payment');
                        return;
                    }
                    
                    // Create payment intent via API
                    const response = await fetch('{{ route("booking.payment-intent") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ 
                            room_id: roomId,
                            start_at: startAt,
                            end_at: endAt
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.client_secret) {
                        clientSecret = data.client_secret;
                        // Extract payment intent ID from client secret (format: pi_xxx_secret_xxx)
                        window.paymentIntentId = clientSecret.split('_secret_')[0];
                        
                        elements = stripe.elements({ 
                            clientSecret: clientSecret,
                            appearance: {
                                theme: 'stripe',
                            }
                        });
                        paymentElement = elements.create('payment');
                        paymentElement.mount('#payment-element');
                    } else if (data.error) {
                        document.getElementById('payment-message').textContent = data.error;
                        document.getElementById('payment-message').classList.remove('hidden');
                    }
                } catch (error) {
                    console.error('Payment initialization error:', error);
                    document.getElementById('payment-message').textContent = 'Failed to initialize payment. Please refresh the page.';
                    document.getElementById('payment-message').classList.remove('hidden');
                }
            })();
        } else {
            document.getElementById('payment-message').textContent = 'Payment processing is not configured. Please contact support.';
            document.getElementById('payment-message').classList.remove('hidden');
        }
    </script>
    @endif
</script>
@endif

@if($step == 2)
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    // Get blocked dates from bookings
    const blockedDates = @json(($bookings ?? collect())->filter(function($booking) {
        return $booking->start_at && $booking->end_at;
    })->map(function($booking) {
        return [
            \Carbon\Carbon::parse($booking->start_at)->format('Y-m-d'),
            \Carbon\Carbon::parse($booking->end_at)->format('Y-m-d')
        ];
    }));
    
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
            if (selectedDates.length === 2) {
                const start = selectedDates[0];
                const end = selectedDates[1];
                const nights = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
                const pricePerNight = {{ $room->base_price }};
                const total = nights * pricePerNight;
                
                const startDate = start.getFullYear() + '-' + 
                    String(start.getMonth() + 1).padStart(2, '0') + '-' + 
                    String(start.getDate()).padStart(2, '0');
                const endDate = end.getFullYear() + '-' + 
                    String(end.getMonth() + 1).padStart(2, '0') + '-' + 
                    String(end.getDate()).padStart(2, '0');
                
                document.getElementById('start_at').value = startDate;
                document.getElementById('end_at').value = endDate;
                
                const summary = document.getElementById('booking-summary');
                summary.classList.remove('hidden');
                summary.innerHTML = `
                    <div class="flex justify-between mb-2">
                        <span>Nights:</span>
                        <span>${nights}</span>
                    </div>
                    <div class="flex justify-between font-bold text-lg">
                        <span>Total:</span>
                        <span>€${total.toFixed(2)}</span>
                    </div>
                `;
            }
        }
    });
</script>
@endif

@if($step == 3)
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    const canvas = document.getElementById('signature-pad');
    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)',
        penColor: 'rgb(0, 0, 0)'
    });
    
    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);
        signaturePad.clear();
    }
    
    window.addEventListener("resize", resizeCanvas);
    resizeCanvas();
    
    document.getElementById('clear-signature').addEventListener('click', function() {
        signaturePad.clear();
    });
    
    document.getElementById('step3-form').addEventListener('submit', function(e) {
        if (signaturePad.isEmpty()) {
            e.preventDefault();
            alert(@json(__('booking.please_provide_signature')));
            return false;
        }
        
        const signatureData = signaturePad.toDataURL();
        document.getElementById('signature-data').value = signatureData;
    });
</script>
@endif

@push('scripts')
<script>
    // Initialize Swiper for room preview
    @if($room->images && $room->images->count() > 1)
    const roomPreviewSwiper = new Swiper('.room-preview-swiper', {
        slidesPerView: 1,
        spaceBetween: 0,
        loop: true,
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
    });
    @endif
</script>
@endpush
@endsection
