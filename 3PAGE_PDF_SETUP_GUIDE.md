# 3-Page Rental Agreement PDF Setup Guide

This guide will help you set up your 3-page Rental Agreement PDF template with dynamic data overlay.

## Step 1: Copy Your PDF Template

Your original PDF is located at:
```
C:\Users\Khizer Ali Khan\Downloads\Rental Agreement .pdf
```

### Option A: Using the Setup Script
Run the setup script:
```bash
php setup-3page-rental-pdf.php
```

### Option B: Manual Copy
1. Create the directory if it doesn't exist:
   ```
   storage/app/rental-templates/
   ```

2. Copy your PDF to:
   ```
   storage/app/rental-templates/rental-agreement-en.pdf
   ```

## Step 2: Identify Yellow Highlighted Fields

Open your PDF template (`Rental Agreement .pdf`) and identify all fields marked with yellow highlights. These are the fields that need dynamic data.

Common fields to look for:
- **Date** - Document date
- **Landlord Name** - Owner/landlord name
- **Landlord Address** - Owner address
- **Tenant Name** - Guest/tenant name
- **Tenant Address** - Tenant address
- **Tenant Email** - Tenant email
- **Room Name** - Room/apartment name
- **Property Address** - Property address
- **Start Date** - Check-in/rental start date
- **End Date** - Check-out/rental end date
- **Rent Amount** - Monthly/total rent
- **Owner Signature** - Owner signature image
- **Tenant Signature** - Tenant signature image
- **Signature Date** - Date when tenant signed

## Step 3: Determine Page Numbers

For each yellow highlighted field, note which page it's on:
- **Page 1** - Usually header information, parties, property details
- **Page 2** - Usually rental terms, amounts, clauses
- **Page 3** - Usually signatures and final provisions

## Step 4: Measure Field Positions

For each yellow highlighted field, you need to measure its position in **millimeters**:

1. **X coordinate** - Distance from the **left edge** of the page
2. **Y coordinate** - Distance from the **top edge** of the page

### How to Measure:

**Using Adobe Reader:**
1. Open your PDF in Adobe Reader
2. Go to Tools → Measure
3. Click and drag to measure distances
4. Note the measurements in millimeters

**Using a Ruler:**
1. Print your PDF (or use a physical copy)
2. Use a millimeter ruler
3. Measure from left edge to the start of the field (X)
4. Measure from top edge to the field (Y)

**Using PDF Annotation Tools:**
1. Open PDF in a viewer that shows coordinates
2. Hover over the field location
3. Note the X and Y values shown

### Coordinate System:
- **A4 page size**: 210mm × 297mm
- **X = 0**: Left edge
- **X = 210**: Right edge
- **Y = 0**: Top edge
- **Y = 297**: Bottom edge

## Step 5: Update Configuration

Edit `config/rental-pdf.php` and update the overlay positions for the English template:

```php
'overlay' => [
    'en' => [
        // Page 1 fields
        'date' => ['x' => 160, 'y' => 22, 'size' => 10, 'page' => 1],
        'landlord_name' => ['x' => 25, 'y' => 42, 'size' => 10, 'page' => 1],
        'landlord_address' => ['x' => 25, 'y' => 48, 'size' => 9, 'page' => 1],
        'tenant_name' => ['x' => 25, 'y' => 68, 'size' => 10, 'page' => 1],
        'tenant_address' => ['x' => 25, 'y' => 74, 'size' => 9, 'page' => 1],
        'tenant_email' => ['x' => 25, 'y' => 80, 'size' => 9, 'page' => 1],
        'room_name' => ['x' => 25, 'y' => 98, 'size' => 10, 'page' => 1],
        'property_address' => ['x' => 25, 'y' => 104, 'size' => 9, 'page' => 1],
        
        // Page 2 fields (adjust based on your PDF)
        'start_at' => ['x' => 25, 'y' => 50, 'size' => 10, 'page' => 2],
        'end_at' => ['x' => 25, 'y' => 56, 'size' => 10, 'page' => 2],
        'rent' => ['x' => 25, 'y' => 70, 'size' => 10, 'page' => 2],
        
        // Page 3 fields (signatures typically on last page)
        'owner_signature' => ['x' => 25, 'y' => 235, 'w' => 50, 'h' => 20, 'page' => 3],
        'tenant_signature' => ['x' => 115, 'y' => 235, 'w' => 50, 'h' => 20, 'page' => 3],
        'tenant_signed_at' => ['x' => 115, 'y' => 258, 'size' => 9, 'page' => 3],
    ],
],
```

