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
        // Define the 3 locations we want
        $targetLocations = [
            [
                'name' => 'Fürstenwalde',
                'description' => null,
                'sort_order' => 1,
            ],
            [
                'name' => 'Hoppegarten',
                'description' => null,
                'sort_order' => 2,
            ],
            [
                'name' => 'Magdeburg',
                'description' => null,
                'sort_order' => 3,
            ],
        ];

        // Get all existing locations
        $allExistingLocations = Location::all();
        
        // Create or update the 3 target locations
        $targetLocationIds = [];
        foreach ($targetLocations as $locationData) {
            $location = Location::updateOrCreate(
                ['slug' => Str::slug($locationData['name'])],
                [
                    'name' => $locationData['name'],
                    'description' => $locationData['description'],
                    'image' => null,
                    'sort_order' => $locationData['sort_order'],
                ]
            );
            $targetLocationIds[] = $location->id;
            $this->command->info("Ensured location exists: {$locationData['name']}");
        }
        
        // Delete any locations that are NOT in our target list
        $locationsToDelete = Location::whereNotIn('id', $targetLocationIds)->get();
        foreach ($locationsToDelete as $locationToDelete) {
            $this->command->info("Deleting location: {$locationToDelete->name}");
            $locationToDelete->delete();
        }

        // Get the final 3 locations
        $locations = Location::orderBy('sort_order')->get();

        foreach ($locations as $location) {

            // Create 1 House per Location with location name (use firstOrCreate)
            $house = House::firstOrCreate(
                ['slug' => Str::slug($location->name . '-house')],
                [
                    'location_id' => $location->id,
                    'name' => $location->name . ' House',
                    'description' => 'Beautiful house in ' . $location->name . ' with multiple apartments',
                    'image' => null, // You can add images later
                ]
            );

            // Create 3-4 Apartments (Rooms) per House with location name
            $apartments = [
                ['name' => $location->name . ' Apartment A', 'capacity' => 2, 'base_price' => 80.00],
                ['name' => $location->name . ' Apartment B', 'capacity' => 4, 'base_price' => 120.00],
                ['name' => $location->name . ' Apartment C', 'capacity' => 3, 'base_price' => 100.00],
                ['name' => $location->name . ' Apartment D', 'capacity' => 2, 'base_price' => 90.00],
            ];

            // Get or create a property for backward compatibility
            $property = Property::firstOrCreate(
                ['name' => $location->name . ' Property'],
                [
                    'address' => 'Sample Address',
                    'city' => $location->name,
                    'postal_code' => '10115',
                ]
            );

            foreach ($apartments as $apt) {
                // Use firstOrCreate to avoid duplicate entry errors
                Room::firstOrCreate(
                    ['slug' => Str::slug($apt['name'])],
                    [
                        'property_id' => $property->id,
                        'house_id' => $house->id,
                        'name' => $apt['name'],
                        'capacity' => $apt['capacity'],
                        'base_price' => $apt['base_price'],
                        'short_term_allowed' => true,
                        'description' => "Comfortable {$apt['name']} with modern amenities. Perfect for {$apt['capacity']} guests.",
                    ]
                );
            }
        }

        $this->command->info('Booking flow data seeded successfully!');
        $this->command->info('Created: 3 Locations, 3 Houses, 12 Apartments');
    }
}
