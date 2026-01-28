# 🔧 Fix: Airbnb Bookings Not Showing (Rooms Free When Blocked on Airbnb)

## 🎯 Problem Identified

**Diagnostic Results:**
- ✅ Import feed exists for "Haus Rosa Zimmer 6" 
- ❌ **Import URL is EMPTY** (no URL configured)
- ❌ No Airbnb bookings imported (sync never ran)
- ❌ Other rooms don't have import feeds configured

---

## ✅ Solution Steps

### **Step 1: Add Airbnb Import URL**

1. **Login to Admin Panel:**
   ```
   https://yourdomain.com/login
   ```

2. **Navigate to Room Settings:**
   ```
   Admin → Rooms → "Haus Rosa Zimmer 6" → "Calendar Sync" tab
   ```

3. **Get Airbnb iCal Export URL:**
   - Go to **Airbnb Host Dashboard**
   - Select your listing
   - **Calendar** → **Sync calendars** → **Export calendar**
   - Copy the iCal URL (looks like):
     ```
     https://www.airbnb.com/calendar/ical/1234567890.ics?user_id=...
     ```

4. **Paste URL in MaRoom:**
   - **Import (Airbnb → MaRoom)** section
   - Paste the Airbnb URL in the "Airbnb iCal Import URL" field
   - ✅ Check **"Active"** checkbox
   - Click **"Save Import URL"**

### **Step 2: Trigger Immediate Sync**

1. **Click "Sync Now" button** (appears after saving URL)
2. **Wait 5-10 seconds** (queue processing)
3. **Refresh page** - should see "Last synced: [timestamp]"

### **Step 3: Verify Import**

**Check Admin → Bookings:**
- Filter by **Source: "Airbnb"**
- Should see bookings imported from Airbnb
- Guest names should be parsed (not "Airbnb Guest")

**Check Room Availability:**
- Go to customer-facing search page
- Select dates that are blocked on Airbnb
- Room should **NOT appear** in available rooms list

---

## 🔄 For Other Rooms

**Repeat Step 1 for each room that's listed on Airbnb:**

1. Admin → Rooms → Select room → Calendar Sync tab
2. Paste Airbnb iCal export URL for that specific room
3. Check "Active" checkbox
4. Save
5. Click "Sync Now"

**Important:** Each room needs its own Airbnb iCal URL (if listed separately on Airbnb).

---

## ⚠️ If "Sync Now" Doesn't Work

### **Check Queue Worker:**

```bash
# Start queue worker (if not running)
php artisan queue:work
```

**Or use scheduler:**
```bash
# Run scheduler manually
php artisan schedule:run
```

### **Check Logs:**

```bash
# View recent logs
tail -f storage/logs/laravel.log
```

**Look for:**
- ✅ "iCal sync completed" - Success
- ❌ "Failed to fetch iCal feed" - URL issue
- ❌ "Invalid event data" - Parse error

---

## 🧪 Quick Test

**After adding URL and syncing:**

1. **Run diagnostic again:**
   ```bash
   php diagnose-airbnb-sync.php
   ```

2. **Expected Results:**
   - ✅ Import URL: Should show actual URL (not empty)
   - ✅ Last Synced: Should show recent timestamp
   - ✅ Airbnb Bookings: Should show count > 0
   - ✅ Sync Results: Should show imported bookings

---

## 📋 Checklist

- [ ] Airbnb iCal export URL copied from Airbnb dashboard
- [ ] URL pasted in MaRoom admin panel
- [ ] "Active" checkbox checked
- [ ] "Save Import URL" clicked
- [ ] "Sync Now" button clicked
- [ ] Bookings appear in Admin → Bookings (filter: Airbnb)
- [ ] Rooms show as unavailable on customer site when dates blocked on Airbnb

---

## 🎯 Expected Result

**Before Fix:**
- ❌ Airbnb: Rooms blocked
- ❌ MaRoom: Rooms showing as available (WRONG!)

**After Fix:**
- ✅ Airbnb: Rooms blocked
- ✅ MaRoom: Rooms showing as unavailable (CORRECT!)

---

## 🆘 Still Not Working?

**Common Issues:**

1. **URL Invalid:**
   - Verify URL is accessible (open in browser)
   - Should download `.ics` file
   - Check for typos in URL

2. **Queue Worker Not Running:**
   - Start: `php artisan queue:work`
   - Or use scheduler: `php artisan schedule:run`

3. **Network Issues:**
   - Check if server can access Airbnb URLs
   - Check firewall/proxy settings

4. **Date Mismatch:**
   - Verify timezone settings (`BOOKING_TIMEZONE` in `.env`)
   - Check if dates in Airbnb match dates in MaRoom

---

**Need Help?** Check `storage/logs/laravel.log` for detailed error messages.
