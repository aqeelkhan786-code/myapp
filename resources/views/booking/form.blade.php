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
        <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('booking_flow.amenities_comfort') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @php
                // Get amenities from room, then house, or use default
                $amenitiesText = $room->amenities_text ?? ($room->house ? $room->house->amenities_text : null);
                $defaultAmenities = [
                    __('booking_flow.amenity_large_beds'),
                    __('booking_flow.amenity_fast_wifi'),
                    __('booking_flow.amenity_weekly_cleaning'),
                    __('booking_flow.amenity_smart_tv'),
                    __('booking_flow.amenity_prices_included'),
                    __('booking_flow.amenity_washer_dryer'),
                    __('booking_flow.amenity_central_location'),
                    __('booking_flow.amenity_fully_equipped_kitchen'),
                    __('booking_flow.amenity_parking'),
                ];
                
                if ($amenitiesText) {
                    $amenities = array_filter(array_map('trim', explode("\n", $amenitiesText)));
                    // Remove emojis from amenities
                    $amenities = array_map(function($item) {
                        return preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', $item);
                    }, $amenities);
                } else {
                    $amenities = $defaultAmenities;
                }
            @endphp
            @foreach($amenities as $amenity)
                <div class="amenity-item">
                    <span class="text-gray-700">{{ $amenity }}</span>
                </div>
            @endforeach
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-8">
        @if($step == 1)
            <!-- Step 1: Rental Agreement Form -->
            <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('booking.booking_information_title') }}</h2>
            
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
                    @php
                        // Check if this is a short-term booking for address fields
                        $endAtForAddress = $formData['step2']['end_at'] ?? request()->get('check_out');
                        $startAtForAddress = $formData['step2']['start_at'] ?? request()->get('check_in');
                        $isShortTermForAddress = false;
                        
                        if ($endAtForAddress && trim($endAtForAddress) !== '' && $startAtForAddress && $room->short_term_allowed) {
                            try {
                                $startDateForAddress = \Carbon\Carbon::parse($startAtForAddress);
                                $endDateForAddress = \Carbon\Carbon::parse($endAtForAddress);
                                $nightsForAddress = $startDateForAddress->diffInDays($endDateForAddress);
                                $isShortTermForAddress = $nightsForAddress <= 30;
                            } catch (\Exception $e) {
                                $isShortTermForAddress = false;
                            }
                        }
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- 1. Vorname -->
                        <div>
                            <label for="guest_first_name" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.first_name_required') }}</label>
                            <input type="text" name="guest_first_name" id="guest_first_name" 
                                   value="{{ old('guest_first_name', $formData['step1']['guest_first_name'] ?? '') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            @error('guest_first_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- 2. Adresse (nur für kurzfristige Buchungen) -->
                        <div>
                            <label for="renter_address" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.address_required') }}</label>
                            <input type="text" name="renter_address" id="renter_address" 
                                   value="{{ old('renter_address', $formData['step2']['renter_address'] ?? '') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   {{ $isShortTermForAddress ? 'required' : '' }}>
                            @error('renter_address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- 3. Telefon -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.phone') ?? 'Handynummer' }} {{ __('common.required') ?? '*' }}</label>
                            <input type="tel" name="phone" id="phone" 
                                   value="{{ old('phone', $formData['step1']['phone'] ?? '') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- 4. Sprache -->
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
                        <!-- 5. Nachname -->
                        <div>
                            <label for="guest_last_name" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.last_name_required') }}</label>
                            <input type="text" name="guest_last_name" id="guest_last_name" 
                                   value="{{ old('guest_last_name', $formData['step1']['guest_last_name'] ?? '') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            @error('guest_last_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- 6. Postleitzahl (nur für kurzfristige Buchungen) -->
                        <div>
                            <label for="renter_postal_code" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.postal_code') ?? 'Postleitzahl' }} {{ $isShortTermForAddress ? '*' : '' }}</label>
                            <input type="text" name="renter_postal_code" id="renter_postal_code" 
                                   value="{{ old('renter_postal_code', $formData['step2']['renter_postal_code'] ?? '') }}" 
                                   placeholder="{{ __('booking.postal_code') ?? 'Postleitzahl' }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   {{ $isShortTermForAddress ? 'required' : '' }}>
                            @error('renter_postal_code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- 7. Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.email') ?? 'E-Mail-Adresse' }} {{ __('common.required') ?? '*' }}</label>
                            <input type="email" name="email" id="email" 
                                   value="{{ old('email', $formData['step1']['email'] ?? '') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- 8. Stadt (nur für kurzfristige Buchungen) -->
                        <div>
                            <label for="renter_city" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.city') ?? 'Stadt' }} {{ $isShortTermForAddress ? '*' : '' }}</label>
                            <input type="text" name="renter_city" id="renter_city" 
                                   value="{{ old('renter_city', $formData['step2']['renter_city'] ?? '') }}" 
                                   placeholder="{{ __('booking.city') ?? 'Stadt' }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   {{ $isShortTermForAddress ? 'required' : '' }}>
                            @error('renter_city')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- 9. Kommunikation Checkbox -->
                        <div class="md:col-span-2">
                            <div class="flex items-start">
                                <input type="checkbox" name="communication_preference" id="communication_preference" 
                                       value="Mail,Whatsapp"
                                       {{ old('communication_preference', $formData['step1']['communication_preference'] ?? '') == 'Mail,Whatsapp' ? 'checked' : '' }}
                                       class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" required>
                                <label for="communication_preference" class="ml-2 block text-sm font-medium text-gray-700">
                                    {{ __('booking.communication_agreement') }} <span class="text-red-600">*</span>
                                </label>
                            </div>
                            @error('communication_preference')
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
                        <label for="rental_period" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.rental_start') ?? 'Mietbeginn' }}</label>
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
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('booking.booking_information_title') }}</h2>
                    
                    <!-- Landlord Section (Hidden/Prefilled) -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-md">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('booking.landlord') }}</h3>
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

                    <!-- Rental Period -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Mietdauer</h3>
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
                        
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <h4 class="text-md font-semibold text-gray-900 mb-2">Kündigungsfrist</h4>
                            <p class="text-sm text-gray-600 leading-relaxed">
                                Mieter und Vermieter können den Mietvertrag mit einer Frist von 1 Monat zum Monatsende kündigen.<br>
                                Die Kündigung muss schriftlich erfolgen (WhatsApp oder E-Mail) und spätestens am letzten Tag des Vormonats bei der anderen Partei eingehen.
                            </p>
                        </div>
                    </div>

                    <!-- Rental Fee -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Miete</h3>
                        @php
                            $endAt = $formData['step2']['end_at'] ?? request()->get('check_out');
                            $isLongTerm = empty($endAt) || $endAt === null || trim($endAt) === '';
                        @endphp
                        <p class="text-sm text-gray-700 mb-4">
                            Die Miete beträgt <strong id="rent-per-night">€{{ number_format($isLongTerm ? ($room->monthly_price ?? 700) : ($room->base_price ?? 0), 2) }}</strong> {{ $isLongTerm ? 'pro Monat' : __('booking.per_night_text') }}.
                        </p>
                        
                        <div class="mb-4 mt-4 pt-4 border-t border-gray-200">
                            <h4 class="text-md font-semibold text-gray-900 mb-2">In der Miete enthalten:</h4>
                            <ul class="text-sm text-gray-700 space-y-1 list-disc list-inside ml-4">
                                <li>Heizung, Warmwasser, Wasser, Abwasser</li>
                                <li>Strom, Gas</li>
                                <li>Internet</li>
                                <li>Reinigung der Gemeinschaftsräume</li>
                                <li>Bettwäsche, Handtücher</li>
                            </ul>
                        </div>
                        
                        <div class="mt-4 p-3 bg-blue-50 border-l-4 border-blue-500 rounded">
                            <p class="text-sm text-gray-700">
                                <strong>Hinweis zur Anpassung:</strong><br>
                                Aufgrund steigender Gas- und Energiepreise kann die Gesamtmiete steigen. Eine Erhöhung wird mindestens 1 Monat im Voraus angekündigt.
                            </p>
                        </div>
                    </div>

                    <!-- Payment of the rent -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Zahlung der Miete</h3>
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
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Kaution</h3>
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
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Rechte, Pflichten & Hausregeln</h3>
                        <p class="text-sm text-gray-700 mb-4">Der Mieter verpflichtet sich insbesondere zu folgenden Punkten:</p>
                        <ul class="text-sm text-gray-700 space-y-3">
                            <li class="flex items-start">
                                <span class="mr-2 text-blue-600">•</span>
                                <span><strong>Sauberkeit:</strong> Wohnung, Küche und Bad sind sauber und ordentlich zu halten.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2 text-blue-600">•</span>
                                <span><strong>Sorgfalt:</strong> Möbel und Gegenstände sind pfleglich zu behandeln und im ursprünglichen Zustand zu hinterlassen.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2 text-blue-600">•</span>
                                <span><strong>Hausordnung & Müll:</strong> Hausordnung beachten, Mülltrennung einhalten (Mülltonnen im Hof).</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2 text-blue-600">•</span>
                                <span><strong>Schäden melden:</strong> Schäden sind sofort zu melden. Bei verspäteter Meldung haftet der Mieter für Folgeschäden.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2 text-blue-600">•</span>
                                <span><strong>Rauchverbot:</strong> Im gesamten Apartment und Treppenhaus gilt absolutes Rauchverbot.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2 text-blue-600">•</span>
                                <span><strong>Keine weiteren Personen:</strong> Unterbringung anderer Personen ist untersagt; längere Besuche nur nach Absprache.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2 text-blue-600">•</span>
                                <span><strong>Zutritt Vermieter:</strong> Vermieter/Beauftragte dürfen die Räume bis zu 2× pro Monat zur Zustandsprüfung betreten.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2 text-blue-600">•</span>
                                <span><strong>Ruhezeiten:</strong> 12–14 Uhr sowie 22–6 Uhr ist Lärm untersagt.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2 text-blue-600">•</span>
                                <span><strong>Bodenpflege:</strong> Böden trocken halten und sachgemäß behandeln, um Schäden zu vermeiden.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2 text-blue-600">•</span>
                                <span><strong>Konsequenz bei Verstoß:</strong> Regelverstöße können zur sofortigen Kündigung führen.</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Important Notice -->
                    <div class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Wichtiger Hinweis zur Buchung</h3>
                        <p class="text-sm text-gray-700 leading-relaxed">
                            Mit dem Absenden der Buchung schließen Sie einen Mietvertrag (Untermietvertrag) ab.<br>
                            Der Vertrag ist verbindlich und wird digital unterschrieben bzw. mit Ihrer Unterschrift bestätigt.
                        </p>
                    </div>

                    <!-- Confirmation -->
                    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Bestätigung</h3>
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
                    <div class="mb-4" style="touch-action: none;">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.signature_required') }}</label>
                        <div class="border-2 border-dashed border-gray-400 rounded-lg bg-white overflow-hidden" style="max-width: 600px; min-height: 200px; touch-action: none;">
                            <canvas id="signature-pad" width="600" height="200" style="width: 100%; max-width: 600px; height: 200px; min-height: 200px; display: block; touch-action: none; cursor: crosshair; -webkit-user-select: none; user-select: none; pointer-events: auto; -webkit-tap-highlight-color: transparent;"></canvas>
                        </div>
                        @error('signature')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">{{ __('booking.signature_instruction') }}</p>
                        <button type="button" id="clear-signature" class="mt-2 text-sm text-blue-600 hover:text-blue-800 underline">{{ __('booking.clear_signature') }}</button>
                        <input type="hidden" name="signature" id="signature-data">
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
                    <!-- Payment will be handled on billing page -->
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-md">
                        <p class="text-sm text-blue-800 font-semibold mb-2">ℹ️ Payment Information</p>
                        <p class="text-sm text-blue-700 mb-4">You will be redirected to a secure payment page after completing the booking form.</p>
                        <p class="text-sm text-blue-600">Total amount to pay: <strong>€{{ number_format($totalAmount, 2) }}</strong></p>
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
                    <div class="border border-gray-300 rounded-md bg-white" style="max-width: 600px; position: relative;">
                        <canvas id="signature-pad" style="width: 100%; max-width: 600px; height: 200px; display: block; touch-action: none; cursor: crosshair; -webkit-user-select: none; user-select: none; pointer-events: auto; -webkit-tap-highlight-color: transparent;"></canvas>
                    </div>
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
@php
    $startAt = request()->query('check_in') ?? request()->get('check_in');
    $endAt = request()->query('check_out') ?? request()->get('check_out');
    if (empty($startAt)) { $startAt = $formData['step2']['start_at'] ?? null; }
    if (empty($endAt)) { $endAt = $formData['step2']['end_at'] ?? null; }
    if (empty($endAt) || $endAt === '' || $endAt === null) { $endAt = null; } else { $endAt = trim($endAt); if ($endAt === '') { $endAt = null; } }
    $isLongTermRental = ($endAt === null || $endAt === '');
    if (!$isLongTermRental && $endAt && $room->short_term_allowed && $startAt) {
        try {
            $sd = \Carbon\Carbon::parse($startAt);
            $ed = \Carbon\Carbon::parse($endAt);
            $isLongTermRental = $sd->diffInDays($ed) > 30;
        } catch (\Exception $e) { $isLongTermRental = true; }
    }
    if (!request()->has('check_out') && !isset($formData['step2']['end_at'])) { $isLongTermRental = true; }
    $bookingFormConfig = [
        'roomsData' => $roomsData ?? [],
        'isLongTermRental' => $isLongTermRental,
        'translations' => [
            'pleaseProvideSignature' => __('booking.please_provide_signature'),
            'signaturePadError' => __('booking.signature_pad_error'),
            'signaturePadNotInitialized' => __('booking.signature_pad_not_initialized'),
            'signatureInputNotFound' => __('booking.signature_input_not_found'),
            'errorCapturingSignature' => __('booking.error_capturing_signature'),
        ],
    ];
