# 🧪 Airbnb iCal Sync - Testing Guide

## 🎯 Testing Overview

Yeh guide aapko dikhayega ke Airbnb sync functionality ko kaise test karein - **local development aur production dono ke liye**.

---

## 📋 Pre-Testing Checklist

### ✅ Prerequisites:
- [ ] Laravel project running
- [ ] Database migrations run kiye hain
- [ ] Admin user created hai
- [ ] At least 1 room created hai
- [ ] Queue worker running (for "Sync Now" button)
- [ ] Cron/scheduler setup (optional for manual testing)

---

## 🧪 TEST 1: Create Test iCal File (Import Testing)

### Step 1.1: Create Test .ics File

**Location:** `public/test-calendar.ics`

**Content:**
```
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Test//Airbnb//EN
CALSCALE:GREGORIAN
METHOD:PUBLISH

BEGIN:VEVENT
UID:test-booking-001@airbnb.com
DTSTART;VALUE=DATE:20240215
DTEND;VALUE=DATE:20240220
SUMMARY:John Doe - Reservation
DESCRIPTION:Guest email: john.doe@example.com, Phone: +49123456789
STATUS:CONFIRMED
DTSTAMP:20240210T120000Z
CREATED:20240210T120000Z
LAST-MODIFIED:20240210T120000Z
END:VEVENT

BEGIN:VEVENT
UID:test-booking-002@airbnb.com
DTSTART;VALUE=DATE:20240225
DTEND;VALUE=DATE:20240228
SUMMARY:Jane Smith
DESCRIPTION:Contact: jane.smith@example.com
STATUS:CONFIRMED
DTSTAMP:20240210T120000Z
END:VEVENT

BEGIN:VEVENT
UID:test-booking-003@airbnb.com
DTSTART;VALUE=DATE:20240305
DTEND;VALUE=DATE:20240310
SUMMARY:Reserved
STATUS:CONFIRMED
DTSTAMP:20240210T120000Z
END:VEVENT

END:VCALENDAR
```

**Save karein:** `public/test-calendar.ics`

**Access URL:** `http://localhost:8000/test-calendar.ics` (ya aapki domain)

---

## 🧪 TEST 2: Test Import Functionality

### Step 2.1: Setup Import URL

1. **Admin Login:**
   ```
   http://localhost:8000/login
   → Admin credentials se login
   ```

2. **Navigate to Room:**
   ```
   Admin → Rooms → Select any room → "Calendar Sync" tab
   ```

3. **Paste Test URL:**
   ```
   Import URL field mein:
   http://localhost:8000/test-calendar.ics
   ```
   (Ya agar aapka localhost different port par hai, adjust karein)

4. **Check "Active" checkbox** ✅

5. **Click "Save Import URL"**

### Step 2.2: Manual Sync Test

1. **Click "Sync Now" button**

2. **Check Response:**
   - Page refresh hona chahiye
   - Success message: "iCal sync has been queued..."
   - Ya error message (agar kuch wrong hai)

3. **Wait 5-10 seconds** (queue processing ke liye)

### Step 2.3: Verify Import

**Check Database:**
```bash
# Tinker se check karein
php artisan tinker
```

```php
// Check bookings created
\App\Models\Booking::where('source', 'airbnb')->get();

// Expected: 3 bookings
// - Booking 1: Feb 15-20 (John Doe)
// - Booking 2: Feb 25-28 (Jane Smith)  
// - Booking 3: Mar 5-10 (Reserved -> "Airbnb Guest")

// Check guest info
\App\Models\Booking::where('source', 'airbnb')->first();
// guest_first_name should be "John" (not "Airbnb")
// guest_last_name should be "Doe" (not "Guest")
// email should be "john.doe@example.com" (if parsed correctly)
```

**Check Admin Panel:**
```
Admin → Bookings
→ Filter by Source: "Airbnb"
→ Should see 3 bookings
→ Guest names check karein (parsed correctly?)
```

**Check Sync Log:**
```
Admin → Rooms → Calendar Sync tab
→ "Last synced" timestamp updated?
→ sync_log details visible? (check database or logs)
```

---

## 🧪 TEST 3: Test Export Functionality

### Step 3.1: Create Test Booking (Manual)

1. **Admin → Bookings → Create**
   - Room select karein
   - Guest: "Test Guest"
   - Dates: **March 15-20, 2024** (future dates, test calendar se different)
   - Source: **"Website"** (important - Airbnb source export nahi hota)
   - Status: **"Confirmed"**
   - Save

### Step 3.2: Generate Export Token

1. **Admin → Rooms → Calendar Sync tab**
2. **Export section** mein **"Generate Export Token"** click
3. **Export URL copy karein:**
   ```
   http://localhost:8000/ical/5/abc123def456.ics
   ```

