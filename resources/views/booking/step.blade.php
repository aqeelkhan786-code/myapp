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
            <!-- Step 1: Rental Agreement Form -->
            <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('booking.rental_agreement') }}</h2>
            <form action="{{ route('booking.save-step', ['booking' => $booking->id, 'step' => 1]) }}" method="POST" id="step1-form">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="guest_first_name" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.first_name') }} *</label>
                        <input type="text" name="guest_first_name" id="guest_first_name" value="{{ old('guest_first_name', $booking->guest_first_name) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        @error('guest_first_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="guest_last_name" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.last_name') }} *</label>
                        <input type="text" name="guest_last_name" id="guest_last_name" value="{{ old('guest_last_name', $booking->guest_last_name) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        @error('guest_last_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.email') }} *</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $booking->email) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.phone') }}</label>
                        <input type="tel" name="phone" id="phone" value="{{ old('phone', $booking->phone) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.address') }}</label>
                        <input type="text" name="address" id="address" value="{{ old('address') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.city') }}</label>
                        <input type="text" name="city" id="city" value="{{ old('city') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.postal_code') }}</label>
                        <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.notes') }}</label>
                    <textarea name="notes" id="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes', $booking->notes) }}</textarea>
                </div>
                
                <!-- Signature Pad -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.signature') }} *</label>
                    <canvas id="signature-pad" class="border border-gray-300 rounded-md" width="600" height="200"></canvas>
                    <button type="button" id="clear-signature" class="mt-2 text-sm text-gray-600 hover:text-gray-800">{{ __('booking.clear_signature') }}</button>
                    <input type="hidden" name="signature" id="signature-data">
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
                @else
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-md">
                    <p class="text-sm text-red-800 font-semibold">{{ __('booking.payment_not_configured') }}</p>
                    <p class="text-sm text-red-700 mt-1">{{ __('booking.contact_administrator') }}</p>
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
    const canvas = document.getElementById('signature-pad');
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
    document.getElementById('clear-signature').addEventListener('click', function() {
        signaturePad.clear();
    });
    
    // Handle form submission
    const form = document.getElementById('step{{ $step }}-form');
    form.addEventListener('submit', async function(e) {
        if (signaturePad.isEmpty()) {
            e.preventDefault();
            alert('{{ __('booking.please_provide_signature') }}');
            return false;
        }
        
        const signatureData = signaturePad.toDataURL();
        document.getElementById('signature-data').value = signatureData;
        
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
</script>
@endpush
@endsection

