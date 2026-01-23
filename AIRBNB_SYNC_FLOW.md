# 🏠 Airbnb iCal Sync - Complete Flow (Your Site)

## 📋 Overview

Yeh document aapko step-by-step dikhata hai ke aapki site par Airbnb calendar sync kaise kaam karega.

---

## 🎯 Scenario Example

**Room:** "Apartment Berlin Mitte"  
**Airbnb Listing:** Airbnb par same apartment listed hai

**Goal:** 
- Airbnb par booking → MaRoom mein auto block
- MaRoom par booking → Airbnb mein auto block
- **No double booking!**

---

## 🔧 STEP 1: Initial Setup (Admin)

### 1.1 Admin Login
```
https://yourdomain.com/login
→ Admin credentials se login
→ Admin Dashboard open
```

### 1.2 Room Settings Open
```
Admin → Rooms → "Apartment Berlin Mitte" click
→ Tabs mein "Calendar Sync" tab open
```

### 1.3 Import Setup (Airbnb → MaRoom)

**Action:**
1. Airbnb Host Dashboard kholo
2. Listing → **Calendar** → **Sync calendars** → **Export calendar**
3. Airbnb aapko URL dega:
   ```
   https://www.airbnb.com/calendar/ical/1234567890.ics?user_id=...
   ```

4. MaRoom Admin mein:
   - **Import (Airbnb → MaRoom)** section
   - URL paste karo
   - ✅ **Active** checkbox ON
   - **Save Import URL** button

**Result:**
- URL database mein save ho jata hai
- Feed `active = true` set hota hai

### 1.4 Export Setup (MaRoom → Airbnb)

**Action:**
1. MaRoom Admin → **Export (MaRoom → Airbnb)** section
2. **Generate Export Token** button click
3. Export URL generate hota hai:
   ```
   https://yourdomain.com/ical/5/abc123def456ghi789.ics
   ```

4. Airbnb Host Dashboard mein:
   - Calendar → **Sync calendars** → **Import calendar**
   - MaRoom export URL paste karo
   - Save

**Result:**
- Airbnb ab MaRoom ki bookings ko import karta rahega
- Token secure hai (jo bhi URL access kare wo bookings dekh sakta hai)

---

## 📥 STEP 2: Import Flow (Airbnb → MaRoom)

### 2.1 Automatic Sync (Hourly)

**What Happens:**
```
Every Hour:
1. Cron job runs: php artisan schedule:run
2. Command runs: php artisan ical:sync-imports
3. System checks all active import feeds
4. For each feed:
   - Fetches iCal URL from Airbnb
   - Parses .ics file
   - Extracts bookings (DTSTART, DTEND, SUMMARY, etc.)
   - Imports/Updates bookings in database
```

**Example Timeline:**
```
10:00 AM - Cron runs
10:00:05 - System fetches Airbnb iCal URL
10:00:06 - Parses .ics file
10:00:07 - Finds 3 bookings:
           - Booking 1: Feb 15-20 (Guest: John Doe)
           - Booking 2: Feb 25-28 (Guest: Jane Smith)
           - Booking 3: Mar 5-10 (Guest: Mike Johnson)
10:00:08 - Checks existing bookings:
           - Booking 1: NEW → Creates in database
           - Booking 2: Already exists → Updates dates if changed
           - Booking 3: NEW → Creates in database
10:00:09 - Sync complete
```

**Database Entries:**
```php
bookings table:
- id: 101
- room_id: 5
- source: 'airbnb'
- guest_first_name: 'John'
- guest_last_name: 'Doe'
- email: 'john@example.com' (if available)
- start_at: '2024-02-15 00:00:00'
- end_at: '2024-02-20 00:00:00'
- status: 'confirmed'
- external_uid: 'airbnb-abc123'
- notes: 'Imported from Airbnb - UID: airbnb-abc123'

ical_feeds table:
- id: 1
- room_id: 5
- direction: 'import'
- url: 'https://www.airbnb.com/calendar/ical/...'
- active: true
- last_synced_at: '2024-02-10 10:00:09'
- sync_log: {
    "imported": 2,
    "updated": 1,
    "cancelled": 0,
    "skipped_conflicts": 0
  }
```

