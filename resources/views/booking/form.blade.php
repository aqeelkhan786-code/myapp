@extends('layouts.app')

@section('title', __('booking.booking_form') . ' - ' . __('booking.step') . ' ' . $step)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
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
                        <div>
                            <label for="job" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.job_required') }}</label>
                            <input type="text" name="job" id="job" 
                                   value="{{ old('job', $formData['step1']['job'] ?? '') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            @error('job')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
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

                <!-- Select Appartment -->
                <div class="mb-6">
                    <label for="room_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.select_apartment') }}</label>
                    <select name="room_id" id="room_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed" 
                            disabled>
                        <option value="">{{ __('booking.select_apartment') }}</option>
                        @foreach($allRooms ?? [] as $apartment)
                            <option value="{{ $apartment->id }}" {{ old('room_id', $room->id) == $apartment->id ? 'selected' : '' }}>
                                {{ $apartment->name }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="room_id" value="{{ $room->id }}">
                    <p class="mt-1 text-xs text-gray-500">{{ __('booking.apartment_cannot_change') ?? 'Dieses Feld kann nicht geändert werden, da die Wohnung bereits ausgewählt wurde.' }}</p>
                </div>

                <!-- Select Date -->
                <div class="mb-8">
                    <label for="booking_dates" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.select_date') ?? 'Datum auswählen' }}</label>
                    @php
                        $dateDisplay = '';
                        // Get dates from formData or request parameters
                        $startAt = $formData['step2']['start_at'] ?? request()->get('check_in');
                        $endAt = $formData['step2']['end_at'] ?? request()->get('check_out');
                        
                        if ($startAt) {
                            $startDate = \Carbon\Carbon::parse($startAt)->format('Y-m-d');
                            // Check if end_at exists and is not empty/null
                            if (!empty($endAt) && $endAt !== null && trim($endAt) !== '') {
                                $endDate = \Carbon\Carbon::parse($endAt)->format('Y-m-d');
                                $dateDisplay = $startDate . ' ' . __('booking.to') . ' ' . $endDate;
                            } else {
                                $dateDisplay = $startDate . ' (' . __('booking.long_term_rental') . ')';
                            }
                        }
                    @endphp
                    <input type="text" id="booking_dates" name="booking_dates" 
                           value="{{ old('booking_dates', $dateDisplay) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed" 
                           readonly disabled>
                    <input type="hidden" name="start_at" id="start_at" value="{{ old('start_at', $formData['step2']['start_at'] ?? '') }}">
                    <input type="hidden" name="end_at" id="end_at" value="{{ old('end_at', $formData['step2']['end_at'] ?? '') }}">
                    <p class="mt-1 text-xs text-gray-500">{{ __('booking.date_cannot_change') ?? 'Dieses Feld kann nicht geändert werden, da das Datum bereits ausgewählt wurde.' }}</p>
                </div>

                <!-- RENTAL AGREEMENT Section -->
                <div class="mb-8 border-t pt-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ strtoupper(__('booking.rental_agreement_title')) }}</h2>
                    
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
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('booking.rental_property') }}:</h3>
                        <p class="text-sm text-gray-600 mb-2"><strong>{{ __('booking.this_field_hidden') }}</strong></p>
                        <p class="text-sm text-gray-700 mb-2"><strong id="selected-room-name">{{ $room->name ?? 'N/A' }}</strong></p>
                        <p class="text-sm text-gray-600 mb-4">{{ __('booking.including_shared_use') }}</p>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.address_required') }}</label>
                            <input type="text" id="room-address" value="{{ $room->property->address ?? 'N/A' }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                        </div>
                        <p class="text-sm text-gray-600 mt-4">
                            {!! __('booking.keys_info') !!}
                        </p>
                        <p class="text-sm text-gray-600 mt-2">
                            {{ __('booking.keys_prohibition') }}
                        </p>
                    </div>

                    <!-- Rental Period -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('booking.rental_period') }}</h3>
                        <p class="text-sm text-gray-600 mb-2"><strong>{{ __('booking.this_field_hidden') }}</strong></p>
                        <p class="text-sm text-gray-700 mb-2">
                            <strong>{{ __('booking.tenancy_from') }}</strong> <span id="tenancy-from">{{ isset($formData['step2']['start_at']) ? \Carbon\Carbon::parse($formData['step2']['start_at'])->format('d.m.Y') : '[Datum auswählen]' }}</span>
                        </p>
                        <p class="text-sm text-gray-700 mb-2">{{ __('booking.for_one_year') }}</p>
                        <p class="text-sm text-gray-600">
                            {{ __('booking.notice_period_text') }}
                        </p>
                    </div>

                    <!-- Rental Fee -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('booking.rental_fee') }}</h3>
                        <p class="text-sm text-gray-600 mb-2"><strong>{{ __('booking.this_field_hidden') }}</strong></p>
                        @php
                            $endAt = $formData['step2']['end_at'] ?? request()->get('check_out');
                            $isLongTerm = empty($endAt) || $endAt === null || trim($endAt) === '';
                        @endphp
                        <p class="text-sm text-gray-700 mb-2">
                            {{ __('booking.rent_is') }} <strong id="rent-per-night">€{{ number_format($isLongTerm ? ($room->monthly_price ?? 700) : ($room->base_price ?? 0), 2) }}</strong> {{ $isLongTerm ? (__('booking.month') ?? '/Monat') : __('booking.per_night_text') }}
                        </p>
                        <p class="text-sm text-gray-600">
                            {!! __('booking.additional_costs') !!}
                        </p>
                    </div>

                    <!-- Payment of the rent -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('booking.payment_of_rent') }}</h3>
                        <p class="text-sm text-gray-600 mb-2"><strong>{{ __('booking.this_field_hidden') }}</strong></p>
                        <p class="text-sm text-gray-600">
                            {!! __('booking.rent_transfer_info') !!}
                        </p>
                    </div>

                    <!-- Deposit -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('booking.deposit') }}</h3>
                        <p class="text-sm text-gray-600 mb-2"><strong>{{ __('booking.this_field_hidden') }}</strong></p>
                        <p class="text-sm text-gray-700 mb-2">
                            {{ __('booking.deposit_is') }} <strong>780€</strong>
                        </p>
                        <p class="text-sm text-gray-600">
                            {!! __('booking.deposit_transfer_info') !!}
                        </p>
                    </div>

                    <!-- Renter's Rights, Obligations and Liability -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('booking.renters_rights') }}</h3>
                        <ul class="text-sm text-gray-600 space-y-2 list-disc list-inside">
                            <li>{{ __('booking.renter_obligations.clean_apartment') }}</li>
                            <li>{{ __('booking.renter_obligations.care_items') }}</li>
                            <li>{{ __('booking.renter_obligations.house_rules') }}</li>
                            <li>{{ __('booking.renter_obligations.report_damage') }}</li>
                            <li>{{ __('booking.renter_obligations.no_smoking') }}</li>
                            <li>{{ __('booking.renter_obligations.no_accommodating') }}</li>
                            <li>{{ __('booking.renter_obligations.landlord_access') }}</li>
                            <li>{{ __('booking.renter_obligations.quiet_times') }}</li>
                            <li>{{ __('booking.renter_obligations.floors_dry') }}</li>
                            <li>{{ __('booking.renter_obligations.termination') }}</li>
                        </ul>
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.place_date') }}:</label>
                            <input type="text" value="{{ \Carbon\Carbon::now()->format('d.m.Y') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.place_date') }}: {{ __('common.required') ?? '*' }}</label>
                            <input type="text" value="{{ \Carbon\Carbon::now()->format('d.m.Y') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                        </div>
                    </div>

                    <!-- Signature Pad -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.signature_required') }}</label>
                        <canvas id="signature-pad" class="border border-gray-300 rounded-md" width="600" height="200"></canvas>
                        <button type="button" id="clear-signature" class="mt-2 text-sm text-gray-600 hover:text-gray-800">Clear Signature</button>
                        <input type="hidden" name="signature" id="signature-data">
                    </div>
                </div>
                
                @if($isShortTerm && config('services.stripe.key'))
                <!-- Payment Section for Short-term Bookings -->
                <div class="mb-8 border-t pt-8">
                    <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                        <p class="text-sm text-yellow-800 font-semibold mb-2">{{ __('booking.payment_required_short_term') }}</p>
                        <p class="text-sm text-yellow-700">{{ __('booking.total_amount') }}: <strong>€{{ number_format($totalAmount, 2) }}</strong></p>
                        <p class="text-xs text-yellow-600 mt-1">{{ __('booking.payment_processed_stripe') }}</p>
                    </div>
                    
                    <!-- Stripe Payment Element -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.payment_information') }} *</label>
                        <div id="payment-element" class="p-4 border border-gray-300 rounded-md bg-white">
                            <!-- Stripe Elements will create form elements here -->
                        </div>
                        <div id="payment-message" class="mt-2 text-sm text-red-600 hidden"></div>
                        <input type="hidden" name="payment_method_id" id="payment_method_id">
                    </div>
                </div>
                @elseif($isShortTerm && !config('services.stripe.key'))
                <div class="mb-8 border-t pt-8">
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-md">
                        <p class="text-sm text-red-800 font-semibold mb-2">{{ __('booking.payment_processing_not_configured') }}</p>
                        <p class="text-sm text-red-700">{{ __('booking.contact_support_complete_booking') }}</p>
                    </div>
                </div>
                @endif
                
                <div class="flex justify-end">
                    <button type="submit" id="submit-btn" class="bg-green-600 text-white py-2 px-6 rounded-md hover:bg-green-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed">
                        {{ __('booking.complete_booking') }}
                    </button>
                </div>
            </form>
            
        @else
            <!-- Steps 2 and 3 are admin-only and not visible to regular users -->
            <div class="text-center py-12">
                <p class="text-gray-500 text-lg">This step is not available. Please contact support if you need assistance.</p>
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
                    <label class="block text-sm font-medium text-gray-700 mb-2">Signature *</label>
                    <canvas id="signature-pad" class="border border-gray-300 rounded-md" width="600" height="200"></canvas>
                    <button type="button" id="clear-signature" class="mt-2 text-sm text-gray-600 hover:text-gray-800">Clear Signature</button>
                    <input type="hidden" name="signature" id="signature-data">
                    @error('signature')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex justify-between">
                    <a href="{{ route('booking.form', ['room' => $room->id, 'step' => 2]) }}" 
                       class="bg-gray-200 text-gray-700 py-2 px-6 rounded-md hover:bg-gray-300 transition-colors">
                        Previous
                    </a>
                    <button type="submit" class="bg-green-600 text-white py-2 px-6 rounded-md hover:bg-green-700 transition-colors">
                        Complete Booking
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
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