### Step 3.3: Test Export URL

**Browser mein directly open karein:**
```
http://localhost:8000/ical/5/abc123def456.ics
```

**Expected Result:**
- Browser download karega `.ics` file
- Ya browser mein iCal content dikhai dega

**Verify .ics Content:**
```ics
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//MaRoom//Booking System//EN

BEGIN:VEVENT
UID:maroom-booking-XXX@maroom.local
DTSTART:20240315T000000
DTEND:20240320T000000
SUMMARY:Room Name - Test Guest
DESCRIPTION:Booking #XXX
LOCATION:...
STATUS:CONFIRMED
END:VEVENT

END:VCALENDAR
```

**Important Checks:**
- ✅ Booking included hai
- ✅ Dates correct hain
- ✅ Guest name correct hai
- ❌ Airbnb source bookings **NOT included** (circular sync prevention)

### Step 3.4: Verify Export Excludes Airbnb Bookings

1. **Check:** Export URL mein sirf website/manual bookings honi chahiye
2. **Airbnb bookings** (TEST 2 se imported) **excluded** honi chahiye

**Tinker se verify:**
```php
php artisan tinker

// Check export query
$room = \App\Models\Room::find(5);
$bookings = \App\Models\Booking::where('room_id', $room->id)
    ->where('status', 'confirmed')
    ->where('source', '!=', 'airbnb') // Export excludes Airbnb
    ->whereNotNull('end_at')
    ->get();

// Should NOT include Airbnb bookings
```

---

## 🧪 TEST 4: Test Conflict Detection

### Step 4.1: Create Overlapping Booking

1. **Admin → Bookings → Create**
   - Room: Same room (TEST 2 wali)
   - Guest: "Conflict Test"
   - Dates: **Feb 18-22, 2024** (overlaps with Airbnb booking Feb 15-20)
   - Source: **"Manual"** (or "Website")
   - Status: **"Confirmed"**
   - Save

### Step 4.2: Re-sync Import (With Conflict)

1. **Admin → Rooms → Calendar Sync → "Sync Now"**

2. **Check Logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```
   
   **Expected Log Entry:**
   ```
   [WARNING] iCal import skipped due to conflict
   - room_id: 5
   - uid: test-booking-001@airbnb.com
   - start_at: 2024-02-15
   - end_at: 2024-02-20
   ```

3. **Verify Sync Log:**
   ```
   Admin → Rooms → Check sync_log (database)
   → "skipped_conflicts": 1
   ```

4. **Verify Booking NOT Created:**
   ```php
   php artisan tinker
   
   // Airbnb booking Feb 15-20 should exist
   // But if conflict detected, no duplicate created
   \App\Models\Booking::where('external_uid', 'test-booking-001@airbnb.com')
       ->get();
   // Should return 1 booking (original)
   ```

---

## 🧪 TEST 5: Test Guest Name Parsing

### Step 5.1: Create Multiple iCal Formats

**Test File:** `public/test-guest-names.ics`

```ics
BEGIN:VCALENDAR
VERSION:2.0

BEGIN:VEVENT
UID:name-test-1@airbnb.com
DTSTART;VALUE=DATE:20240401
DTEND;VALUE=DATE:20240405
SUMMARY:John Doe - Reservation
STATUS:CONFIRMED
END:VEVENT

BEGIN:VEVENT
UID:name-test-2@airbnb.com
DTSTART;VALUE=DATE:20240410
DTEND;VALUE=DATE:20240415
SUMMARY:Jane Smith
STATUS:CONFIRMED
END:VEVENT

BEGIN:VEVENT
UID:name-test-3@airbnb.com
DTSTART;VALUE=DATE:20240420
DTEND;VALUE=DATE:20240425
SUMMARY:Reserved
STATUS:CONFIRMED
END:VEVENT

BEGIN:VEVENT
UID:name-test-4@airbnb.com
DTSTART;VALUE=DATE:20240501
DTEND;VALUE=DATE:20240505
SUMMARY:Airbnb Guest - Confirmed
STATUS:CONFIRMED
END:VEVENT

END:VCALENDAR
```

### Step 5.2: Import and Verify

1. **Update import URL** to new test file
2. **Sync Now** click
3. **Check bookings:**
   - `name-test-1`: First="John", Last="Doe" ✅
   - `name-test-2`: First="Jane", Last="Smith" ✅
   - `name-test-3`: First="Airbnb", Last="Guest" ✅ (fallback for "Reserved")
   - `name-test-4`: First="Airbnb", Last="Guest" ✅ (contains "Airbnb" word)

---

## 🧪 TEST 6: Test Email/Phone Extraction

### Step 6.1: Create iCal with Contact Info

**Test File:** `public/test-contact-info.ics`

```ics
BEGIN:VCALENDAR
VERSION:2.0

