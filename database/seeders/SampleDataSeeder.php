<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\Room;
use App\Models\IcalFeed;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $property = Property::firstOrCreate(
            ['name' => 'Haus Rosa'],
            [
                'address' => '123 Main Street',
                'city' => 'Berlin',
                'postal_code' => '10115',
            ]
        );

        $this->command->info("Created property: {$property->name}");

        $rooms = [
            [
                'name' => 'Zimmer 1',
                'slug' => 'zimmer-1',
                'capacity' => 2,
                'base_price' => 50.00,
                'short_term_allowed' => true,
                'description' => 'A cozy apartment with modern amenities.',
            ],
            [
                'name' => 'Zimmer 2',
                'slug' => 'zimmer-2',
                'capacity' => 4,
                'base_price' => 75.00,
                'short_term_allowed' => true,
                'description' => 'Spacious apartment ideal for families.',
            ],
            [
                'name' => 'Zimmer 3',
                'slug' => 'zimmer-3',
                'capacity' => 3,
                'base_price' => 60.00,
                'short_term_allowed' => false,
                'description' => 'Comfortable apartment for longer stays.',
            ],
        ];

        foreach ($rooms as $roomData) {
            $room = Room::firstOrCreate(
                ['slug' => $roomData['slug']],
                array_merge($roomData, ['property_id' => $property->id])
            );

            $exportFeed = IcalFeed::firstOrCreate(
                [
                    'room_id' => $room->id,
                    'direction' => 'export',
                ],
                [
                    'token' => Str::random(32),
                    'active' => true,
                ]
            );

            $this->command->info("Created room: {$room->name}");
        }
    }
}
