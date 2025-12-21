<?php

namespace Database\Factories;

use App\Models\BlackoutDate;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BlackoutDate>
 */
class BlackoutDateFactory extends Factory
{
    protected $model = BlackoutDate::class;

    public function definition(): array
    {
        $startDate = Carbon::now()->addDays(fake()->numberBetween(1, 30));
        $endDate = $startDate->copy()->addDays(fake()->numberBetween(1, 7));

        return [
            'room_id' => Room::factory(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => fake()->randomElement(['Maintenance', 'Cleaning', 'Renovation', 'Holiday']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}


