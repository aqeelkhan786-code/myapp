<?php

/**
 * Test Export URL - Check what bookings are being exported
 * Usage: php test-export-url.php [room_id] [token]
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Room;
use App\Models\Booking;
use App\Models\IcalFeed;

$roomId = $argv[1] ?? 77; // Default to room 77 from your image
$token = $argv[2] ?? null;

echo "🔍 Testing Export URL for Room ID: {$roomId}\n";
echo str_repeat("=", 60) . "\n\n";

// 1. Check Room
$room = Room::find($roomId);
if (!$room) {
    echo "❌ Room not found!\n";
    exit(1);
}

echo "✅ Room found: {$room->name}\n\n";

// 2. Check Export Feed
$exportFeed = IcalFeed::where('room_id', $roomId)
    ->where('direction', 'export')
    ->first();

if (!$exportFeed) {
    echo "❌ No export feed found for this room!\n";
    echo "   → Go to Admin → Rooms → Calendar Sync → Generate Export Token\n\n";
    exit(1);
}

if (!$exportFeed->token) {
    echo "❌ Export token not generated!\n";
    echo "   → Go to Admin → Rooms → Calendar Sync → Generate Export Token\n\n";
    exit(1);
}

echo "✅ Export feed found\n";
echo "   Token: {$exportFeed->token}\n";
echo "   Active: " . ($exportFeed->active ? 'YES' : 'NO') . "\n";
echo "   Export URL: " . route('ical.export', ['room' => $roomId, 'token' => $exportFeed->token]) . "\n\n";

// 3. Check Bookings for Export
echo "3️⃣ Checking Bookings for Export...\n";

// This is the EXACT query used in generateExport()
$bookings = Booking::where('room_id', $roomId)
    ->where('status', 'confirmed')
    ->where('source', '!=', 'airbnb')  // Excludes Airbnb bookings
    ->whereNotNull('end_at')  // Excludes long-term bookings without end date
    ->get();

echo "   Total bookings found for export: {$bookings->count()}\n\n";

if ($bookings->isEmpty()) {
    echo "   ⚠️  NO BOOKINGS TO EXPORT!\n\n";
    echo "   Possible reasons:\n";
    echo "   1. All bookings are from Airbnb (excluded from export)\n";
    echo "   2. All bookings have status != 'confirmed'\n";
    echo "   3. All bookings are long-term (end_at is null)\n\n";
    
    // Show all bookings for this room
    $allBookings = Booking::where('room_id', $roomId)->get();
    echo "   All bookings for this room:\n";
    if ($allBookings->isEmpty()) {
        echo "   - No bookings at all\n\n";
    } else {
        foreach ($allBookings as $booking) {
            echo "   - Booking #{$booking->id}:\n";
            echo "     Source: {$booking->source}\n";
            echo "     Status: {$booking->status}\n";
            echo "     Dates: {$booking->start_at->format('Y-m-d')} to " . ($booking->end_at ? $booking->end_at->format('Y-m-d') : 'NULL (long-term)') . "\n";
            echo "     Guest: {$booking->guest_first_name} {$booking->guest_last_name}\n";
            
            $excludedReasons = [];
            if ($booking->source === 'airbnb') {
                $excludedReasons[] = "Source is 'airbnb' (excluded to prevent circular sync)";
            }
            if ($booking->status !== 'confirmed') {
                $excludedReasons[] = "Status is '{$booking->status}' (only 'confirmed' exported)";
            }
            if (!$booking->end_at) {
                $excludedReasons[] = "end_at is NULL (long-term booking, iCal requires end date)";
            }
            
            if (!empty($excludedReasons)) {
                echo "     ❌ EXCLUDED: " . implode(', ', $excludedReasons) . "\n";
            } else {
                echo "     ✅ Would be exported\n";
            }
            echo "\n";
        }
    }
} else {
    echo "   ✅ Bookings that WILL be exported:\n";
    foreach ($bookings as $booking) {
        echo "   - Booking #{$booking->id}:\n";
        echo "     Guest: {$booking->guest_first_name} {$booking->guest_last_name}\n";
        echo "     Dates: {$booking->start_at->format('Y-m-d')} to {$booking->end_at->format('Y-m-d')}\n";
        echo "     Source: {$booking->source}\n";
        echo "     Status: {$booking->status}\n\n";
    }
}

// 4. Test Export Generation
echo "4️⃣ Testing iCal Generation...\n";
try {
    $icalService = app(\App\Services\IcalService::class);
    $icalContent = $icalService->generateExport($room);
    
    echo "   ✅ iCal generated successfully\n";
    echo "   Content length: " . strlen($icalContent) . " bytes\n\n";
    
    // Count VEVENTs
    $veventCount = substr_count($icalContent, 'BEGIN:VEVENT');
    echo "   VEVENTs in iCal: {$veventCount}\n";
    
    if ($veventCount === 0) {
        echo "   ⚠️  WARNING: iCal file is empty (no bookings)\n";
        echo "   → This is why Airbnb shows room as free!\n\n";
    } else {
        echo "   ✅ iCal contains {$veventCount} booking(s)\n\n";
    }
    
    // Show first few lines
    $lines = explode("\r\n", $icalContent);
    echo "   First 20 lines of iCal:\n";
    echo "   " . str_repeat("-", 56) . "\n";
    foreach (array_slice($lines, 0, 20) as $line) {
        if (strlen($line) > 60) {
            $line = substr($line, 0, 57) . '...';
        }
        echo "   {$line}\n";
    }
    echo "   " . str_repeat("-", 56) . "\n\n";
    
} catch (\Exception $e) {
    echo "   ❌ Error generating iCal: {$e->getMessage()}\n\n";
}

// 5. Recommendations
echo "5️⃣ Recommendations:\n";
echo str_repeat("-", 60) . "\n";

if ($bookings->isEmpty()) {
    echo "❌ PROBLEM: No bookings to export!\n\n";
    
    $allBookings = Booking::where('room_id', $roomId)->get();
    $hasAirbnbOnly = $allBookings->where('source', 'airbnb')->isNotEmpty() && 
                     $allBookings->where('source', '!=', 'airbnb')->isEmpty();
    $hasNonConfirmed = $allBookings->where('status', '!=', 'confirmed')->isNotEmpty();
    $hasLongTerm = $allBookings->whereNull('end_at')->isNotEmpty();
    
    if ($hasAirbnbOnly) {
        echo "   → All bookings are from Airbnb (excluded to prevent circular sync)\n";
        echo "   → Create a manual/website booking to test export\n";
        echo "   → Or: This is correct behavior if you only have Airbnb bookings\n\n";
    }
    
    if ($hasNonConfirmed) {
        echo "   → Some bookings have status != 'confirmed'\n";
        echo "   → Only 'confirmed' bookings are exported\n";
        echo "   → Change booking status to 'confirmed' in Admin → Bookings\n\n";
    }
    
    if ($hasLongTerm) {
        echo "   → Some bookings are long-term (no end date)\n";
        echo "   → iCal format requires end dates\n";
        echo "   → Long-term bookings are excluded from export\n\n";
    }
    
    echo "   SOLUTION:\n";
    echo "   1. Create a test booking:\n";
    echo "      - Admin → Bookings → Create\n";
    echo "      - Room: {$room->name}\n";
    echo "      - Source: 'Website' or 'Manual'\n";
    echo "      - Status: 'Confirmed'\n";
    echo "      - Dates: Set both start_at and end_at (not long-term)\n";
    echo "   2. Re-test export URL\n";
    echo "   3. Check Airbnb calendar (may take 1-2 hours to sync)\n\n";
} else {
    echo "✅ Bookings found for export\n";
    echo "   → Export URL should work\n";
    echo "   → If Airbnb still shows free, check:\n";
    echo "     1. URL is correctly pasted in Airbnb\n";
    echo "     2. Airbnb has synced (can take 1-2 hours)\n";
    echo "     3. Test URL directly in browser:\n";
    echo "        " . route('ical.export', ['room' => $roomId, 'token' => $exportFeed->token]) . "\n";
    echo "     4. Should download .ics file with bookings\n\n";
}

echo str_repeat("=", 60) . "\n";
echo "✅ Test complete!\n";