@endphp
<script type="application/json" id="booking-form-config">@json($bookingFormConfig)</script>
<script src="{{ asset('js/booking-form-step1.js') }}"></script>
@endif

@if($step == 2)
@php
    // Process bookings in PHP first (can't use closures in @json)
    $blockedDates = collect($bookings ?? [])
        ->filter(function($booking) {
            return $booking->start_at && $booking->end_at;
        })
        ->map(function($booking) {
            return [
                \Carbon\Carbon::parse($booking->start_at)->format('Y-m-d'),
                \Carbon\Carbon::parse($booking->end_at)->format('Y-m-d')
            ];
        })
        ->values()
        ->all();
@endphp
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    // Get blocked dates from bookings (processed in PHP above)
    const blockedDates = @json($blockedDates);
    
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
<script>
    console.log('🚀 Step 3 SignaturePad Script Started');
    console.log('📋 Current step:', {{ $step ?? 'undefined' }});
    console.log('📚 SignaturePad library available:', typeof SignaturePad !== 'undefined');
    
    // Wait for DOM and SignaturePad library
    function initializeStep3SignaturePad() {
        console.log('🔄 initializeStep3SignaturePad() called');
        console.log('📄 Document ready state:', document.readyState);
        
        const canvas = document.getElementById('signature-pad');
        console.log('🎯 Canvas element:', canvas);
        
        if (!canvas) {
            console.error('❌ Signature pad canvas not found');
            console.log('🔍 Searching for canvas with id="signature-pad"...');
            const allCanvases = document.querySelectorAll('canvas');
            console.log('📊 Total canvas elements found:', allCanvases.length);
            allCanvases.forEach((c, i) => {
                console.log(`  Canvas ${i}: id="${c.id}", class="${c.className}"`);
            });
            return;
        }
        
        console.log('✅ Canvas found:', {
            id: canvas.id,
            className: canvas.className,
            tagName: canvas.tagName,
            parentElement: canvas.parentElement?.tagName
        });
        
        // Wait for SignaturePad library to load
        if (typeof SignaturePad === 'undefined') {
            console.log('⏳ Waiting for SignaturePad library... (attempt ' + (window.signaturePadAttempts || 0) + ')');
            window.signaturePadAttempts = (window.signaturePadAttempts || 0) + 1;
            if (window.signaturePadAttempts > 50) {
                console.error('❌ SignaturePad library failed to load after 50 attempts');
                return;
            }
            setTimeout(initializeStep3SignaturePad, 100);
            return;
        }
        
        console.log('✅ SignaturePad library loaded');
        
        // Check if canvas is visible and has dimensions
        const style = window.getComputedStyle(canvas);
        const rect = canvas.getBoundingClientRect();
        
        console.log('📏 Canvas dimensions check:', {
            display: style.display,
            visibility: style.visibility,
            opacity: style.opacity,
            pointerEvents: style.pointerEvents,
            boundingRect: {
                width: rect.width,
                height: rect.height,
                top: rect.top,
                left: rect.left
            },
            offsetWidth: canvas.offsetWidth,
            offsetHeight: canvas.offsetHeight,
            clientWidth: canvas.clientWidth,
            clientHeight: canvas.clientHeight
        });
        
        // If canvas dimensions are 0, wait a bit and retry
        if (rect.width === 0 || rect.height === 0) {
            console.log('⏳ Canvas not ready, retrying...', { 
                width: rect.width, 
                height: rect.height,
                attempt: window.canvasReadyAttempts || 0
            });
            window.canvasReadyAttempts = (window.canvasReadyAttempts || 0) + 1;
            if (window.canvasReadyAttempts > 10) {
                console.error('❌ Canvas dimensions still 0 after 10 attempts');
                console.log('💡 Trying to set fallback dimensions...');
                canvas.width = 600;
                canvas.height = 200;
            } else {
                setTimeout(initializeStep3SignaturePad, 200);
                return;
            }
        }
        
        // Set canvas dimensions WITHOUT scaling BEFORE initializing SignaturePad
        // This is CRITICAL - SignaturePad needs 1:1 coordinate mapping
        const displayWidth = Math.floor(rect.width) || 600;
        const displayHeight = Math.floor(rect.height) || 200;
        
        // Set canvas internal dimensions to display size (not multiplied by devicePixelRatio)
        canvas.width = displayWidth;
        canvas.height = displayHeight;
        
        // Ensure canvas is interactive
        canvas.style.pointerEvents = 'auto';
        canvas.style.touchAction = 'none';
        canvas.style.cursor = 'crosshair';
        
        console.log('📐 Canvas dimensions set:', {
            internal: canvas.width + 'x' + canvas.height,
            bounding: rect.width + 'x' + rect.height
        });
        
        // Initialize SignaturePad AFTER dimensions are set (NO scaling!)
        console.log('🎨 Initializing SignaturePad...');
        const signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)',
            minWidth: 1,
            maxWidth: 3,
            throttle: 16,
            velocityFilterWeight: 0.7
        });
        
        // Make it globally accessible
        window.signaturePad = signaturePad;
        
        console.log('✅ SignaturePad initialized successfully', {
            isEmpty: signaturePad.isEmpty(),
            canvasWidth: canvas.width,
            canvasHeight: canvas.height
        });
        
        // Add comprehensive event listeners for debugging
        canvas.addEventListener('mousedown', function(e) {
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            console.log('🖱️ Mouse down on canvas:', {
                clientX: e.clientX,
                clientY: e.clientY,
                canvasX: x,
                canvasY: y,
                button: e.button,
                buttons: e.buttons
            });
        });
        
        canvas.addEventListener('mousemove', function(e) {
            if (e.buttons === 1) {
                console.log('🖱️ Mouse move (dragging)');
            }
        });
        
        canvas.addEventListener('mouseup', function(e) {
            console.log('🖱️ Mouse up on canvas');
        });
        
        canvas.addEventListener('touchstart', function(e) {
            console.log('👆 Touch start on canvas:', {
                touches: e.touches.length,
                target: e.target.tagName
            });
            e.preventDefault();
        }, { passive: false });
        
        canvas.addEventListener('touchmove', function(e) {
            console.log('👆 Touch move on canvas');
            e.preventDefault();
        }, { passive: false });
        
        canvas.addEventListener('touchend', function(e) {
            console.log('👆 Touch end on canvas');
        });
        
        canvas.addEventListener('click', function(e) {
            console.log('🖱️ Canvas clicked');
        });
        
        // Check SignaturePad event system
        console.log('🔍 SignaturePad event system check:', {
            hasAddEventListener: typeof signaturePad.addEventListener === 'function',
            hasOn: typeof signaturePad.on === 'function',
            signaturePadType: typeof signaturePad
        });
        
        if (signaturePad && typeof signaturePad.addEventListener === 'function') {
            signaturePad.addEventListener('beginStroke', function() {
                console.log('✏️ Stroke began - drawing is working!');
            });
            signaturePad.addEventListener('endStroke', function() {
                console.log('✏️ Stroke ended', { isEmpty: signaturePad.isEmpty() });
            });
        } else {
            console.warn('⚠️ SignaturePad does not have addEventListener method');
        }
        
        // Test if we can manually draw on the canvas context
        try {
            const ctx = canvas.getContext('2d');
            if (ctx) {
                console.log('✅ Canvas 2D context available');
                // Draw a small test dot to verify context works
                ctx.fillStyle = 'rgba(200, 200, 200, 0.5)';
                ctx.fillRect(10, 10, 5, 5);
                console.log('✅ Test dot drawn - canvas context is working');
            } else {
                console.error('❌ Cannot get 2D context from canvas');
            }
        } catch (error) {
            console.error('❌ Error testing canvas context:', error);
        }
        
        console.log('🎉 All event listeners attached and ready!');
        
        // Handle window resize (without scaling - keep 1:1 coordinates)
        let resizeTimeout;
        window.addEventListener("resize", function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                const rect = canvas.getBoundingClientRect();
                const newWidth = Math.floor(rect.width) || 600;
                const newHeight = Math.floor(rect.height) || 200;
                
                // Only resize if dimensions changed
                if (canvas.width !== newWidth || canvas.height !== newHeight) {
                    canvas.width = newWidth;
                    canvas.height = newHeight;
                    signaturePad.clear();
                    console.log('📐 Canvas resized to:', newWidth + 'x' + newHeight);
                }
            }, 100);
        });
        
        // Clear button
        const clearBtn = document.getElementById('clear-signature');
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                signaturePad.clear();
            });
        }
        
        // Form submission
        const form = document.getElementById('step3-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (signaturePad.isEmpty()) {
                    e.preventDefault();
                    alert(@json(__('booking.please_provide_signature')));
                    return false;
                }
                
                const signatureData = signaturePad.toDataURL();
                const signatureInput = document.getElementById('signature-data');
                if (signatureInput) {
                    signatureInput.value = signatureData;
                }
            });
        }
    }
    
    // Initialize when DOM is ready
    function startInitialization() {
        console.log('🚀 startInitialization() called');
        console.log('📄 Document ready state:', document.readyState);
        
        if (document.readyState === 'loading') {
            console.log('⏳ Document still loading, waiting for DOMContentLoaded...');
            document.addEventListener('DOMContentLoaded', function() {
                console.log('✅ DOMContentLoaded fired');
                // Small delay to ensure canvas is rendered
                setTimeout(function() {
                    console.log('⏰ Initialization timeout fired (150ms)');
                    initializeStep3SignaturePad();
                }, 150);
            });
        } else {
            console.log('✅ Document already ready');
            // Small delay to ensure canvas is rendered
            setTimeout(function() {
                console.log('⏰ Initialization timeout fired (150ms)');
                initializeStep3SignaturePad();
            }, 150);
        }
    }
    
    console.log('🎬 Starting initialization process...');
    startInitialization();
</script>
@endif

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
