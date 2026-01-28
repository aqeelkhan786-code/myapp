<?php

/**
 * Setup script for 3-page Rental Agreement PDF templates (English & German)
 * 
 * This script helps you:
 * 1. Copy your PDF templates to the correct location
 * 2. Verify the templates are in place
 * 3. Provide instructions for setting up overlay positions
 */

$sourcePdfs = [
    'en' => 'C:\Users\Khizer Ali Khan\Downloads\Rental Agreement .pdf',
    'de' => 'C:\Users\Khizer Ali Khan\Downloads\Mietvertrag (1).pdf',
];

$targetDir = __DIR__ . '/storage/app/rental-templates';
$targetFiles = [
    'en' => $targetDir . '/rental-agreement-en.pdf',
    'de' => $targetDir . '/rental-agreement-de.pdf',
];

echo "=== Multi-Language 3-Page Rental Agreement PDF Setup ===\n\n";

// Step 1: Create target directory if it doesn't exist
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
    echo "✓ Created directory: {$targetDir}\n";
} else {
    echo "✓ Target directory exists: {$targetDir}\n";
}

$successCount = 0;
$errorCount = 0;

// Step 2: Copy both PDFs
foreach ($sourcePdfs as $locale => $sourcePdf) {
    $targetFile = $targetFiles[$locale];
    $localeName = $locale === 'en' ? 'English' : 'German';
    
    echo "\n--- Processing {$localeName} template ({$locale}) ---\n";
    
    // Check if source PDF exists
    if (!file_exists($sourcePdf)) {
        echo "❌ Source PDF not found: {$sourcePdf}\n";
        echo "   Please update the path in this script.\n";
        $errorCount++;
        continue;
    }
    
    echo "✓ Source PDF found: {$sourcePdf}\n";
    
    // Copy the PDF
    if (copy($sourcePdf, $targetFile)) {
        echo "✓ PDF copied successfully to: {$targetFile}\n";
        
        // Verify the PDF
        if (file_exists($targetFile)) {
            $fileSize = filesize($targetFile);
            echo "✓ PDF verified. File size: " . number_format($fileSize / 1024, 2) . " KB\n";
            $successCount++;
        } else {
            echo "❌ PDF verification failed.\n";
            $errorCount++;
        }
    } else {
        echo "❌ Failed to copy PDF. Please check permissions.\n";
        $errorCount++;
    }
}

echo "\n=== Setup Summary ===\n";
echo "✓ Successfully copied: {$successCount} template(s)\n";
if ($errorCount > 0) {
    echo "❌ Errors: {$errorCount} template(s) failed\n";
}

if ($successCount > 0) {
    echo "\n=== Setup Complete! ===\n\n";
    echo "Next steps:\n";
    echo "1. Open your PDF templates and identify the yellow highlighted fields\n";
    echo "   - English: Rental Agreement .pdf\n";
    echo "   - German: Mietvertrag (1).pdf\n";
    echo "2. Note which page each field is on (1, 2, or 3)\n";
    echo "3. Measure the position of each field in millimeters from:\n";
    echo "   - Left edge (x coordinate)\n";
    echo "   - Top edge (y coordinate)\n";
    echo "4. Edit config/rental-pdf.php and update the overlay positions for both languages:\n";
    echo "   - 'overlay' => ['en' => [...], 'de' => [...]]\n";
    echo "5. Use the debug PDF feature to verify positions:\n";
    echo "   - Go to /admin/bookings/{id}/edit\n";
    echo "   - Click 'Debug PDF (EN)' or 'Debug PDF (DE)' button\n";
    echo "   - Red boxes show where fields are placed\n";
    echo "\n";
    echo "Example overlay configuration:\n";
    echo "  'overlay' => [\n";
    echo "    'en' => [\n";
    echo "      'date' => ['x' => 160, 'y' => 22, 'size' => 10, 'page' => 1],\n";
    echo "      'rent' => ['x' => 25, 'y' => 50, 'size' => 10, 'page' => 2],\n";
    echo "      'owner_signature' => ['x' => 25, 'y' => 235, 'w' => 50, 'h' => 20, 'page' => 3],\n";
    echo "    ],\n";
    echo "    'de' => [\n";
    echo "      'date' => ['x' => 160, 'y' => 22, 'size' => 10, 'page' => 1],\n";
    echo "      'rent' => ['x' => 25, 'y' => 50, 'size' => 10, 'page' => 2],\n";
    echo "      'owner_signature' => ['x' => 25, 'y' => 235, 'w' => 50, 'h' => 20, 'page' => 3],\n";
    echo "    ],\n";
    echo "  ],\n";
    echo "\n";
    echo "Available fields:\n";
    echo "  - date, landlord_name, landlord_address\n";
    echo "  - tenant_name, tenant_address, tenant_email\n";
    echo "  - room_name, property_address\n";
    echo "  - start_at, end_at, rent\n";
    echo "  - owner_signature, tenant_signature, tenant_signed_at\n";
    echo "\n";
    echo "Language Selection:\n";
    echo "  - The system automatically uses the correct template based on booking language:\n";
    echo "    * English bookings → rental-agreement-en.pdf\n";
    echo "    * German bookings → rental-agreement-de.pdf\n";
    echo "  - Language is determined from the booking's 'language' field\n";
    echo "\n";
} else {
    echo "\n❌ Setup failed. Please check the errors above and try again.\n";
    exit(1);
}