BEGIN:VEVENT
UID:contact-test-1@airbnb.com
DTSTART;VALUE=DATE:20240601
DTEND;VALUE=DATE:20240605
SUMMARY:Test Guest
DESCRIPTION:Email: test@example.com, Phone: +49123456789
STATUS:CONFIRMED
END:VEVENT

BEGIN:VEVENT
UID:contact-test-2@airbnb.com
DTSTART;VALUE=DATE:20240610
DTEND;VALUE=DATE:20240615
SUMMARY:Another Guest
DESCRIPTION:Contact me at guest@test.com or call +49987654321
STATUS:CONFIRMED
END:VEVENT

BEGIN:VEVENT
UID:contact-test-3@airbnb.com
DTSTART;VALUE=DATE:20240620
DTEND;VALUE=DATE:20240625
SUMMARY:No Contact
DESCRIPTION:No email or phone in description
STATUS:CONFIRMED
END:VEVENT

END:VCALENDAR
```

### Step 6.2: Verify Extraction

```php
php artisan tinker

$booking1 = \App\Models\Booking::where('external_uid', 'contact-test-1@airbnb.com')->first();
// email should be: "test@example.com"
// phone should be: "+49123456789"

$booking2 = \App\Models\Booking::where('external_uid', 'contact-test-2@airbnb.com')->first();
// email should be: "guest@test.com"
// phone should be: "+49987654321"

$booking3 = \App\Models\Booking::where('external_uid', 'contact-test-3@airbnb.com')->first();
// email should be: null (or placeholder)
// phone should be: null
```

---

## 🧪 TEST 7: Test Cancellation Detection

### Step 7.1: Remove Booking from iCal

1. **Original test-calendar.ics file edit karein**
2. **Remove one VEVENT** (e.g., test-booking-002)

### Step 7.2: Re-sync

1. **Sync Now** click
2. **Check booking status:**

```php
php artisan tinker

// Removed booking should be cancelled
$booking = \App\Models\Booking::where('external_uid', 'test-booking-002@airbnb.com')->first();
// status should be: "cancelled"
```

---

## 🧪 TEST 8: Test Queue Worker (For "Sync Now")

### Step 8.1: Start Queue Worker

**Terminal 1:**
```bash
php artisan queue:work
```

### Step 8.2: Trigger Sync

**Browser:**
- Admin → Rooms → Calendar Sync → "Sync Now"

### Step 8.3: Monitor Queue

**Terminal 1 Output:**
```
Processing: App\Jobs\SyncIcalFeed
Processing iCal sync for room: Apartment Berlin Mitte
✓ Imported: 3, Updated: 0, Cancelled: 0
Processed: App\Jobs\SyncIcalFeed
```

---

## 🧪 TEST 9: Test Scheduled Sync (Cron)

### Step 9.1: Manual Scheduler Run

```bash
php artisan schedule:run
```

**Expected Output:**
```
Running scheduled command: php artisan ical:sync-imports
iCal sync completed!
```

### Step 9.2: Verify Hourly Schedule

**Check:** `app/Console/Kernel.php`
```php
$schedule->command('ical:sync-imports')
    ->hourly()
    ->withoutOverlapping();
```

**Test:**
```bash
# List scheduled tasks
php artisan schedule:list
```

---

## 🧪 TEST 10: Test Real Airbnb URL (Production Test)

### Step 10.1: Get Real Airbnb URL

1. **Airbnb Host Dashboard**
2. **Listing → Calendar → Export calendar**
3. **Copy iCal URL**

### Step 10.2: Test in Staging

1. **Use real Airbnb URL in staging environment**
2. **Monitor logs** for errors
3. **Verify bookings imported correctly**

---

## ✅ Testing Checklist

### Import Tests:
- [ ] ✅ iCal file parses correctly
- [ ] ✅ Bookings created in database
- [ ] ✅ Guest names parsed correctly
- [ ] ✅ Email/phone extracted (if available)
- [ ] ✅ Dates correct
- [ ] ✅ Status mapped correctly
- [ ] ✅ External UID stored

### Export Tests:
- [ ] ✅ Export URL accessible
- [ ] ✅ iCal format valid
- [ ] ✅ Bookings included
- [ ] ✅ Airbnb bookings **excluded** (critical!)
- [ ] ✅ Dates correct
- [ ] ✅ Guest names included

### Conflict Tests:
- [ ] ✅ Overlapping bookings skipped
- [ ] ✅ Warning logged
- [ ] ✅ Sync log shows skipped count
- [ ] ✅ No duplicate bookings created

### Cancellation Tests:
- [ ] ✅ Removed bookings marked cancelled
- [ ] ✅ Status updated correctly

### Integration Tests:
- [ ] ✅ Queue worker processes sync jobs
- [ ] ✅ Scheduler runs hourly
- [ ] ✅ Manual sync works
- [ ] ✅ Admin UI shows sync status

---

## 🐛 Common Issues & Solutions

### Issue 1: "Sync Now" Button Does Nothing

**Check:**
```bash
# Queue worker running?
php artisan queue:work

