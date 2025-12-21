<?php

namespace Tests\Unit\Services;

use App\Models\Booking;
use App\Models\Room;
use App\Models\Property;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Mockery;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set a dummy Stripe secret for testing
        Config::set('services.stripe.secret', 'sk_test_dummy_key');
    }

    /** @test */
    public function it_updates_booking_with_payment_status_on_successful_payment()
    {
        $property = Property::factory()->create();
        $room = Room::factory()->create(['property_id' => $property->id]);
        $booking = Booking::factory()->create([
            'room_id' => $room->id,
            'total_amount' => 100.00,
            'payment_status' => 'pending',
        ]);

        $paymentIntent = Mockery::mock('Stripe\PaymentIntent');
        $paymentIntent->id = 'pi_test_123';
        $paymentIntent->amount = 10000; // 100.00 in cents
        $paymentIntent->status = 'succeeded';

        $service = new PaymentService();
        $service->handleSuccessfulPayment($booking, $paymentIntent);

        $booking->refresh();
        $this->assertEquals('paid', $booking->payment_status);
        $this->assertEquals(100.00, $booking->paid_amount);
    }

    /** @test */
    public function it_converts_payment_amount_from_cents_to_dollars()
    {
        $property = Property::factory()->create();
        $room = Room::factory()->create(['property_id' => $property->id]);
        $booking = Booking::factory()->create([
            'room_id' => $room->id,
            'total_amount' => 250.50,
        ]);

        $paymentIntent = Mockery::mock('Stripe\PaymentIntent');
        $paymentIntent->id = 'pi_test_123';
        $paymentIntent->amount = 25050; // 250.50 in cents
        $paymentIntent->status = 'succeeded';

        $service = new PaymentService();
        $service->handleSuccessfulPayment($booking, $paymentIntent);

        $booking->refresh();
        $this->assertEquals(250.50, $booking->paid_amount);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}


