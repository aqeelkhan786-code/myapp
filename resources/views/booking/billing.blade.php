@extends('layouts.app')

@section('title', __('booking.payment_information') ?? 'Payment Information')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    @if(session('info'))
        <div class="mb-6 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded-lg">
            <p class="font-semibold">{{ session('info') }}</p>
        </div>
    @endif
    
    @if($errors->any())
        <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Header -->
        <div class="bg-blue-600 px-8 py-6">
            <h1 class="text-3xl font-bold text-white">{{ __('booking.payment_information') }}</h1>
            <p class="text-blue-100 mt-2">{{ __('booking.payment_required_warning') }}</p>
        </div>
        
        <div class="p-8">
            <!-- Booking Summary -->
            <div class="mb-8 bg-gray-50 rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('booking.booking_summary') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">{{ __('booking.room_label') }}</p>
                        <p class="font-semibold text-gray-900">{{ $booking->room->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">{{ __('booking.guest_label') }}</p>
                        <p class="font-semibold text-gray-900">{{ $booking->guest_first_name }} {{ $booking->guest_last_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">{{ __('booking.check_in_label') }}</p>
                        <p class="font-semibold text-gray-900">
                            {{ \Carbon\Carbon::parse($booking->start_at)->format('M d, Y') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">{{ __('booking.check_out_label') }}</p>
                        <p class="font-semibold text-gray-900">
                            {{ $booking->end_at ? \Carbon\Carbon::parse($booking->end_at)->format('M d, Y') : 'N/A' }}
                        </p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-600">{{ __('booking.total_amount_label') }}</p>
                        <p class="text-2xl font-bold text-blue-600">€{{ number_format($booking->total_amount, 2) }}</p>
                    </div>
                </div>
            </div>

            @if(config('services.stripe.key') && isset($clientSecret) && $clientSecret)
            <!-- Payment Form -->
            <form id="payment-form" method="POST" action="{{ route('booking.billing.pay', $booking) }}">
                @csrf
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('booking.payment_information') }} *</label>
                    <div id="payment-element" class="p-4 border border-gray-300 rounded-md bg-white" style="min-height: 200px;">
                        <!-- Stripe Elements will create form elements here -->
                    </div>
                    <div id="payment-message" class="mt-2 text-sm text-red-600 hidden"></div>
                    <input type="hidden" name="payment_method_id" id="payment_method_id">
                </div>

                <div class="flex justify-end items-center">
                    <button type="submit" id="submit-btn" class="bg-blue-600 text-white py-3 px-8 rounded-md hover:bg-blue-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed font-semibold">
                        {{ __('booking.pay_button', ['amount' => '€' . number_format($booking->total_amount, 2)]) }}
                    </button>
                </div>
            </form>
            @else
            <div class="p-4 bg-red-50 border border-red-200 rounded-md">
                @if(!config('services.stripe.key'))
                <p class="text-sm text-red-800 font-semibold mb-2">{{ __('booking.payment_processing_not_configured') }}</p>
                <p class="text-sm text-red-700">{{ __('booking.contact_support_complete_booking') }}</p>
                @else
                <p class="text-sm text-red-800 font-semibold mb-2">{{ __('booking.payment_initialization_failed') }}</p>
                <p class="text-sm text-red-700">{{ __('booking.refresh_page_or_contact_support') }}</p>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

@if(config('services.stripe.key') && isset($clientSecret) && $clientSecret)
<script src="https://js.stripe.com/v3/"></script>
<script>
    (function() {
        // Translations for JavaScript
        const translations = {
            stripeNotConfigured: @json(__('booking.stripe_not_configured_contact_support')),
            failedToInitializePayment: @json(__('booking.failed_to_initialize_payment')),
            failedToInitializePaymentForm: @json(__('booking.failed_to_initialize_payment_form')),
            paymentFormCouldNotLoad: @json(__('booking.payment_form_could_not_load')),
            processing: @json(__('booking.processing')),
            payButton: @json(__('booking.pay_button', ['amount' => '€' . number_format($booking->total_amount, 2)])),
            failedToVerifyPayment: @json(__('booking.failed_to_verify_payment')),
            paymentNotSuccessfulStatus: @json(__('booking.payment_not_successful_status')),
            anErrorOccurred: @json(__('booking.an_error_occurred')),
        };
        
        const stripeKey = '{{ config("services.stripe.key") }}';
        const clientSecret = '{{ $clientSecret ?? "" }}';
        
        if (!stripeKey) {
            console.error('Stripe publishable key missing');
            const paymentMessage = document.getElementById('payment-message');
            if (paymentMessage) {
                paymentMessage.textContent = translations.stripeNotConfigured;
                paymentMessage.classList.remove('hidden');
            }
            return;
        }
        
        if (!clientSecret || clientSecret.trim() === '') {
            console.error('Client secret missing');
            const paymentMessage = document.getElementById('payment-message');
            if (paymentMessage) {
                paymentMessage.textContent = translations.failedToInitializePayment;
                paymentMessage.classList.remove('hidden');
            }
            // Disable submit button
            const submitBtn = document.getElementById('submit-btn');
            if (submitBtn) {
                submitBtn.disabled = true;
            }
            return;
        }
        
        // Wait for Stripe.js to load
        function waitForStripe(callback) {
            if (typeof Stripe !== 'undefined') {
                callback();
            } else {
                setTimeout(function() {
                    waitForStripe(callback);
                }, 100);
            }
        }
        
        waitForStripe(function() {
            let stripe;
            let elements;
            let paymentElement;
            
            try {
                stripe = Stripe(stripeKey);
                
                // Initialize Stripe Elements
                elements = stripe.elements({
                    clientSecret: clientSecret,
                    appearance: {
                        theme: 'stripe',
                    }
                });
                
                paymentElement = elements.create('payment');
                paymentElement.mount('#payment-element');
                
                console.log('Payment element mounted successfully');
            } catch (error) {
                console.error('Failed to initialize Stripe Elements:', error);
                const paymentMessage = document.getElementById('payment-message');
                if (paymentMessage) {
                    paymentMessage.textContent = translations.failedToInitializePaymentForm.replace(':error', error.message);
                    paymentMessage.classList.remove('hidden');
                }
                const paymentElementDiv = document.getElementById('payment-element');
                if (paymentElementDiv) {
                    paymentElementDiv.innerHTML = '<p class="text-red-500 text-sm">' + translations.paymentFormCouldNotLoad + '</p>';
                }
                return;
            }
            
            // Handle form submission
            const form = document.getElementById('payment-form');
            const submitBtn = document.getElementById('submit-btn');
            const paymentMessage = document.getElementById('payment-message');
            
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                submitBtn.disabled = true;
                submitBtn.textContent = translations.processing;
                
                if (paymentMessage) {
                    paymentMessage.classList.add('hidden');
                    paymentMessage.textContent = '';
                }
                
                try {
                    // Confirm payment
                    const {error: submitError, paymentIntent} = await stripe.confirmPayment({
                        elements,
                        confirmParams: {
                            return_url: window.location.href,
                        },
                        redirect: 'if_required'
                    });
                    
                    if (submitError) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = translations.payButton;
                        if (paymentMessage) {
                            paymentMessage.textContent = submitError.message;
                            paymentMessage.classList.remove('hidden');
                        }
                        return;
                    }
                    
                    // Check payment status
                    let finalPaymentIntent = paymentIntent;
                    
                    if (!paymentIntent || paymentIntent.status !== 'succeeded') {
                        const {error: retrieveError, paymentIntent: retrievedIntent} = await stripe.retrievePaymentIntent(clientSecret);
                        if (retrieveError) {
                            submitBtn.disabled = false;
                            submitBtn.textContent = translations.payButton;
                            if (paymentMessage) {
                                paymentMessage.textContent = translations.failedToVerifyPayment;
                                paymentMessage.classList.remove('hidden');
                            }
                            return;
                        }
                        finalPaymentIntent = retrievedIntent;
                    }
                    
                    if (finalPaymentIntent && finalPaymentIntent.status === 'succeeded') {
                        // Store payment method ID
                        const paymentMethodIdInput = document.getElementById('payment_method_id');
                        if (paymentMethodIdInput) {
                            paymentMethodIdInput.value = finalPaymentIntent.id;
                        }
                        
                        // Submit the form
                        form.submit();
                    } else {
                        submitBtn.disabled = false;
                        submitBtn.textContent = translations.payButton;
                        if (paymentMessage) {
                            const status = finalPaymentIntent ? finalPaymentIntent.status : 'unknown';
                            paymentMessage.textContent = translations.paymentNotSuccessfulStatus.replace(':status', status);
                            paymentMessage.classList.remove('hidden');
                        }
                    }
                } catch (error) {
                    console.error('Payment error:', error);
                    submitBtn.disabled = false;
                    submitBtn.textContent = translations.payButton;
                    if (paymentMessage) {
                        paymentMessage.textContent = translations.anErrorOccurred.replace(':error', error.message);
                        paymentMessage.classList.remove('hidden');
                    }
                }
            });
        });
    })();
</script>
@endif
@endsection
