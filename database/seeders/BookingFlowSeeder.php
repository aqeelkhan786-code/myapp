<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;
use App\Models\House;
use App\Models\Room;
use App\Models\Property;
use Illuminate\Support\Str;

class BookingFlowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 3 Locations
        $locations = [
            [
                'name' => 'Berlin Center',
                'description' => 'Prime location in the heart of Berlin',
                'sort_order' => 1,
            ],
            [
                'name' => 'Berlin East',
                'description' => 'Modern apartments in Berlin East',
                'sort_order' => 2,
            ],
            [
                'name' => 'Berlin West',
                'description' => 'Luxury accommodations in Berlin West',
                'sort_order' => 3,
            ],
        ];

        foreach ($locations as $locationData) {
            $location = Location::create([
                'name' => $locationData['name'],
                'slug' => Str::slug($locationData['name']),
                'description' => $locationData['description'],
                'image' => null, // You can add images later
                'sort_order' => $locationData['sort_order'],
            ]);

            // Create 1 House per Location
            $house = House::create([
                'location_id' => $location->id,
                'name' => 'Main House',
                'slug' => Str::slug($location->name . '-main-house'),
                'description' => 'Beautiful main house with multiple apartments',
                'image' => null, // You can add images later
            ]);

            // Create 3-4 Apartments (Rooms) per House
            $apartments = [
                ['name' => 'Apartment A', 'capacity' => 2, 'base_price' => 80.00],
                ['name' => 'Apartment B', 'capacity' => 4, 'base_price' => 120.00],
                ['name' => 'Apartment C', 'capacity' => 3, 'base_price' => 100.00],
                ['name' => 'Apartment D', 'capacity' => 2, 'base_price' => 90.00],
            ];

            // Get or create a property for backward compatibility
            $property = Property::firstOrCreate(
                ['name' => $location->name . ' Property'],
                [
                    'address' => 'Sample Address',
                    'city' => 'Berlin',
                    'postal_code' => '10115',
                ]
            );

            foreach ($apartments as $apt) {
                Room::create([
                    'property_id' => $property->id,
                    'house_id' => $house->id,
                    'name' => $apt['name'],
                    'slug' => Str::slug($location->name . '-' . $apt['name']),
                    'capacity' => $apt['capacity'],
                    'base_price' => $apt['base_price'],
                    'short_term_allowed' => true,
                    'description' => "Comfortable {$apt['name']} with modern amenities. Perfect for {$apt['capacity']} guests.",
                ]);
            }
        }

        $this->command->info('Booking flow data seeded successfully!');
        $this->command->info('Created: 3 Locations, 3 Houses, 12 Apartments');
    }
}