### 2.2 Manual Sync (Admin Click "Sync Now")

**What Happens:**
```
Admin clicks "Sync Now" button:
1. Request goes to: POST /admin/rooms/{room}/ical/sync
2. System dispatches SyncIcalFeed job to queue
3. Queue worker processes job immediately
4. Same process as automatic sync (but immediate)
5. Admin page refresh → sees "Last synced: Feb 10, 2024 10:15:23"
```

**Use Case:**
- New booking Airbnb par hui
- Abhi wait nahi karna chahte (hourly sync ka)
- **"Sync Now"** click → Instant import

### 2.3 What Gets Imported?

**From Airbnb iCal:**
```
BEGIN:VEVENT
UID:airbnb-abc123
DTSTART:20240215
DTEND:20240220
SUMMARY:John Doe - Reservation
DESCRIPTION:Guest email: john@example.com, Phone: +49123456789
STATUS:CONFIRMED
END:VEVENT
```

**After Parsing (MaRoom Database):**
- ✅ Guest name: "John Doe" (parsed from SUMMARY)
- ✅ Email: "john@example.com" (extracted from DESCRIPTION)
- ✅ Phone: "+49123456789" (extracted from DESCRIPTION)
- ✅ Dates: Feb 15-20 (parsed from DTSTART/DTEND)
- ✅ Status: "confirmed" (mapped from STATUS)

### 2.4 Conflict Detection (Important!)

**Scenario:**
- Manual booking exists: Feb 18-25 (Guest: Sarah)
- Airbnb booking incoming: Feb 20-22 (Guest: John)

**What Happens:**
```
1. System checks: Is Feb 20-22 available?
2. Finds conflict: Feb 18-25 booking already exists
3. Airbnb booking SKIPPED (not imported)
4. Logs warning:
   "iCal import skipped due to conflict"
   - room_id: 5
   - dates: Feb 20-22
5. sync_log mein:
   "skipped_conflicts": 1
```

**Result:**
- ❌ Airbnb booking NOT imported (double booking prevented)
- ✅ Manual booking remains intact
- ✅ Admin ko sync log mein dikhai degi: "1 conflict skipped"

---

## 📤 STEP 3: Export Flow (MaRoom → Airbnb)

### 3.1 When Does Export Happen?

**Export is REAL-TIME (on-demand):**
- Airbnb har 1-2 hours mein MaRoom ki URL fetch karta hai
- URL: `https://yourdomain.com/ical/5/abc123def456.ics`

### 3.2 What Gets Exported?

**MaRoom Database:**
```php
Confirmed bookings for Room 5:
1. Booking #50: Feb 10-15 (source: 'website', Guest: Alice)
2. Booking #51: Feb 20-25 (source: 'manual', Guest: Bob)
3. Booking #52: Feb 28-Mar 5 (source: 'airbnb', Guest: John) ❌ EXCLUDED
```

**iCal Output (What Airbnb Gets):**
```
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//MaRoom//Booking System//EN

BEGIN:VEVENT
UID:maroom-booking-50@maroom.local
DTSTART:20240210T000000
DTEND:20240215T000000
SUMMARY:Apartment Berlin Mitte - Alice Brown
DESCRIPTION:Booking #50
LOCATION:123 Main St, Berlin
STATUS:CONFIRMED
END:VEVENT

BEGIN:VEVENT
UID:maroom-booking-51@maroom.local
DTSTART:20240220T000000
DTEND:20240225T000000
SUMMARY:Apartment Berlin Mitte - Bob Wilson
DESCRIPTION:Booking #51
LOCATION:123 Main St, Berlin
STATUS:CONFIRMED
END:VEVENT

END:VCALENDAR
```

**Important:** Booking #52 (Airbnb source) **NOT exported** - prevents circular sync!

### 3.3 What Happens on Airbnb?

```
Airbnb fetches URL:
1. Gets .ics file from MaRoom
2. Parses bookings
3. Blocks dates on Airbnb calendar:
   - Feb 10-15: Blocked
   - Feb 20-25: Blocked
4. Airbnb listing shows these dates as "Not available"
```

