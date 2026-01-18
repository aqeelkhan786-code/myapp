<?php

namespace App\Services;

use App\Models\Booking;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }
    
    /**
     * Create a payment intent for a booking
     */
    public function createPaymentIntent(Booking $booking): PaymentIntent
    {
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => (int)($booking->total_amount * 100), // Convert to cents
                'currency' => 'eur',
                'metadata' => [
                    'booking_id' => $booking->id,
                ],
            ]);
            
            $booking->update([
                'stripe_payment_intent_id' => $paymentIntent->id,
            ]);
            
            return $paymentIntent;
        } catch (ApiErrorException $e) {
            Log::error('Stripe payment intent creation failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
    
    /**
     * Confirm a payment intent
     */
    public function confirmPayment(string $paymentIntentId, string $paymentMethodId): PaymentIntent
    {
        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
            $paymentIntent->confirm([
                'payment_method' => $paymentMethodId,
            ]);
            
            return $paymentIntent;
        } catch (ApiErrorException $e) {
            Log::error('Stripe payment confirmation failed', [
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
    
    /**
     * Handle successful payment
     */
    public function handleSuccessfulPayment(Booking $booking, PaymentIntent $paymentIntent): void
    {
        $booking->update([
            'status' => 'confirmed', // Confirm booking after successful payment
            'payment_status' => 'paid',
            'paid_amount' => $paymentIntent->amount / 100, // Convert from cents
        ]);
    }
}

