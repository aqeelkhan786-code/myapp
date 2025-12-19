<?php

namespace Tests\Feature\Booking;

use App\Models\Booking;
use App\Models\Room;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function users_can_view_booking_index_page()
    {
        $property = Property::factory()->create();
        $room = Room::factory()->create(['property_id' => $property->id]);
        
        $response = $this->get('/booking');
        
        $response->assertStatus(200);
        $response->assertViewIs('booking.index');
    }

    /** @test */
    public function users_can_view_room_details()
    {
        $property = Property::factory()->create();
        $room = Room::factory()->create(['property_id' => $property->id]);
        
        $response = $this->get("/booking/{$room->id}");
        
        $response->assertStatus(200);
        $response->assertViewIs('booking.show');
    }

    /** @test */
    public function users_can_create_booking_with_valid_dates()
    {
        $property = Property::factory()->create();
        $room = Room::factory()->create([
            'property_id' => $property->id,
            'short_term_allowed' => false,
        ]);
        
        $startAt = Carbon::now()->addDays(1);
        $endAt = Carbon::now()->addDays(5);
        
        $response = $this->post("/booking/{$room->id}", [
            'start_at' => $startAt->format('Y-m-d'),
            'end_at' => $endAt->format('Y-m-d'),
        ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'room_id' => $room->id,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function users_cannot_create_booking_with_past_dates()
    {
        $property = Property::factory()->create();
        $room = Room::factory()->create(['property_id' => $property->id]);
        
        // Use a date clearly in the past (2 days ago)
        $startAt = Carbon::now()->subDays(2);
        $endAt = Carbon::now()->addDays(5);
        
        $response = $this->post("/booking/{$room->id}", [
            'start_at' => $startAt->format('Y-m-d'),
            'end_at' => $endAt->format('Y-m-d'),
        ]);
        
        $response->assertSessionHasErrors(['start_at']);
    }

    /** @test */
    public function users_cannot_create_booking_with_end_before_start()
    {
        $property = Property::factory()->create();
        $room = Room::factory()->create(['property_id' => $property->id]);
        
        $startAt = Carbon::now()->addDays(5);
        $endAt = Carbon::now()->addDays(1);
        
        $response = $this->post("/booking/{$room->id}", [
            'start_at' => $startAt->format('Y-m-d'),
            'end_at' => $endAt->format('Y-m-d'),
        ]);
        
        $response->assertSessionHasErrors(['end_at']);
    }

    /** @test */
    public function users_cannot_book_unavailable_room()
    {
        $property = Property::factory()->create();
        $room = Room::factory()->create(['property_id' => $property->id]);
        
        // Create a confirmed booking
        Booking::factory()->confirmed()->create([
            'room_id' => $room->id,
            'start_at' => Carbon::now()->addDays(2),
            'end_at' => Carbon::now()->addDays(4),
            'payment_status' => 'pending',
        ]);
        
        // Try to book overlapping dates
        $startAt = Carbon::now()->addDays(1);
        $endAt = Carbon::now()->addDays(5);
        
        $response = $this->post("/booking/{$room->id}", [
            'start_at' => $startAt->format('Y-m-d'),
            'end_at' => $endAt->format('Y-m-d'),
        ]);
        
        $response->assertSessionHasErrors(['dates']);
    }

    /** @test */
    public function users_can_lookup_bookings_by_email()
    {
        $property = Property::factory()->create();
        $room = Room::factory()->create(['property_id' => $property->id]);
        
        $booking = Booking::factory()->create([
            'room_id' => $room->id,
            'email' => 'test@example.com',
            'payment_status' => 'pending',
        ]);
        
        $response = $this->post('/booking/find', [
            'email' => 'test@example.com',
        ]);
        
        $response->assertStatus(200);
        $response->assertViewIs('booking.my-bookings');
    }

    /** @test */
    public function booking_lookup_returns_error_for_invalid_email()
    {
        $response = $this->post('/booking/find', [
            'email' => 'nonexistent@example.com',
        ]);
        
        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function users_can_view_booking_lookup_page()
    {
        $response = $this->get('/booking/lookup');
        
        $response->assertStatus(200);
        $response->assertViewIs('booking.lookup');
    }
}

