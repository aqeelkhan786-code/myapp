# Multi-Language Rental Agreement PDF Setup

This guide explains how the system automatically uses the correct PDF template based on the booking language.

## Overview

The system supports two PDF templates:
- **English**: `Rental Agreement .pdf` → `rental-agreement-en.pdf`
- **German**: `Mietvertrag (1).pdf` → `rental-agreement-de.pdf`

The system automatically selects the correct template based on the booking's language field.

## Automatic Language Detection

When a booking is created, the system:

1. **Reads the booking's language field** (set during booking creation)
2. **Converts it to a locale code**:
   - `"Deutsch"`, `"de"`, or `"German"` → `"de"` (German)
   - `"Englisch"`, `"en"`, `"eng"`, or `"English"` → `"en"` (English)
3. **Uses the corresponding PDF template**:
   - `"de"` → `rental-agreement-de.pdf`
   - `"en"` → `rental-agreement-en.pdf`

## Setup Instructions

### Step 1: Copy Both PDF Templates

Run the setup script to copy both templates:

```bash
php setup-3page-rental-pdf.php
```

This will:
- Copy `Rental Agreement .pdf` → `storage/app/rental-templates/rental-agreement-en.pdf`
- Copy `Mietvertrag (1).pdf` → `storage/app/rental-templates/rental-agreement-de.pdf`

### Step 2: Configure Overlay Positions

Edit `config/rental-pdf.php` and configure overlay positions for **both languages**:

```php
'overlay' => [
    'en' => [
        // English template overlay positions
        'date' => ['x' => 160, 'y' => 22, 'size' => 10, 'page' => 1],
        'landlord_name' => ['x' => 25, 'y' => 42, 'size' => 10, 'page' => 1],
        // ... more fields
        'owner_signature' => ['x' => 25, 'y' => 235, 'w' => 50, 'h' => 20, 'page' => 3],
        'tenant_signature' => ['x' => 115, 'y' => 235, 'w' => 50, 'h' => 20, 'page' => 3],
    ],
    'de' => [
        // German template overlay positions (may differ from English!)
        'date' => ['x' => 160, 'y' => 22, 'size' => 10, 'page' => 1],
        'landlord_name' => ['x' => 25, 'y' => 42, 'size' => 10, 'page' => 1],
        // ... more fields
        'owner_signature' => ['x' => 25, 'y' => 235, 'w' => 50, 'h' => 20, 'page' => 3],
        'tenant_signature' => ['x' => 115, 'y' => 235, 'w' => 50, 'h' => 20, 'page' => 3],
    ],
],
```

**Important**: The German template (`Mietvertrag`) may have different field positions than the English template. You need to configure both separately.

### Step 3: Test Both Templates

1. **Test English template**:
   - Create a booking with language = "English" or "en"
   - Go to `/admin/bookings/{id}/edit`
   - Click "Debug PDF (EN)" button
   - Verify field positions match yellow highlights

2. **Test German template**:
   - Create a booking with language = "Deutsch" or "de"
   - Go to `/admin/bookings/{id}/edit`
   - Click "Debug PDF (DE)" button
   - Verify field positions match yellow highlights

## How It Works

### Booking Creation Flow

1. **User selects language** during booking (e.g., "Deutsch" or "English")
2. **Booking is created** with `language` field set
3. **Document is created** with locale determined from booking language:
   ```php
   $locale = $booking->getLocaleFromLanguage(); // Returns 'de' or 'en'
   $document = $documentService->createDocument($booking, 'rental_agreement', $locale);
   ```

### PDF Generation Flow

1. **DocumentService** checks if PDF templates are enabled
2. **RentalPdfTemplateService** reads the document's locale:
   ```php
   $locale = $document->locale; // 'de' or 'en'
   $templatePath = config("rental-pdf.templates.{$locale}");
   // Returns: 'rental-templates/rental-agreement-en.pdf' or 'rental-templates/rental-agreement-de.pdf'
   ```
3. **Overlay positions** are loaded from config:
   ```php
   $overlay = config("rental-pdf.overlay.{$locale}");
   // Returns overlay positions for the specific language
   ```
4. **PDF is generated** with the correct template and overlay positions

## Language Mapping

The system maps booking language to locale as follows:

| Booking Language | Locale | Template Used |
|-----------------|--------|---------------|
| "Deutsch" | `de` | `rental-agreement-de.pdf` |
| "de" | `de` | `rental-agreement-de.pdf` |
| "German" | `de` | `rental-agreement-de.pdf` |
| "Englisch" | `en` | `rental-agreement-en.pdf` |
| "en" | `en` | `rental-agreement-en.pdf` |
| "eng" | `en` | `rental-agreement-en.pdf` |
| "English" | `en` | `rental-agreement-en.pdf` |
| (not set / unknown) | `de` | `rental-agreement-de.pdf` (default) |

## Troubleshooting

### Wrong template is being used

**Check the booking's language field:**
```php
$booking = Booking::find($id);
echo $booking->language; // Should show "Deutsch" or "English"
echo $booking->getLocaleFromLanguage(); // Should show "de" or "en"
```

**Check the document's locale:**
```php
$document = $booking->documents()->where('doc_type', 'rental_agreement')->first();
echo $document->locale; // Should match the booking language
```

### Template not found error

**Verify templates exist:**
```bash
# Check if files exist
ls storage/app/rental-templates/

# Should show:
# - rental-agreement-en.pdf
# - rental-agreement-de.pdf
```

**Check file permissions:**
- Files should be readable by the web server
- Check `storage/app/rental-templates/` directory permissions

### Fields not aligning correctly

**Remember**: English and German templates may have different layouts!

- Use debug PDF feature for each language separately
- Configure overlay positions independently for each language
- Field positions in `config/rental-pdf.php` under `'en'` and `'de'` can be completely different

## Example: Different Layouts

If your German template has a different layout than English:

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

## Summary

- ✅ System automatically uses correct template based on booking language
- ✅ Both templates are configured in `config/rental-pdf.php`
- ✅ Overlay positions are configured separately for each language
- ✅ Debug PDF feature works for both languages
- ✅ No code changes needed - just configure overlay positions!
