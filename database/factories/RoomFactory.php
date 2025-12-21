<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'name' => 'Room ' . fake()->numberBetween(1, 10),
            'slug' => fake()->slug(),
            'capacity' => fake()->numberBetween(1, 6),
            'base_price' => fake()->randomFloat(2, 30, 100),
            'monthly_price' => fake()->randomFloat(2, 500, 1500),
            'short_term_allowed' => fake()->boolean(70),
            'description' => fake()->paragraph(),
        ];
    }
}