<script>
    // Room data for updates (passed from controller)
    const roomsData = @json($roomsData ?? []);
    
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
            
            if (selectedRoomNameEl) selectedRoomNameEl.textContent = selectedRoom.name;
            if (roomAddressEl) roomAddressEl.value = selectedRoom.address;
            if (rentPerNightEl) rentPerNightEl.textContent = '€' + parseFloat(selectedRoom.price).toFixed(2);
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
    
    // Initialize Signature Pad
    const canvas = document.getElementById('signature-pad');
    if (canvas) {
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
        
        const clearBtn = document.getElementById('clear-signature');
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                signaturePad.clear();
            });
        }
        
        // Handle form submission
        const form = document.getElementById('step1-form');
        if (form) {
            form.addEventListener('submit', async function(e) {
                if (signaturePad.isEmpty()) {
                    e.preventDefault();
                    alert('Please provide your signature');
                    return false;
                }
                
                const signatureData = signaturePad.toDataURL();
                const signatureInput = document.getElementById('signature-data');
                if (signatureInput) {
                    signatureInput.value = signatureData;
                }
                
                @if($isShortTerm && config('services.stripe.key'))
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
                            paymentMessage.textContent = 'Payment was not successful. Status: ' + (finalPaymentIntent ? finalPaymentIntent.status : 'unknown');
                            paymentMessage.classList.remove('hidden');
                        }
                    }
                } catch (error) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    if (paymentMessage) {
                        paymentMessage.textContent = 'Payment processing error: ' + error.message;
                        paymentMessage.classList.remove('hidden');
                    }
                }
                @else
                // For long-term bookings, allow normal form submission
                // No payment processing needed
                @endif
            });
        }
    }
    
    @if($isShortTerm && config('services.stripe.key'))
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
    const blockedDates = @json(($bookings ?? collect())->map(function($booking) {
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
            alert('Please provide your signature');
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

