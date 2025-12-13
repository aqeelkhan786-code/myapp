@extends('layouts.app')

@section('title', 'Booking Form - Step ' . $step)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Progress Steps - Only Step 1 visible to users -->
    <div class="mb-8">
        <div class="flex items-center justify-center">
            <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-600 text-white">
                    1
                </div>
                <span class="ml-2 text-sm font-medium text-blue-600">rental agreement</span>
            </div>
        </div>
        <p class="text-center text-sm text-gray-500 mt-2">Complete your booking request</p>
    </div>
    
    <!-- Room Preview -->
    <div class="mb-8 bg-white rounded-lg shadow-md overflow-hidden p-6">
        <div class="flex items-center gap-4">
            @if($room->images && $room->images->count() > 0)
                <img src="{{ asset('storage/' . $room->images->first()->path) }}" 
                     alt="{{ $room->name }}" 
                     class="w-24 h-24 object-cover rounded-lg">
            @else
                <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=400&h=300&fit=crop" 
                     alt="{{ $room->name }}" 
                     class="w-24 h-24 object-cover rounded-lg">
            @endif
            <div>
                <h3 class="text-lg font-semibold">{{ $room->name }}</h3>
                <p class="text-gray-600">€{{ number_format($room->base_price, 2) }} per night</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-8">
        @if($step == 1)
            <!-- Step 1: Rental Agreement Form -->
            <h2 class="text-2xl font-bold text-gray-900 mb-6">rental agreement</h2>
            
            <form action="{{ route('booking.form-step', ['room' => $room->id, 'step' => 1]) }}" method="POST" id="step1-form">
                @csrf
                
                <!-- Personal Information Section -->
                <div class="mb-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="guest_first_name" class="block text-sm font-medium text-gray-700 mb-2">First name(Required)</label>
                            <input type="text" name="guest_first_name" id="guest_first_name" 
                                   value="{{ old('guest_first_name', $formData['step1']['guest_first_name'] ?? '') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            @error('guest_first_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="guest_last_name" class="block text-sm font-medium text-gray-700 mb-2">Last name(Required)</label>
                            <input type="text" name="guest_last_name" id="guest_last_name" 
                                   value="{{ old('guest_last_name', $formData['step1']['guest_last_name'] ?? '') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            @error('guest_last_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="job" class="block text-sm font-medium text-gray-700 mb-2">Job(Required)</label>
                            <input type="text" name="job" id="job" 
                                   value="{{ old('job', $formData['step1']['job'] ?? '') }}" 
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
                                <option value="Deutsch" {{ old('language', $formData['step1']['language'] ?? '') == 'Deutsch' ? 'selected' : '' }}>Deutsch</option>
                                <option value="Englisch" {{ old('language', $formData['step1']['language'] ?? '') == 'Englisch' ? 'selected' : '' }}>Englisch</option>
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
                                <option value="Mail" {{ old('communication_preference', $formData['step1']['communication_preference'] ?? '') == 'Mail' ? 'selected' : '' }}>Mail</option>
                                <option value="Whatsapp" {{ old('communication_preference', $formData['step1']['communication_preference'] ?? '') == 'Whatsapp' ? 'selected' : '' }}>Whatsapp</option>
                            </select>
                            @error('communication_preference')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Handynummer(Required)</label>
                            <input type="tel" name="phone" id="phone" 
                                   value="{{ old('phone', $formData['step1']['phone'] ?? '') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-Mail-Adresse(Required)</label>
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
                    <label for="room_id" class="block text-sm font-medium text-gray-700 mb-2">Select Appartment</label>
                    <select name="room_id" id="room_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed" 
                            disabled>
                        <option value="">Select Appartment</option>
                        @foreach($allRooms ?? [] as $apartment)
                            <option value="{{ $apartment->id }}" {{ old('room_id', $room->id) == $apartment->id ? 'selected' : '' }}>
                                {{ $apartment->name }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="room_id" value="{{ $room->id }}">
                    <p class="mt-1 text-xs text-gray-500">This field cannot be changed as the apartment has already been selected.</p>
                </div>

                <!-- Select Date -->
                <div class="mb-8">
                    <label for="booking_dates" class="block text-sm font-medium text-gray-700 mb-2">Select Date</label>
                    @php
                        $dateDisplay = '';
                        if (isset($formData['step2']['start_at'])) {
                            $startDate = \Carbon\Carbon::parse($formData['step2']['start_at'])->format('Y-m-d');
                            if (isset($formData['step2']['end_at']) && $formData['step2']['end_at']) {
                                $endDate = \Carbon\Carbon::parse($formData['step2']['end_at'])->format('Y-m-d');
                                $dateDisplay = $startDate . ' to ' . $endDate;
                            } else {
                                $dateDisplay = $startDate . ' (Long-term rental)';
                            }
                        }
                    @endphp
                    <input type="text" id="booking_dates" name="booking_dates" 
                           value="{{ old('booking_dates', $dateDisplay) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed" 
                           readonly disabled>
                    <input type="hidden" name="start_at" id="start_at" value="{{ old('start_at', $formData['step2']['start_at'] ?? '') }}">
                    <input type="hidden" name="end_at" id="end_at" value="{{ old('end_at', $formData['step2']['end_at'] ?? '') }}">
                    <p class="mt-1 text-xs text-gray-500">This field cannot be changed as the date has already been selected.</p>
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
                                <input type="text" name="renter_name" id="renter_name" 
                                       value="{{ old('renter_name', $formData['step2']['renter_name'] ?? '') }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                @error('renter_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="renter_address" class="block text-sm font-medium text-gray-700 mb-2">Address:(Required)</label>
                                <input type="text" name="renter_address" id="renter_address" 
                                       value="{{ old('renter_address', $formData['step2']['renter_address'] ?? '') }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                @error('renter_address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="renter_postal_code" class="block text-sm font-medium text-gray-700 mb-2">Postcode, city(Required)</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="text" name="renter_postal_code" id="renter_postal_code" 
                                           value="{{ old('renter_postal_code', $formData['step2']['renter_postal_code'] ?? '') }}" 
                                           placeholder="Postcode"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                    <input type="text" name="renter_city" id="renter_city" 
                                           value="{{ old('renter_city', $formData['step2']['renter_city'] ?? '') }}" 
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
                                <input type="tel" name="renter_phone" id="renter_phone" 
                                       value="{{ old('renter_phone', $formData['step2']['renter_phone'] ?? '') }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                @error('renter_phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="renter_email" class="block text-sm font-medium text-gray-700 mb-2">Email:(Required)</label>
                                <input type="email" name="renter_email" id="renter_email" 
                                       value="{{ old('renter_email', $formData['step2']['renter_email'] ?? '') }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                @error('renter_email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Rental property -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Rental property:</h3>
                        <p class="text-sm text-gray-600 mb-2"><strong>This field is hidden when viewing the form</strong></p>
                        <p class="text-sm text-gray-700 mb-2"><strong id="selected-room-name">{{ $room->name ?? 'N/A' }}</strong></p>
                        <p class="text-sm text-gray-600 mb-4">Including shared use of: Kitchen, Bathroom, Furniture</p>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Address:(Required)</label>
                            <input type="text" id="room-address" value="{{ $room->property->address ?? 'N/A' }}" 
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
                            <strong>Tenancy from</strong> <span id="tenancy-from">{{ isset($formData['step2']['start_at']) ? \Carbon\Carbon::parse($formData['step2']['start_at'])->format('d.m.Y') : '[Select Date]' }}</span>
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
                            The rent is <strong id="rent-per-night">€{{ number_format($room->base_price ?? 0, 2) }}</strong> per Night
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
                                <p class="text-sm text-gray-600" id="renter-full-name">{{ old('guest_first_name', $formData['step1']['guest_first_name'] ?? '') }} {{ old('guest_last_name', $formData['step1']['guest_last_name'] ?? '') }}</p>
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
                
                <div class="flex justify-end">
                    <button type="submit" id="submit-btn" class="bg-green-600 text-white py-2 px-6 rounded-md hover:bg-green-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed">
                        Complete Booking
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
                    <label for="dates" class="block text-sm font-medium text-gray-700 mb-2">Select Dates *</label>
                    <input type="text" id="dates" name="dates" 
                           value="{{ old('dates', isset($formData['step2']['start_at']) ? \Carbon\Carbon::parse($formData['step2']['start_at'])->format('Y-m-d') . ' to ' . \Carbon\Carbon::parse($formData['step2']['end_at'])->format('Y-m-d') : '') }}" 
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
                        <label for="renter_city" class="block text-sm font-medium text-gray-700 mb-2">City *</label>
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
                            $end = \Carbon\Carbon::parse($formData['step2']['end_at']);
                            $nights = $start->diffInDays($end);
                            $total = $nights * $room->base_price;
                        @endphp
                        <div class="flex justify-between mb-2">
                            <span>Nights:</span>
                            <span>{{ $nights }}</span>
                        </div>
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
                        <span class="text-gray-600">Room:</span>
                        <span class="font-semibold">{{ $room->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Check-in:</span>
                        <span class="font-semibold">{{ \Carbon\Carbon::parse($formData['step2']['start_at'])->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Check-out:</span>
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
            form.addEventListener('submit', function(e) {
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
            });
        }
    }
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
@endpush
@endsection