**Result:**
- ✅ MaRoom bookings → Airbnb blocked dates
- ✅ No circular sync (Airbnb bookings excluded)
- ✅ Airbnb customers can't book blocked dates

---

## 👤 STEP 4: Customer Booking Flow

### 4.1 Customer Searches Availability

**Customer Action:**
```
Customer visits: https://yourdomain.com
→ Selects dates: Feb 15-20
→ Searches available rooms
```

**What Happens Behind Scenes:**
```php
// BookingController@index or BookingFlowController@search

1. System checks all confirmed bookings:
   - Manual bookings
   - Website bookings
   - Airbnb bookings (imported) ✅ INCLUDED

2. Filters out unavailable rooms:
   Room 5 (Apartment Berlin Mitte):
   - Has Airbnb booking: Feb 15-20 ❌
   - Customer's dates: Feb 15-20
   - Result: ROOM NOT AVAILABLE (filtered out)

3. Shows only available rooms to customer
```

**Customer Sees:**
- ❌ Room 5: "Not available for selected dates"
- ✅ Other rooms: Available (if dates clear)

### 4.2 Customer Books on MaRoom

**Scenario:**
- Customer books: Feb 25-28 (Room 5)
- Payment successful
- Booking created with `source = 'website'`

**What Happens:**
```
1. Booking created in database:
   - id: 53
   - room_id: 5
   - source: 'website'
   - start_at: '2024-02-25'
   - end_at: '2024-02-28'
   - status: 'confirmed'

2. Next Airbnb sync (1-2 hours later):
   - Airbnb fetches export URL
   - Gets updated .ics (includes Booking #53)
   - Airbnb blocks Feb 25-28 dates

3. Airbnb customers see:
   - Feb 25-28: "Not available"
```

**Result:**
- ✅ MaRoom booking → Airbnb blocked (within 1-2 hours)
- ✅ No double booking possible

---

## 🔄 STEP 5: Complete Flow Example

### Day 1 (Monday, Feb 5)

**Morning (10:00 AM):**
```
Admin Setup:
✅ Import URL: Airbnb export URL pasted
✅ Export token: Generated
✅ Both feeds active
```

**Afternoon (2:00 PM):**
```
Airbnb Booking:
- Guest: John books on Airbnb
- Dates: Feb 15-20
- Airbnb calendar updated
```

**Evening (6:00 PM):**
```
Automatic Sync (Hourly):
- System fetches Airbnb iCal
- Finds new booking (Feb 15-20)
- Imports to MaRoom
- Database: Booking created (source: airbnb)
```

**Result:** 
- ✅ Airbnb booking → MaRoom blocked
- ✅ MaRoom website: Feb 15-20 dates unavailable

---

### Day 2 (Tuesday, Feb 6)

**Morning (11:00 AM):**
```
Customer Books on MaRoom:
- Guest: Sarah searches dates
- Finds Room 5 available: Feb 25-28
- Completes booking
- Payment successful
- Booking created (source: website)
```

**Afternoon (1:00 PM - 2:00 PM):**
```
Airbnb Auto-Fetch (Every 1-2 hours):
- Airbnb fetches MaRoom export URL
- Gets updated .ics file
- Sees new booking (Feb 25-28)
- Blocks dates on Airbnb calendar
```

**Result:**
- ✅ MaRoom booking → Airbnb blocked
- ✅ Airbnb customers: Feb 25-28 unavailable

---

### Day 3 (Wednesday, Feb 7)

**Morning (10:00 AM):**
```
Conflicting Booking Attempt:
- Another guest tries to book on Airbnb
- Dates: Feb 18-22
- System checks:
  ❌ Feb 18-22 conflicts with existing booking (Feb 15-20)
  ❌ Airbnb rejects booking
```

**Evening (6:00 PM):**
```
Automatic Sync:
- System imports Airbnb feed
- Airbnb calendar has no new bookings
- Sync complete (no changes)
```

---

## 📊 STEP 6: Admin Dashboard View

### 6.1 Bookings List

