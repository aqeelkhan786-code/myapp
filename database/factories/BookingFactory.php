<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $startAt = Carbon::now()->addDays(fake()->numberBetween(1, 30));
        $endAt = $startAt->copy()->addDays(fake()->numberBetween(1, 14));

        return [
            'room_id' => Room::factory(),
            'start_at' => $startAt,
            'end_at' => $endAt,
            'source' => fake()->randomElement(['manual', 'website', 'airbnb']),
            'status' => fake()->randomElement(['pending', 'confirmed', 'cancelled']),
            'guest_first_name' => fake()->firstName(),
            'guest_last_name' => fake()->lastName(),
            'job' => fake()->jobTitle(),
            'language' => fake()->randomElement(['Deutsch', 'Englisch']),
            'communication_preference' => fake()->randomElement(['Mail', 'Whatsapp']),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'renter_address' => fake()->streetAddress(),
            'renter_postal_code' => fake()->postcode(),
            'renter_city' => fake()->city(),
            'is_short_term' => fake()->boolean(60),
            'total_amount' => fake()->randomFloat(2, 50, 2000),
            'paid_amount' => fake()->randomFloat(2, 0, 2000),
            'payment_status' => fake()->randomElement(['pending', 'paid', 'refunded']),
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }
}

