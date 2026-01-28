# How to Adjust Dynamic Data Positions in PDF Documents

This guide explains how to change where dynamic data fields appear on your PDF rental agreement templates.

## Quick Start

1. **Edit the config file**: `config/rental-pdf.php`
2. **Adjust X and Y coordinates** for each field
3. **Test with Debug PDF** to see the changes
4. **Fine-tune** until positions match your yellow highlights

## Understanding Coordinates

The PDF uses a coordinate system measured in **millimeters (mm)**:

- **X coordinate**: Distance from the **left edge** of the page (0 = left, 210 = right for A4)
- **Y coordinate**: Distance from the **top edge** of the page (0 = top, 297 = bottom for A4)
- **Page**: Which page the field is on (1, 2, or 3)

### A4 Page Dimensions
- Width: 210mm
- Height: 297mm

## Step-by-Step Guide

### Step 1: Open the Config File

Edit `config/rental-pdf.php`:

```php
'overlay' => [
    'en' => [
        'date' => ['x' => 160, 'y' => 22, 'size' => 10, 'page' => 1],
        'landlord_name' => ['x' => 25, 'y' => 42, 'size' => 10, 'page' => 1],
        // ... more fields
    ],
    'de' => [
        // German positions
    ],
],
```

### Step 2: Identify the Field You Want to Move

Find the field name in the config. Available fields:

- `date` - Document date
- `landlord_name` - Landlord/owner name
- `landlord_address` - Landlord address
- `tenant_name` - Tenant/guest name
- `tenant_address` - Tenant address
- `tenant_email` - Tenant email
- `room_name` - Room/apartment name
- `property_address` - Property address
- `start_at` - Check-in/start date
- `end_at` - Check-out/end date
- `rent` - Rental amount
- `owner_signature` - Owner signature image
- `tenant_signature` - Tenant signature image
- `tenant_signed_at` - Signature date

### Step 3: Measure the Position in Your PDF

Open your original PDF template and measure where the yellow highlighted field is:

**Method 1: Using Adobe Reader**
1. Open PDF in Adobe Reader
2. Go to Tools → Measure
3. Click and drag to measure from left edge to field start (X)
4. Measure from top edge to field (Y)
5. Note the measurements in millimeters

**Method 2: Using a Ruler**
1. Print your PDF (or use a physical copy)
2. Use a millimeter ruler
3. Measure from left edge to field start (X)
4. Measure from top edge to field (Y)

**Method 3: Using PDF Coordinates**
1. Open PDF in a viewer that shows coordinates
2. Hover over the field location
3. Note the X and Y values shown

### Step 4: Update the Coordinates

Edit the field in `config/rental-pdf.php`:

**Before:**
```php
'date' => ['x' => 160, 'y' => 22, 'size' => 10, 'page' => 1],
```

**After (if you measured X=150, Y=25):**
```php
'date' => ['x' => 150, 'y' => 25, 'size' => 10, 'page' => 1],
```

### Step 5: Test with Debug PDF

1. Go to `/admin/bookings/{id}/edit`
2. Click **"Debug PDF (EN)"** or **"Debug PDF (DE)"** button
3. A PDF downloads showing:
   - **Red boxes** with labels showing where fields are placed
   - Each box shows: field name, coordinates, and page number
4. Compare with your original PDF
5. Adjust coordinates if needed

### Step 6: Fine-Tune

If the position is close but not exact:

- **Move left**: Decrease X value (e.g., `'x' => 148` instead of `'x' => 150`)
- **Move right**: Increase X value (e.g., `'x' => 152` instead of `'x' => 150`)
- **Move up**: Decrease Y value (e.g., `'y' => 23` instead of `'y' => 25`)
- **Move down**: Increase Y value (e.g., `'y' => 27` instead of `'y' => 25`)

Adjust in small increments (1-2mm) and regenerate the debug PDF.

## Examples

### Example 1: Move Date Field to Top Right

**Original:**
```php
'date' => ['x' => 160, 'y' => 22, 'size' => 10, 'page' => 1],
```

**Moved to top right corner:**
```php
'date' => ['x' => 180, 'y' => 15, 'size' => 10, 'page' => 1],
```

### Example 2: Move Rent Amount to Page 2

**Original (on page 1):**
```php
'rent' => ['x' => 25, 'y' => 142, 'size' => 10, 'page' => 1],
```

**Moved to page 2:**
```php
'rent' => ['x' => 25, 'y' => 50, 'size' => 10, 'page' => 2],
```

### Example 3: Adjust Signature Position

**Original:**
```php
'tenant_signature' => ['x' => 115, 'y' => 235, 'w' => 50, 'h' => 20, 'page' => 3],
```

**Moved and resized:**
```php
'tenant_signature' => ['x' => 120, 'y' => 240, 'w' => 60, 'h' => 25, 'page' => 3],
```

## Advanced: Styling Options