**Admin → Bookings:**
```
All Bookings:
┌────┬─────────────────┬──────────────┬──────────┬──────────────┬─────────┐
│ ID │ Room            │ Guest        │ Dates    │ Source       │ Status  │
├────┼─────────────────┼──────────────┼──────────┼──────────────┼─────────┤
│ 50 │ Apartment B...  │ Alice Brown  │ Feb 10-15│ website      │ confirm │
│ 51 │ Apartment B...  │ Bob Wilson   │ Feb 20-25│ manual       │ confirm │
│ 52 │ Apartment B...  │ John Doe     │ Feb 15-20│ airbnb       │ confirm │
│ 53 │ Apartment B...  │ Sarah Jones  │ Feb 25-28│ website      │ confirm │
└────┴─────────────────┴──────────────┴──────────┴──────────────┴─────────┘
```

**Note:** Source column se admin ko pata chalta hai booking kahan se aayi.

### 6.2 Calendar View

**Admin → Bookings → Calendar:**
```
February 2024:
┌─────┬─────┬─────┬─────┬─────┬─────┬─────┐
│ Mon │ Tue │ Wed │ Thu │ Fri │ Sat │ Sun │
├─────┼─────┼─────┼─────┼─────┼─────┼─────┤
│ 10  │ 11  │ 12  │ 13  │ 14  │ 15  │ 16  │
│     │     │     │     │     │[50] │[50] │ ← Website booking
│     │     │     │     │     │[52] │[52] │ ← Airbnb booking (overlap!)
│ 17  │ 18  │ 19  │ 20  │ 21  │ 22  │ 23  │
│[52] │[52] │[52] │[51] │[51] │[51] │[51] │
│ 24  │ 25  │ 26  │ 27  │ 28  │     │     │
│     │[51] │[53] │[53] │[53] │     │     │
└─────┴─────┴─────┴─────┴─────┴─────┴─────┘
```

**Visual Indicator:**
- Different colors for different sources
- Overlapping bookings visible (if any conflicts)

### 6.3 Sync Status

**Admin → Rooms → "Apartment Berlin Mitte" → Calendar Sync Tab:**

```
Import (Airbnb → MaRoom):
✅ URL: https://www.airbnb.com/calendar/ical/...
✅ Active: Yes
🔄 Last synced: Feb 7, 2024 18:00:05
📊 Sync Log:
   - Imported: 3 bookings
   - Updated: 0 bookings
   - Cancelled: 0 bookings
   - Skipped conflicts: 1 booking
   
[Sync Now] button

---

Export (MaRoom → Airbnb):
✅ Token: Generated
📋 Export URL: https://yourdomain.com/ical/5/abc123def456.ics
💡 Paste this URL into Airbnb calendar import settings

[Revoke Token] button
```

---

## ⚠️ Important Notes

### ✅ What Works:
1. **Automatic sync:** Hourly cron job
2. **Manual sync:** "Sync Now" button
3. **Conflict prevention:** Overlapping bookings skipped
4. **Circular sync prevention:** Airbnb bookings excluded from export
5. **Guest info extraction:** Names, emails, phones parsed

### ❌ What Doesn't Work:
1. **Real-time sync:** Not instant (hourly for import, 1-2 hours for export)
2. **Price sync:** Airbnb prices not imported
3. **Cancellation notifications:** Manual cancellation on Airbnb won't notify MaRoom instantly

### 🔧 Maintenance:
- **Check sync logs:** Monthly review
- **Monitor conflicts:** If too many conflicts, check calendar setup
- **Token security:** Don't share export URLs publicly
- **Timezone:** Ensure `BOOKING_TIMEZONE` in `.env` matches your location

---

## 🎯 Summary

**Flow:**
```
Airbnb → (iCal URL) → MaRoom Import → Database → Website Availability
                                                          ↓
MaRoom Export ← Database ← Booking ← Customer Booking
     ↓
Airbnb Calendar (Blocked Dates)
```

**Key Points:**
- ✅ Two-way sync working
- ✅ No double booking
- ✅ Automatic (hourly)
- ✅ Manual sync available
- ✅ Conflict detection active

---

**Questions?** Check:
- `storage/logs/laravel.log` for errors
- Admin sync logs for import/export status
- Airbnb calendar for blocked dates verification
