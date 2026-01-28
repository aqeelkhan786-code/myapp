# PDF Overlay Position Setup Guide

This guide explains how to set up your PDF templates and adjust overlay positions so that booking data maps correctly to your Word document templates.

## Step 1: Convert Word Documents to PDFs

1. Open your Word documents:
   - `Rental Agreement.docx` (English)
   - `Mietvertrag (1).docx` (German)

2. Convert them to PDF:
   - In Word: File → Save As → Choose PDF format
   - Or use an online converter
   - Save as:
     - `rental-agreement-en.pdf` (English)
     - `rental-agreement-de.pdf` (German)

## Step 2: Place PDF Templates

1. Create the directory if it doesn't exist:
   ```
   storage/app/rental-templates/
   ```

2. Copy your PDF files to:
   - `storage/app/rental-templates/rental-agreement-en.pdf`
   - `storage/app/rental-templates/rental-agreement-de.pdf`

## Step 3: Test Current Positions

1. Go to any booking edit page: `/admin/bookings/{id}/edit`
2. Click the **"Debug PDF (EN)"** or **"Debug PDF (DE)"** button
3. A PDF will download showing:
   - **Red boxes** with labels showing where text fields are placed
   - **Colored boxes** showing signature positions
   - Each box shows the field name and coordinates (x, y)

## Step 4: Adjust Coordinates

1. Open the debug PDF and compare it with your original Word document
2. Note which fields are in the wrong positions
3. Edit `config/rental-pdf.php`
4. Adjust the `x` and `y` coordinates for each field:

```php
'overlay' => [
    'en' => [
        'date' => ['x' => 160, 'y' => 22, 'size' => 10],  // x = horizontal (mm), y = vertical (mm)
        'landlord_name' => ['x' => 25, 'y' => 42, 'size' => 10],
        // ... adjust as needed
    ],
],
```

### Coordinate System
- **A4 page size**: 210mm × 297mm
- **x**: Horizontal position from left edge (0 = left, 210 = right)
- **y**: Vertical position from top edge (0 = top, 297 = bottom)
- **size**: Font size in points (pt)

### Tips for Finding Coordinates
1. Use the debug PDF to see current positions
2. Measure the distance from the left edge and top edge in millimeters
3. In Adobe Reader or similar, you can use the measurement tool
4. Adjust coordinates in small increments (1-2mm) and regenerate

## Step 5: Test Again

1. After adjusting coordinates, regenerate the debug PDF
2. Compare with your original document
3. Repeat until all fields align correctly

## Available Fields

The following fields can be positioned:

- `date` - Document date
- `landlord_name` - Landlord/owner name
- `landlord_address` - Landlord address
- `tenant_name` - Tenant/guest name
- `tenant_address` - Tenant address
- `tenant_email` - Tenant email
- `room_name` - Room name
- `property_address` - Property address
- `start_at` - Check-in/start date
- `end_at` - Check-out/end date
- `rent` - Rental amount
- `owner_signature` - Owner signature image (x, y, w, h)
- `tenant_signature` - Tenant signature image (x, y, w, h)
- `tenant_signed_at` - Signature date

## Troubleshooting

### PDF template not found
- Make sure PDFs are in `storage/app/rental-templates/`
- Check file names match exactly: `rental-agreement-en.pdf` and `rental-agreement-de.pdf`
- Ensure files are readable (check permissions)

### Fields still not aligning
- Double-check coordinate system (mm from top-left)
- Remember: y increases downward
- Try adjusting in smaller increments
- Use the debug PDF to visualize positions

### Text is cut off
- Increase the width of the Cell() call in `RentalPdfTemplateService.php`
- Or adjust font size if text is too large

## Example: Adjusting a Field

If the "tenant_name" field appears 5mm too far right and 3mm too high:

**Before:**
```php
'tenant_name' => ['x' => 25, 'y' => 68, 'size' => 10],
```

**After:**
```php
'tenant_name' => ['x' => 20, 'y' => 71, 'size' => 10],  // x-5, y+3
```

Remember: Decreasing x moves left, increasing y moves down.
