@extends('layouts.app')

@section('title', 'Booking - Step ' . $step)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Progress Steps -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 rounded-full {{ $step >= 1 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600' }}">
                    1
                </div>
                <span class="ml-2 text-sm font-medium {{ $step >= 1 ? 'text-blue-600' : 'text-gray-600' }}">{{ __('booking.rental_agreement') }}</span>
            </div>
            <div class="flex-1 h-1 mx-4 {{ $step > 1 ? 'bg-blue-600' : 'bg-gray-200' }}"></div>
            <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 rounded-full {{ $step >= 2 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600' }}">
                    2
                </div>
                <span class="ml-2 text-sm font-medium {{ $step >= 2 ? 'text-blue-600' : 'text-gray-600' }}">{{ __('booking.wohnungsgeberbescheinigung') }}</span>
            </div>
            <div class="flex-1 h-1 mx-4 {{ $step > 2 ? 'bg-blue-600' : 'bg-gray-200' }}"></div>
            <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 rounded-full {{ $step >= 3 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600' }}">
                    3
                </div>
                <span class="ml-2 text-sm font-medium {{ $step >= 3 ? 'text-blue-600' : 'text-gray-600' }}">{{ __('booking.mietschuldsbefreiung') }}</span>
            </div>
        </div>
    </div>
    
    <!-- Room Preview -->
    <div class="mb-8 bg-white rounded-lg shadow-md overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-6">
            <div class="md:col-span-1">
                @if($booking->room->images->count() > 0)
                    <img src="{{ asset('storage/' . $booking->room->images->first()->path) }}" 
                         alt="{{ $booking->room->name }}" 
                         class="w-full h-48 object-cover rounded-lg">
                @else
                    <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=400&h=300&fit=crop" 
                         alt="{{ $booking->room->name }}" 
                         class="w-full h-48 object-cover rounded-lg">
                @endif
            </div>
            <div class="md:col-span-2">
                <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $booking->room->name }}</h3>
                <p class="text-gray-600 mb-4">{{ Str::limit($booking->room->description, 150) }}</p>
                <div class="flex flex-wrap gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">{{ __('booking.check_in') }}:</span>
                        <span class="font-semibold ml-1">{{ \Carbon\Carbon::parse($booking->start_at)->format('M d, Y') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">{{ __('booking.check_out') }}:</span>
                        <span class="font-semibold ml-1">{{ \Carbon\Carbon::parse($booking->end_at)->format('M d, Y') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">{{ __('booking.total') }}:</span>
                        <span class="font-semibold ml-1">€{{ number_format($booking->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-8">
        @if($step == 1)
            <!-- Step 1: Appartment Step Form -->
            <h2 class="text-2xl font-bold text-gray-900 mb-6">appartment step form</h2>
            
            <form action="{{ route('booking.save-step', ['booking' => $booking->id, 'step' => 1]) }}" method="POST" id="step1-form">
                @csrf
                
                <!-- Personal Information Section -->
                <div class="mb-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="guest_first_name" class="block text-sm font-medium text-gray-700 mb-2">First name(Required)</label>
                            <input type="text" name="guest_first_name" id="guest_first_name" 
                                   value="{{ old('guest_first_name', $booking->guest_first_name) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            @error('guest_first_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="guest_last_name" class="block text-sm font-medium text-gray-700 mb-2">Last name(Required)</label>
                            <input type="text" name="guest_last_name" id="guest_last_name" 
                                   value="{{ old('guest_last_name', $booking->guest_last_name) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            @error('guest_last_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="job" class="block text-sm font-medium text-gray-700 mb-2">Job(Required)</label>
                            <input type="text" name="job" id="job" 
                                   value="{{ old('job', $booking->job) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            @error('job')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="language" class="block text-sm font-medium text-gray-700 mb-2">Sprache(Required)</label>
                            <select name="language" id="language" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Select Language</option>
                                <option value="Deutsch" {{ old('language', $booking->language) == 'Deutsch' ? 'selected' : '' }}>Deutsch</option>
                                <option value="Englisch" {{ old('language', $booking->language) == 'Englisch' ? 'selected' : '' }}>Englisch</option>
                            </select>
                            @error('language')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="communication_preference" class="block text-sm font-medium text-gray-700 mb-2">Kommunikation(Required)</label>
                            <select name="communication_preference" id="communication_preference" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Select Communication</option>
                                <option value="Mail" {{ old('communication_preference', $booking->communication_preference) == 'Mail' ? 'selected' : '' }}>Mail</option>
                                <option value="Whatsapp" {{ old('communication_preference', $booking->communication_preference) == 'Whatsapp' ? 'selected' : '' }}>Whatsapp</option>
                            </select>
                            @error('communication_preference')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Handynummer(Required)</label>
                            <input type="tel" name="phone" id="phone" 
                                   value="{{ old('phone', $booking->phone) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-Mail-Adresse(Required)</label>
                            <input type="email" name="email" id="email" 
                                   value="{{ old('email', $booking->email) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Select Appartment -->
                <div class="mb-6">
                    <label for="room_id" class="block text-sm font-medium text-gray-700 mb-2">Select Appartment</label>
                    <select name="room_id" id="room_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Appartment</option>
                        @foreach($rooms ?? [] as $room)
                            <option value="{{ $room->id }}" {{ old('room_id', $booking->room_id) == $room->id ? 'selected' : '' }}>
                                {{ $room->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Select Date -->
                <div class="mb-8">
                    <label for="booking_dates" class="block text-sm font-medium text-gray-700 mb-2">Select Date</label>
                    <input type="text" id="booking_dates" name="booking_dates" 
                           value="{{ old('booking_dates', \Carbon\Carbon::parse($booking->start_at)->format('Y-m-d') . ' to ' . \Carbon\Carbon::parse($booking->end_at)->format('Y-m-d')) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" readonly>
                    <input type="hidden" name="start_at" id="start_at" value="{{ old('start_at', \Carbon\Carbon::parse($booking->start_at)->format('Y-m-d')) }}">
                    <input type="hidden" name="end_at" id="end_at" value="{{ old('end_at', \Carbon\Carbon::parse($booking->end_at)->format('Y-m-d')) }}">
                </div>

                <!-- RENTAL AGREEMENT Section -->
                <div class="mb-8 border-t pt-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">RENTAL AGREEMENT</h2>
                    
                    <!-- Landlord Section (Hidden/Prefilled) -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-md">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Landlord</h3>
                        <p class="text-sm text-gray-600 mb-2"><strong>This field is hidden when viewing the form</strong></p>
                        <div class="space-y-2 text-sm">
                            <p><strong>Surname, Name:</strong> <span class="text-gray-600">Martin Assies</span></p>
                            <p><strong>Address:</strong> <span class="text-gray-600">[Landlord Address]</span></p>
                            <p><strong>Postcode, city:</strong> <span class="text-gray-600">[Postcode, City]</span></p>
                            <p><strong>Telephone:</strong> <span class="text-gray-600">[Phone]</span></p>
                            <p><strong>E-Mail:</strong> <span class="text-gray-600">[Email]</span></p>
                        </div>
                    </div>

                    <!-- Renter Section -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Renter</h3>
                        <p class="text-sm text-gray-600 mb-4"><strong>This field is hidden when viewing the form</strong></p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="renter_name" class="block text-sm font-medium text-gray-700 mb-2">Surname, Name:(Required)</label>
                                <input type="text" id="renter_name" 
                                       value="{{ old('guest_first_name', $booking->guest_first_name) }} {{ old('guest_last_name', $booking->guest_last_name) }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                            </div>
                            <div>
                                <label for="renter_address" class="block text-sm font-medium text-gray-700 mb-2">Address:(Required)</label>
                                <input type="text" name="renter_address" id="renter_address" 
                                       value="{{ old('renter_address', $booking->renter_address) }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                @error('renter_address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="renter_postal_code" class="block text-sm font-medium text-gray-700 mb-2">Postcode, city(Required)</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="text" name="renter_postal_code" id="renter_postal_code" 
                                           value="{{ old('renter_postal_code', $booking->renter_postal_code) }}" 
                                           placeholder="Postcode"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                    <input type="text" name="renter_city" id="renter_city" 
                                           value="{{ old('renter_city', $booking->renter_city) }}" 
                                           placeholder="City"
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
                                <label for="renter_phone" class="block text-sm font-medium text-gray-700 mb-2">Telephone:(Required)</label>
                                <input type="tel" id="renter_phone" 
                                       value="{{ old('phone', $booking->phone) }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                            </div>
                            <div>
                                <label for="renter_email" class="block text-sm font-medium text-gray-700 mb-2">Email:(Required)</label>
                                <input type="email" id="renter_email" 
                                       value="{{ old('email', $booking->email) }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Rental property -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Rental property:</h3>
                        <p class="text-sm text-gray-600 mb-2"><strong>This field is hidden when viewing the form</strong></p>
                        <p class="text-sm text-gray-700 mb-2"><strong>{{ $booking->room->name ?? 'N/A' }}</strong></p>
                        <p class="text-sm text-gray-600 mb-4">Including shared use of: Kitchen, Bathroom, Furniture</p>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Address:(Required)</label>
                            <input type="text" value="{{ $booking->room->property->address ?? 'N/A' }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                        </div>
                        <p class="text-sm text-gray-600 mt-4">
                            For the duration of the rental period, the tenant is given:<br>
                            1 Pin-Code for the front door<br>
                            0 apartment key<br>
                            1 room key
                        </p>
                        <p class="text-sm text-gray-600 mt-2">
                            The subtenant is prohibited from making house keys. If one or more keys are lost, the landlord is entitled to replace the affected locks at the expense of the subtenant.
                        </p>
                    </div>

                    <!-- Rental Period -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Rental Period</h3>
                        <p class="text-sm text-gray-600 mb-2"><strong>This field is hidden when viewing the form</strong></p>
                        <p class="text-sm text-gray-700 mb-2">
                            <strong>Tenancy from</strong> {{ \Carbon\Carbon::parse($booking->start_at)->format('d.m.Y') }}
                        </p>
                        <p class="text-sm text-gray-700 mb-2">For 1 year</p>
                        <p class="text-sm text-gray-600">
                            The notice period is 1 month. Renter and landlord can terminate the rental agreement with one month's notice to the end of a calendar month. Termination must be made in writing (WhatsApp or email) and submitted to the other contracting party by the last day of the previous month at the latest..
                        </p>
                    </div>

                    <!-- Rental Fee -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Rental Fee</h3>
                        <p class="text-sm text-gray-600 mb-2"><strong>This field is hidden when viewing the form</strong></p>
                        <p class="text-sm text-gray-700 mb-2">
                            The rent is <strong>€{{ number_format($booking->room->base_price ?? 0, 2) }}</strong> per Night
                        </p>
                        <p class="text-sm text-gray-600">
                            The following additional costs are included in the rent: heating, hot water, water, waste water, electricity, gas, internet, cleaning of common areas, bed linen, towels.<br>
                            As a result of rising gas and energy prices, the total rent may increase. An increase in rent will be announced 1 month in advance.
                        </p>
                    </div>

                    <!-- Payment of the rent -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment of the rent</h3>
                        <p class="text-sm text-gray-600 mb-2"><strong>This field is hidden when viewing the form</strong></p>
                        <p class="text-sm text-gray-600">
                            The rent is to be transferred monthly in advance, not later than the 1st of the month, to the following account:<br>
                            Recipient: Martin Assies<br>
                            Bank: N26 Bank<br>
                            IBAN: DE24 1001 1001 2623 5950 48<br>
                            BIC: NTSBDEB1XXX
                        </p>
                    </div>

                    <!-- Deposit -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Deposit</h3>
                        <p class="text-sm text-gray-600 mb-2"><strong>This field is hidden when viewing the form</strong></p>
                        <p class="text-sm text-gray-700 mb-2">
                            The deposit is: <strong>780€</strong>
                        </p>
                        <p class="text-sm text-gray-600">
                            Recipient: Martin Assies<br>
                            IBAN: DE24 1001 1001 2623 5950 48<br>
                            BIC: NTSBDEB1XXX
                        </p>
                    </div>

                    <!-- Renter's Rights, Obligations and Liability -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Renter's Rights, Obligations and Liability</h3>
                        <ul class="text-sm text-gray-600 space-y-2 list-disc list-inside">
                            <li>For the duration of the rental stay, each tenant is obliged to keep the apartment clean and tidy. This applies in particular to the bathroom and kitchen.</li>
                            <li>The tenant undertakes to take care of the items provided (living space and furniture) and to leave them in the same condition in which they were received.</li>
                            <li>The house rules must be observed. Garbage cans are located in the yard, waste separation must be ensured.</li>
                            <li>Damage to the rental property must be reported to the landlord immediately. The subtenant is liable for damage resulting from a late notification.</li>
                            <li>There is an absolute smoking ban in the entire apartment and in the stairwell.</li>
                            <li>Accommodating other people is prohibited. Longer visits must be agreed with the landlord.</li>
                            <li>The landlord or a person authorized by him is permitted to enter the rented rooms 2 times a month in order to determine whether the rented rooms are in a contractual condition.</li>
                            <li>The following quiet times must be observed: No noise is permitted between midday and 2 p.m. and at night between 10 p.m. and 6 a.m.</li>
                            <li>Floors must be kept dry and treated properly so that no damage occurs.</li>
                            <li>Failure to comply with the rules may result in immediate termination. With his signature, the subtenant declares that he agrees with the content of this rental agreement.</li>
                        </ul>
                    </div>

                    <!-- Signatures -->
                    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-md font-semibold text-gray-900 mb-2">Landlord</h4>
                            <div class="border-t border-gray-300 pt-4">
                                <p class="text-sm text-gray-600">Martin Assies</p>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-md font-semibold text-gray-900 mb-2">Renter:</h4>
                            <div class="border-t border-gray-300 pt-4">
                                <p class="text-sm text-gray-600">{{ $booking->guest_first_name }} {{ $booking->guest_last_name }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Place, date:</label>
                            <input type="text" value="{{ \Carbon\Carbon::now()->format('d.m.Y') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Place, date:(Required)</label>
                            <input type="text" value="{{ \Carbon\Carbon::now()->format('d.m.Y') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                        </div>
                    </div>

                    <!-- Signature Pad -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Signature:(Required)</label>
                        <canvas id="signature-pad" class="border border-gray-300 rounded-md" width="600" height="200"></canvas>
                        <button type="button" id="clear-signature" class="mt-2 text-sm text-gray-600 hover:text-gray-800">Clear Signature</button>
                        <input type="hidden" name="signature" id="signature-data">
                    </div>
                </div>

                @if($booking->is_short_term)
                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                    <p class="text-sm text-yellow-800 font-semibold mb-2">{{ __('booking.short_term_booking_payment_required') }}</p>
                    <p class="text-sm text-yellow-700">{{ __('booking.total_amount') }}: <strong>€{{ number_format($booking->total_amount, 2) }}</strong></p>
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
                @endif
                @endif
                
                <div class="flex justify-end">
                    <button type="submit" id="submit-btn" class="bg-blue-600 text-white py-2 px-6 rounded-md hover:bg-blue-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed" @if($booking->is_short_term && !config('services.stripe.key')) disabled @endif>
                        @if($booking->is_short_term)
                            @if(config('services.stripe.key'))
                                {{ __('booking.pay_continue') }}
                            @else
                                {{ __('booking.payment_not_available') }}
                            @endif
                        @else
                            {{ __('booking.next') }}
                        @endif
                    </button>
                </div>
            </form>
        @elseif($step == 2)
            <!-- Step 2: Wohnungsgeberbescheinigung -->
            <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('booking.wohnungsgeberbescheinigung') }}</h2>
            <form action="{{ route('booking.signature', ['booking' => $booking->id]) }}" method="POST" id="step2-form">
                @csrf
                <input type="hidden" name="step" value="2">
                
                <div class="mb-6">
                    <p class="text-gray-600 mb-4">Please review and sign the Wohnungsgeberbescheinigung (Landlord Confirmation) document.</p>
                </div>
                
                <!-- Signature Pad -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.signature') }} *</label>
                    <canvas id="signature-pad" class="border border-gray-300 rounded-md" width="600" height="200"></canvas>
                    <button type="button" id="clear-signature" class="mt-2 text-sm text-gray-600 hover:text-gray-800">{{ __('booking.clear_signature') }}</button>
                    <input type="hidden" name="signature" id="signature-data">
                </div>
                
                <div class="flex justify-between">
                    <a href="{{ route('booking.step', ['booking' => $booking->id, 'step' => 1]) }}" class="bg-gray-200 text-gray-700 py-2 px-6 rounded-md hover:bg-gray-300 transition-colors">
                        {{ __('booking.previous') }}
                    </a>
                    <button type="submit" class="bg-blue-600 text-white py-2 px-6 rounded-md hover:bg-blue-700 transition-colors">
                        {{ __('booking.next') }}
                    </button>
                </div>
            </form>
        @elseif($step == 3)
            <!-- Step 3: Mietschuldsbefreiung -->
            <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('booking.mietschuldsbefreiung') }}</h2>
            <form action="{{ route('booking.signature', ['booking' => $booking->id]) }}" method="POST" id="step3-form">
                @csrf
                <input type="hidden" name="step" value="3">
                
                <div class="mb-6">
                    <p class="text-gray-600 mb-4">Please review and sign the Mietschuldsbefreiung (Certificate of Rent Arrears) document.</p>
                </div>
                
                <!-- Signature Pad -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.signature') }} *</label>
                    <canvas id="signature-pad" class="border border-gray-300 rounded-md" width="600" height="200"></canvas>
                    <button type="button" id="clear-signature" class="mt-2 text-sm text-gray-600 hover:text-gray-800">{{ __('booking.clear_signature') }}</button>
                    <input type="hidden" name="signature" id="signature-data">
                </div>
                
                <div class="flex justify-between">
                    <a href="{{ route('booking.step', ['booking' => $booking->id, 'step' => 2]) }}" class="bg-gray-200 text-gray-700 py-2 px-6 rounded-md hover:bg-gray-300 transition-colors">
                        {{ __('booking.previous') }}
                    </a>
                    <button type="submit" class="bg-green-600 text-white py-2 px-6 rounded-md hover:bg-green-700 transition-colors">
                        Complete Booking
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>

@push('scripts')
@if($step == 1)
<!-- Flatpickr for date selection -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
@endif

@if($step == 1 && $booking->is_short_term && config('services.stripe.key'))
<script src="https://js.stripe.com/v3/"></script>
<script>
    // Initialize Stripe
    const stripeKey = '{{ config("services.stripe.key") }}';
    if (!stripeKey) {
        console.error('Stripe publishable key is not configured');
        document.getElementById('payment-message').textContent = 'Payment processing is not configured. Please contact support.';
        document.getElementById('payment-message').classList.remove('hidden');
    } else {
        const stripe = Stripe(stripeKey);
        let elements;
        let paymentElement;
        
        // Initialize payment element
        (async function() {
            try {
                const response = await fetch('{{ route("booking.payment", $booking) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ action: 'setup' })
                });
                
                const data = await response.json();
                
                if (data.client_secret) {
                    elements = stripe.elements({ 
                        clientSecret: data.client_secret,
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
                console.error('Error setting up payment:', error);
                document.getElementById('payment-message').textContent = 'Error setting up payment. Please refresh the page.';
                document.getElementById('payment-message').classList.remove('hidden');
            }
        })();
    }
</script>
@endif

<script>
    @if($step == 1)
    // Initialize Flatpickr for date selection
    const bookingDates = document.getElementById('booking_dates');
    if (bookingDates) {
        const fp = flatpickr(bookingDates, {
            mode: "range",
            dateFormat: "Y-m-d",
            minDate: "today",
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length === 2) {
                    document.getElementById('start_at').value = selectedDates[0].toISOString().split('T')[0];
                    document.getElementById('end_at').value = selectedDates[1].toISOString().split('T')[0];
                }
            }
        });
    }
    @endif

    const canvas = document.getElementById('signature-pad');
    if (canvas) {
        const signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)'
        });
        
        // Adjust canvas size
        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            signaturePad.clear();
        }
        
        window.addEventListener("resize", resizeCanvas);
        resizeCanvas();
    
        // Clear signature
        const clearBtn = document.getElementById('clear-signature');
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                signaturePad.clear();
            });
        }
        
        // Handle form submission
        const form = document.getElementById('step{{ $step }}-form');
        if (form) {
            form.addEventListener('submit', async function(e) {
                if (signaturePad.isEmpty()) {
                    e.preventDefault();
                    alert('{{ __('booking.please_provide_signature') }}');
                    return false;
                }
                
                const signatureData = signaturePad.toDataURL();
                const signatureInput = document.getElementById('signature-data');
                if (signatureInput) {
                    signatureInput.value = signatureData;
                }
        
                // Handle payment for short-term bookings
                @if($step == 1 && $booking->is_short_term && config('services.stripe.key'))
                e.preventDefault();
                const submitBtn = document.getElementById('submit-btn');
                submitBtn.disabled = true;
                submitBtn.textContent = 'Processing...';
                
                try {
                    const { error, paymentMethod } = await stripe.createPaymentMethod({
                        elements: elements,
                    });
                    
                    if (error) {
                        document.getElementById('payment-message').textContent = error.message;
                        document.getElementById('payment-message').classList.remove('hidden');
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Pay & Continue';
                        return;
                    }
                    
                    document.getElementById('payment_method_id').value = paymentMethod.id;
                    
                    // Now submit the form
                    form.submit();
                } catch (err) {
                    document.getElementById('payment-message').textContent = 'An error occurred. Please try again.';
                    document.getElementById('payment-message').classList.remove('hidden');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Pay & Continue';
                }
                @endif
            });
        }
    }
</script>
@endpush
@endsection