You can also adjust font, style, and color:

```php
'tenant_name' => [
    'x' => 25, 
    'y' => 68, 
    'size' => 12,           // Font size in points
    'page' => 1,
    'font' => 'Times',      // Font family: 'Helvetica', 'Times', 'Courier'
    'style' => 'B',         // Style: '' (normal), 'B' (bold), 'I' (italic), 'BI' (bold+italic)
    'color' => [0, 0, 0],   // RGB color: [R, G, B] each 0-255
    'width' => 80,          // Optional: width in mm for text wrapping
],
```

### Font Options
- `'font' => 'Helvetica'` - Sans-serif (default)
- `'font' => 'Times'` - Serif
- `'font' => 'Courier'` - Monospace

### Style Options
- `'style' => ''` - Normal text
- `'style' => 'B'` - Bold
- `'style' => 'I'` - Italic
- `'style' => 'BI'` - Bold + Italic

### Color Examples
- `'color' => [0, 0, 0]` - Black
- `'color' => [255, 0, 0]` - Red
- `'color' => [0, 0, 255]` - Blue
- `'color' => [128, 128, 128]` - Gray

## Multi-Page Fields

If your PDF has fields on different pages:

```php
'overlay' => [
    'en' => [
        // Page 1 fields
        'date' => ['x' => 160, 'y' => 22, 'size' => 10, 'page' => 1],
        'tenant_name' => ['x' => 25, 'y' => 68, 'size' => 10, 'page' => 1],
        
        // Page 2 fields
        'rent' => ['x' => 25, 'y' => 50, 'size' => 10, 'page' => 2],
        'start_at' => ['x' => 25, 'y' => 70, 'size' => 10, 'page' => 2],
        
        // Page 3 fields (signatures)
        'owner_signature' => ['x' => 25, 'y' => 235, 'w' => 50, 'h' => 20, 'page' => 3],
        'tenant_signature' => ['x' => 115, 'y' => 235, 'w' => 50, 'h' => 20, 'page' => 3],
    ],
],
```

## Different Positions for English vs German

If your English and German templates have different layouts:

```php
'overlay' => [
    'en' => [
        'date' => ['x' => 160, 'y' => 22, 'size' => 10, 'page' => 1], // Top right
        'rent' => ['x' => 25, 'y' => 142, 'size' => 10, 'page' => 1],  // Page 1
    ],
    'de' => [
        'date' => ['x' => 25, 'y' => 22, 'size' => 10, 'page' => 1],  // Top left (different!)
        'rent' => ['x' => 25, 'y' => 80, 'size' => 10, 'page' => 2],   // Page 2 (different!)
    ],
],
```

## Troubleshooting

### Field Not Appearing

1. **Check if field is configured**: Make sure the field exists in the config
2. **Check page number**: Verify the page number matches where the field should be
3. **Check coordinates**: Ensure coordinates are within page bounds (0-210mm for X, 0-297mm for Y)
4. **Check if data exists**: Some fields won't show if the data is empty

### Field in Wrong Position

1. **Use debug PDF**: Generate debug PDF to see current position
2. **Compare with original**: Open both PDFs side by side
3. **Adjust incrementally**: Change coordinates by 1-2mm at a time
4. **Regenerate**: Create new debug PDF after each change

### Text is Cut Off

1. **Increase width**: Add `'width' => 80` to allow text wrapping
2. **Reduce font size**: Decrease `'size'` value
3. **Check coordinates**: Ensure field isn't too close to page edge

### Field on Wrong Page

1. **Check page number**: Verify `'page' => N` matches the correct page
2. **Remember**: Page numbers start at 1 (not 0)

## Quick Reference: Coordinate Adjustments

| Direction | Change | Example |
|-----------|--------|---------|
| Move left | Decrease X | `'x' => 20` → `'x' => 18` |
| Move right | Increase X | `'x' => 20` → `'x' => 22` |
| Move up | Decrease Y | `'y' => 50` → `'y' => 48` |
| Move down | Increase Y | `'y' => 50` → `'y' => 52` |
| Smaller text | Decrease size | `'size' => 12` → `'size' => 10` |
| Larger text | Increase size | `'size' => 10` → `'size' => 12` |

## Workflow Summary

1. ✅ Open `config/rental-pdf.php`
2. ✅ Find the field you want to adjust
3. ✅ Measure position in your original PDF (X, Y in mm)
4. ✅ Update coordinates in config
5. ✅ Generate debug PDF to verify
6. ✅ Compare with original PDF
7. ✅ Fine-tune if needed
8. ✅ Repeat for other fields

## Tips

- **Start with one field at a time** - Get one perfect before moving to the next
- **Use debug PDF liberally** - It's the fastest way to see changes
- **Keep notes** - Write down measurements as you go
- **Test both languages** - English and German may need different positions
- **Save backups** - Keep a backup of working config before making changes
