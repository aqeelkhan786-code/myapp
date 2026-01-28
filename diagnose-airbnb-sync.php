<?php

/**
 * Quick Diagnostic Script for Airbnb Sync Issue
 * Run: php diagnose-airbnb-sync.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Booking;
use App\Models\IcalFeed;
use App\Models\Room;

echo "🔍 Airbnb Sync Diagnostic Tool\n";
echo str_repeat("=", 60) . "\n\n";

// 1. Check Import Feeds
echo "1️⃣ Checking Import Feeds...\n";
$importFeeds = IcalFeed::where('direction', 'import')->get();

if ($importFeeds->isEmpty()) {
    echo "   ❌ NO IMPORT FEEDS FOUND!\n";
    echo "   → Go to Admin → Rooms → Calendar Sync → Add Airbnb Import URL\n\n";
} else {
    echo "   ✅ Found {$importFeeds->count()} import feed(s):\n";
    foreach ($importFeeds as $feed) {
        $room = $feed->room;
        echo "   - Room: {$room->name} (ID: {$room->id})\n";
        echo "     URL: {$feed->url}\n";
        echo "     Active: " . ($feed->active ? '✅ YES' : '❌ NO') . "\n";
        echo "     Last Synced: " . ($feed->last_synced_at ? $feed->last_synced_at->format('Y-m-d H:i:s') : 'Never') . "\n";
        
        if ($feed->sync_log) {
            $log = $feed->sync_log;
            echo "     Last Sync Results:\n";
            echo "       - Imported: " . ($log['imported'] ?? 0) . "\n";
            echo "       - Updated: " . ($log['updated'] ?? 0) . "\n";
            echo "       - Cancelled: " . ($log['cancelled'] ?? 0) . "\n";
            echo "       - Skipped Conflicts: " . ($log['skipped_conflicts'] ?? 0) . "\n";
            if (!empty($log['errors'])) {
                echo "       - Errors: " . count($log['errors']) . "\n";
                foreach ($log['errors'] as $error) {
                    echo "         * {$error}\n";
                }
            }
        }
        echo "\n";
    }
}

// 2. Check Airbnb Bookings
echo "2️⃣ Checking Airbnb Bookings in Database...\n";
$airbnbBookings = Booking::where('source', 'airbnb')->get();

if ($airbnbBookings->isEmpty()) {
    echo "   ❌ NO AIRBNB BOOKINGS FOUND!\n";
    echo "   → This means sync is NOT working or never ran\n";
    echo "   → Check if import feed is active and synced\n\n";
} else {
    echo "   ✅ Found {$airbnbBookings->count()} Airbnb booking(s):\n";
    foreach ($airbnbBookings->take(10) as $booking) {
        $room = $booking->room;
        echo "   - Booking #{$booking->id}: {$room->name}\n";
        echo "     Guest: {$booking->guest_first_name} {$booking->guest_last_name}\n";
        echo "     Dates: {$booking->start_at->format('Y-m-d')} to {$booking->end_at->format('Y-m-d')}\n";
        echo "     Status: {$booking->status}\n";
        echo "     UID: {$booking->external_uid}\n";
        echo "\n";
    }
    if ($airbnbBookings->count() > 10) {
        echo "   ... and " . ($airbnbBookings->count() - 10) . " more\n";
    }
    echo "\n";
}

// 3. Check Active Bookings (All Sources)
echo "3️⃣ Checking All Confirmed Bookings...\n";
$allBookings = Booking::where('status', 'confirmed')
    ->with('room')
    ->get()
    ->groupBy('source');

echo "   Total confirmed bookings by source:\n";
foreach ($allBookings as $source => $bookings) {
    echo "   - {$source}: {$bookings->count()} booking(s)\n";
}

if ($allBookings->isEmpty()) {
    echo "   ⚠️  No confirmed bookings found at all\n\n";
} else {
    echo "\n";
}

// 4. Check Specific Rooms
echo "4️⃣ Checking Room Availability Logic...\n";
$rooms = Room::with('icalFeeds')->get();

foreach ($rooms as $room) {
    $confirmedBookings = Booking::where('room_id', $room->id)
        ->where('status', 'confirmed')
        ->get();
    
    $airbnbBookings = $confirmedBookings->where('source', 'airbnb');
    $otherBookings = $confirmedBookings->where('source', '!=', 'airbnb');
    
    echo "   Room: {$room->name} (ID: {$room->id})\n";
    echo "     Total confirmed bookings: {$confirmedBookings->count()}\n";
    echo "     - Airbnb: {$airbnbBookings->count()}\n";
    echo "     - Other (website/manual): {$otherBookings->count()}\n";
    
    // Check if import feed exists
    $importFeed = $room->icalFeeds->where('direction', 'import')->first();
    if ($importFeed) {
        echo "     Import Feed: ✅ Configured";
        if (!$importFeed->active) {
            echo " (but INACTIVE!)";
        }
        echo "\n";
    } else {
        echo "     Import Feed: ❌ NOT CONFIGURED\n";
    }
    echo "\n";
}

// 5. Test Availability Query
echo "5️⃣ Testing Availability Query (Example: Today's date)...\n";
$testDate = \Carbon\Carbon::today()->setTimezone('Europe/Berlin')->startOfDay();
$testEndDate = $testDate->copy()->addDays(7);

$unavailableRooms = Booking::where('status', 'confirmed')
    ->where(function ($q) use ($testDate, $testEndDate) {
        $q->where(function ($q2) use ($testDate, $testEndDate) {
            $q2->where('start_at', '<', $testEndDate->utc())
               ->where(function ($q3) use ($testDate) {
                   $q3->where('end_at', '>', $testDate->utc())
                      ->orWhereNull('end_at');
               });
        });
    })
    ->with('room')
    ->get()
    ->groupBy('room_id');

echo "   Rooms unavailable for next 7 days:\n";
if ($unavailableRooms->isEmpty()) {
    echo "   - None (all rooms available)\n";
} else {
    foreach ($unavailableRooms as $roomId => $bookings) {
        $room = $bookings->first()->room;
        $airbnbCount = $bookings->where('source', 'airbnb')->count();
        $otherCount = $bookings->where('source', '!=', 'airbnb')->count();
        echo "   - {$room->name}: {$bookings->count()} booking(s)";
        echo " (Airbnb: {$airbnbCount}, Other: {$otherCount})\n";
    }
}
echo "\n";

// 6. Recommendations
echo "6️⃣ Recommendations:\n";
echo str_repeat("-", 60) . "\n";

$hasImportFeeds = !$importFeeds->isEmpty();
$hasActiveFeeds = $importFeeds->where('active', true)->isNotEmpty();
$hasAirbnbBookings = !$airbnbBookings->isEmpty();

if (!$hasImportFeeds) {
    echo "❌ ACTION REQUIRED: No import feeds configured\n";
    echo "   → Go to Admin → Rooms → Select room → Calendar Sync tab\n";
    echo "   → Paste Airbnb iCal export URL\n";
    echo "   → Check 'Active' checkbox\n";
    echo "   → Click 'Save Import URL'\n\n";
} elseif (!$hasActiveFeeds) {
    echo "❌ ACTION REQUIRED: Import feeds exist but are INACTIVE\n";
    echo "   → Go to Admin → Rooms → Calendar Sync tab\n";
    echo "   → Check 'Active' checkbox for import feed\n";
    echo "   → Click 'Save Import URL'\n\n";
} elseif (!$hasAirbnbBookings) {
    echo "⚠️  WARNING: Import feeds configured but no bookings imported\n";
    echo "   → Click 'Sync Now' button to trigger immediate sync\n";
    echo "   → Check logs: storage/logs/laravel.log\n";
    echo "   → Verify Airbnb iCal URL is accessible\n\n";
} else {
    echo "✅ Import feeds configured and bookings found\n";
    echo "   → If rooms still show as available, check:\n";
    echo "     1. Booking dates match blocked dates on Airbnb\n";
    echo "     2. Booking status is 'confirmed' (not 'pending' or 'cancelled')\n";
    echo "     3. Availability query includes Airbnb bookings (should work)\n\n";
}

// 7. Quick Fix Commands
echo "7️⃣ Quick Fix Commands:\n";
echo str_repeat("-", 60) . "\n";
echo "   # Manual sync (if queue worker running):\n";
echo "   php artisan ical:sync-imports\n\n";
echo "   # Check queue worker:\n";
echo "   php artisan queue:work\n\n";
echo "   # Check scheduler:\n";
echo "   php artisan schedule:run\n\n";
echo "   # Check logs:\n";
echo "   tail -f storage/logs/laravel.log\n\n";

echo str_repeat("=", 60) . "\n";
echo "✅ Diagnostic complete!\n";