# Check queue table
php artisan tinker
\DB::table('jobs')->get();
```

**Solution:** Start queue worker

---

### Issue 2: Bookings Not Importing

**Check:**
```bash
# Check logs
tail -f storage/logs/laravel.log

# Common errors:
# - Invalid URL
# - Network timeout
# - Parse errors
```

**Solution:** Verify iCal URL accessible, check format

---

### Issue 3: Export URL Returns 404

**Check:**
```bash
# Route exists?
php artisan route:list | grep ical

# Token correct?
php artisan tinker
\App\Models\IcalFeed::where('direction', 'export')->first();
```

**Solution:** Verify token generated, check route

---

### Issue 4: Guest Names Not Parsing

**Check:**
```php
php artisan tinker
$booking = \App\Models\Booking::where('source', 'airbnb')->first();
$booking->guest_first_name; // Should not be "Airbnb"
```

**Solution:** Check iCal SUMMARY field format

---

### Issue 5: Circular Sync (Airbnb Bookings in Export)

**Check:**
```bash
# Export URL open karein
# Check if Airbnb bookings included
```

**Expected:** ❌ Airbnb bookings NOT in export

**Solution:** Verify export query filters `source != 'airbnb'`

---

## 📊 Expected Database State After Tests

```php
// Bookings table
bookings:
- id: 1-3 (from test-calendar.ics)
  source: 'airbnb'
  external_uid: 'test-booking-XXX@airbnb.com'
  
- id: 4 (manual test booking)
  source: 'website' or 'manual'
  
// IcalFeeds table
ical_feeds:
- room_id: 5
  direction: 'import'
  url: 'http://localhost:8000/test-calendar.ics'
  active: true
  last_synced_at: [timestamp]
  
- room_id: 5
  direction: 'export'
  token: 'abc123def456...'
  active: true
```

---

## 🎯 Quick Test Script

**Save as:** `test-ical-sync.php` (project root)

```php
<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Booking;
use App\Models\IcalFeed;
use App\Models\Room;

echo "🧪 Testing iCal Sync\n\n";

// Test 1: Check import feed exists
echo "1. Checking import feed...\n";
$feed = IcalFeed::where('direction', 'import')->first();
if ($feed) {
    echo "   ✅ Import feed found: {$feed->url}\n";
    echo "   Status: " . ($feed->active ? 'Active' : 'Inactive') . "\n";
} else {
    echo "   ❌ No import feed found\n";
}

// Test 2: Check export feed exists
echo "\n2. Checking export feed...\n";
$exportFeed = IcalFeed::where('direction', 'export')->first();
if ($exportFeed && $exportFeed->token) {
    echo "   ✅ Export token generated\n";
} else {
    echo "   ❌ No export token found\n";
}

// Test 3: Check Airbnb bookings
echo "\n3. Checking Airbnb bookings...\n";
$airbnbBookings = Booking::where('source', 'airbnb')->count();
echo "   Found: {$airbnbBookings} Airbnb bookings\n";

// Test 4: Check export excludes Airbnb
echo "\n4. Checking export query...\n";
$room = Room::first();
if ($room) {
    $exportBookings = Booking::where('room_id', $room->id)
        ->where('status', 'confirmed')
        ->where('source', '!=', 'airbnb')
        ->whereNotNull('end_at')
        ->count();
    echo "   Exportable bookings: {$exportBookings}\n";
    $allBookings = Booking::where('room_id', $room->id)
        ->where('status', 'confirmed')
        ->count();
    echo "   Total bookings: {$allBookings}\n";
    if ($exportBookings < $allBookings) {
        echo "   ✅ Airbnb bookings excluded (correct!)\n";
    }
}

echo "\n✅ Test complete!\n";
```

**Run:**
```bash
php test-ical-sync.php
```

---

## 📝 Test Results Template

**Date:** ___________  
**Tester:** ___________  
**Environment:** Local / Staging / Production

| Test | Status | Notes |
|------|--------|-------|
| Import from iCal | ✅/❌ | |
| Export to iCal | ✅/❌ | |
| Conflict Detection | ✅/❌ | |
| Guest Name Parsing | ✅/❌ | |
| Email/Phone Extraction | ✅/❌ | |
| Cancellation Detection | ✅/❌ | |
| Queue Worker | ✅/❌ | |
| Scheduled Sync | ✅/❌ | |

**Issues Found:**
1. 
2. 
3. 

---

**Happy Testing! 🚀**
