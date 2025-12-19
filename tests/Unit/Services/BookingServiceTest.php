<?php

namespace Tests\Unit\Services;

use App\Models\Booking;
use App\Models\Room;
use App\Models\BlackoutDate;
use App\Models\Property;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BookingService $bookingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bookingService = new BookingService();
    }

    /** @test */
    public function it_checks_if_room_is_available_when_no_bookings_exist()
    {
        $property = Property::factory()->create();
        $room = Room::factory()->create(['property_id' => $property->id]);
        
        $startAt = Carbon::now()->addDays(1);
        $endAt = Carbon::now()->addDays(5);

        $result = $this->bookingService->isAvailable($room, $startAt, $endAt);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_returns_false_when_room_has_conflicting_confirmed_booking()
    {
        $property = Property::factory()->create();
        $room = Room::factory()->create(['property_id' => $property->id]);
        
        // Create a confirmed booking
        Booking::factory()->create([
            'room_id' => $room->id,
            'start_at' => Carbon::now()->addDays(2),
            'end_at' => Carbon::now()->addDays(4),
            'status' => 'confirmed',
        ]);

        // Try to book overlapping dates
        $startAt = Carbon::now()->addDays(1);
        $endAt = Carbon::now()->addDays(5);

        $result = $this->bookingService->isAvailable($room, $startAt, $endAt);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_returns_true_when_room_has_pending_booking()
    {
        $property = Property::factory()->create();
        $room = Room::factory()->create(['property_id' => $property->id]);
        
        // Create a pending booking (should not block availability)
        Booking::factory()->create([
            'room_id' => $room->id,
            'start_at' => Carbon::now()->addDays(2),
            'end_at' => Carbon::now()->addDays(4),
            'status' => 'pending',
        ]);

        $startAt = Carbon::now()->addDays(1);
        $endAt = Carbon::now()->addDays(5);

        $result = $this->bookingService->isAvailable($room, $startAt, $endAt);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_returns_false_when_room_has_blackout_date()
    {
        $property = Property::factory()->create();
        $room = Room::factory()->create(['property_id' => $property->id]);
        
        // Create a blackout date
        BlackoutDate::factory()->create([
            'room_id' => $room->id,
            'start_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(4)->format('Y-m-d'),
        ]);

        $startAt = Carbon::now()->addDays(1);
        $endAt = Carbon::now()->addDays(5);

        $result = $this->bookingService->isAvailable($room, $startAt, $endAt);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_excludes_specified_booking_id_when_checking_availability()
    {
        $property = Property::factory()->create();
        $room = Room::factory()->create(['property_id' => $property->id]);
        
        $existingBooking = Booking::factory()->create([
            'room_id' => $room->id,
            'start_at' => Carbon::now()->addDays(2),
            'end_at' => Carbon::now()->addDays(4),
            'status' => 'confirmed',
            'payment_status' => 'pending',
        ]);

        // Check availability excluding this booking (for updates)
        $startAt = Carbon::now()->addDays(1);
        $endAt = Carbon::now()->addDays(5);

        $result = $this->bookingService->isAvailable($room, $startAt, $endAt, $existingBooking->id);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_calculates_total_for_short_term_booking()
    {
        $property = Property::factory()->create();
        $room = Room::factory()->create([
            'property_id' => $property->id,
            'base_price' => 50.00,
        ]);
        
        $startAt = Carbon::now()->addDays(1);
        $endAt = Carbon::now()->addDays(6); // 5 nights

        $total = $this->bookingService->calculateTotal($room, $startAt, $endAt);

        $this->assertEquals(250.00, $total); // 5 nights * 50
    }

    /** @test */
    public function it_calculates_total_for_long_term_booking()
    {
        $property = Property::factory()->create();
        $room = Room::factory()->create([
            'property_id' => $property->id,
            'monthly_price' => 700.00,
        ]);
        
        $startAt = Carbon::now()->addDays(1);
        $endAt = Carbon::now()->addDays(45); // More than 30 days

        $total = $this->bookingService->calculateTotal($room, $startAt, $endAt);

        $this->assertEquals(1400.00, $total); // 2 months * 700
    }

    /** @test */
    public function it_calculates_total_for_long_term_without_end_date()
    {
        $property = Property::factory()->create();
        $room = Room::factory()->create([
            'property_id' => $property->id,
            'monthly_price' => 700.00,
        ]);
        
        $startAt = Carbon::now()->addDays(1);

        $total = $this->bookingService->calculateTotal($room, $startAt, null);

        $this->assertEquals(700.00, $total); // Monthly price
    }

    /** @test */
    public function it_uses_default_monthly_price_when_not_set()
    {
        $property = Property::factory()->create();
        $room = Room::factory()->create([
            'property_id' => $property->id,
            'monthly_price' => null,
        ]);
        
        $startAt = Carbon::now()->addDays(1);

        $total = $this->bookingService->calculateTotal($room, $startAt, null);

        $this->assertEquals(700.00, $total); // Default monthly price
    }

    /** @test */
    public function it_gets_conflicting_bookings()
    {
        $property = Property::factory()->create();
        $room = Room::factory()->create(['property_id' => $property->id]);
        
        $booking1 = Booking::factory()->create([
            'room_id' => $room->id,
            'start_at' => Carbon::now()->addDays(2),
            'end_at' => Carbon::now()->addDays(4),
            'status' => 'confirmed',
            'payment_status' => 'pending',
        ]);

        $booking2 = Booking::factory()->create([
            'room_id' => $room->id,
            'start_at' => Carbon::now()->addDays(5),
            'end_at' => Carbon::now()->addDays(7),
            'status' => 'confirmed',
            'payment_status' => 'pending',
        ]);

        $startAt = Carbon::now()->addDays(1);
        $endAt = Carbon::now()->addDays(6);

        $conflicts = $this->bookingService->getConflicts($room, $startAt, $endAt);

        $this->assertCount(2, $conflicts);
        $this->assertTrue(collect($conflicts)->pluck('id')->contains($booking1->id));
        $this->assertTrue(collect($conflicts)->pluck('id')->contains($booking2->id));
    }
}