### Field Configuration Format:
```php
'field_name' => [
    'x' => 25,        // X position in mm (from left)
    'y' => 42,        // Y position in mm (from top)
    'size' => 10,     // Font size in points
    'page' => 1,      // Page number (1, 2, or 3)
],
```

### Signature Fields Format:
```php
'owner_signature' => [
    'x' => 25,        // X position in mm
    'y' => 235,       // Y position in mm
    'w' => 50,        // Width in mm
    'h' => 20,        // Height in mm
    'page' => 3,      // Page number
],
```

## Step 6: Test with Debug PDF

1. Go to any booking edit page: `/admin/bookings/{id}/edit`
2. Click the **"Debug PDF (EN)"** button
3. A PDF will download showing:
   - **Red boxes** with labels showing where text fields are placed
   - **Colored boxes** showing signature positions
   - Each box shows the field name, coordinates, and page number

## Step 7: Adjust Positions

1. Compare the debug PDF with your original PDF
2. Note which fields are in the wrong positions
3. Edit `config/rental-pdf.php` and adjust coordinates:
   - **Move left**: Decrease X value
   - **Move right**: Increase X value
   - **Move up**: Decrease Y value
   - **Move down**: Increase Y value
4. Regenerate the debug PDF
5. Repeat until all fields align correctly

## Tips for Accurate Positioning

1. **Start with one page at a time** - Get page 1 perfect, then move to page 2, then page 3
2. **Use small increments** - Adjust by 1-2mm at a time
3. **Check font size** - If text is too large/small, adjust the `size` parameter
4. **Account for text length** - Some fields may need wider cells if text is long
5. **Verify signatures** - Make sure signature images fit within the designated areas

## Available Fields

The following fields can be positioned:

| Field Name | Description | Example |
|------------|-------------|---------|
| `date` | Document date | Jan 27, 2026 |
| `landlord_name` | Landlord/owner name | Martin Assies |
| `landlord_address` | Landlord address | Street, City |
| `tenant_name` | Tenant/guest name | John Doe |
| `tenant_address` | Tenant address | Street, City |
| `tenant_email` | Tenant email | john@example.com |
| `room_name` | Room/apartment name | Room 101 |
| `property_address` | Property address | Street, City |
| `start_at` | Check-in/start date | Jan 27, 2026 |
| `end_at` | Check-out/end date | Feb 27, 2026 |
| `rent` | Rental amount | €1,200.00 |
| `owner_signature` | Owner signature image | (image) |
| `tenant_signature` | Tenant signature image | (image) |
| `tenant_signed_at` | Signature date | Jan 27, 2026 |

## Troubleshooting

### PDF template not found
- Make sure PDF is in `storage/app/rental-templates/rental-agreement-en.pdf`
- Check file permissions (should be readable)
- Verify the file name matches exactly

### Fields not appearing
- Check that the field name exists in the overlay configuration
- Verify the page number is correct (1, 2, or 3)
- Ensure coordinates are within page bounds (0-210mm for X, 0-297mm for Y)

### Fields in wrong position
- Double-check coordinate system (mm from top-left)
- Remember: Y increases downward
- Try adjusting in smaller increments (1-2mm)
- Use debug PDF to visualize positions

### Text is cut off
- Increase font size if text is too large
- Check if coordinates place text outside page bounds
- Verify the field has enough space in the original PDF

### Multi-page issues
- Ensure all pages are imported (check that template has 3 pages)
- Verify page numbers in overlay config match actual PDF pages
- Check that signatures are on the correct page

## Example: Setting Up a Field

Let's say you have a "Rent Amount" field on page 2 of your PDF:

1. **Measure position:**
   - X = 30mm from left edge
   - Y = 80mm from top edge

2. **Update config:**
   ```php
   'rent' => ['x' => 30, 'y' => 80, 'size' => 10, 'page' => 2],
   ```

3. **Test:**
   - Generate debug PDF
   - Check if red box aligns with yellow highlight
   - Adjust if needed

4. **Fine-tune:**
   - If too far right: `'x' => 28` (decrease by 2mm)
   - If too low: `'y' => 78` (decrease by 2mm)
   - Regenerate and check again

## Support

If you encounter issues:
1. Check the Laravel logs: `storage/logs/laravel.log`
2. Verify FPDI library is installed: `composer show setasign/fpdi`
3. Ensure PDF template is valid (can be opened in PDF viewer)
4. Check that all required fields are configured
