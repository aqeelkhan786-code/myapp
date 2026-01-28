<?php

/**
 * Helper script to check PDF template setup
 * Run: php check-pdf-templates.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$templateDir = storage_path('app/rental-templates');
$enTemplate = $templateDir . '/rental-agreement-en.pdf';
$deTemplate = $templateDir . '/rental-agreement-de.pdf';

echo "PDF Template Setup Check\n";
echo "========================\n\n";

// Check directory
if (!is_dir($templateDir)) {
    echo "❌ Template directory does not exist: {$templateDir}\n";
    echo "   Creating directory...\n";
    if (!mkdir($templateDir, 0755, true)) {
        echo "   ❌ Failed to create directory!\n";
        exit(1);
    }
    echo "   ✅ Directory created\n\n";
} else {
    echo "✅ Template directory exists: {$templateDir}\n\n";
}

// Check English template
if (is_file($enTemplate) && is_readable($enTemplate)) {
    $size = filesize($enTemplate);
    echo "✅ English template found: rental-agreement-en.pdf ({$size} bytes)\n";
} else {
    echo "❌ English template missing: rental-agreement-en.pdf\n";
    echo "   Expected location: {$enTemplate}\n";
    echo "   Action: Convert 'Rental Agreement.docx' to PDF and save as 'rental-agreement-en.pdf'\n\n";
}

// Check German template
if (is_file($deTemplate) && is_readable($deTemplate)) {
    $size = filesize($deTemplate);
    echo "✅ German template found: rental-agreement-de.pdf ({$size} bytes)\n";
} else {
    echo "❌ German template missing: rental-agreement-de.pdf\n";
    echo "   Expected location: {$deTemplate}\n";
    echo "   Action: Convert 'Mietvertrag (1).docx' to PDF and save as 'rental-agreement-de.pdf'\n\n";
}

// Check config
echo "\nConfiguration Check:\n";
echo "===================\n";
$useTemplates = config('rental-pdf.use_pdf_templates', true);
echo "Use PDF templates: " . ($useTemplates ? "✅ Enabled" : "❌ Disabled") . "\n";

$enOverlay = config('rental-pdf.overlay.en', []);
$deOverlay = config('rental-pdf.overlay.de', []);

echo "\nEnglish overlay fields: " . count($enOverlay) . "\n";
echo "German overlay fields: " . count($deOverlay) . "\n";

if (count($enOverlay) === 0 || count($deOverlay) === 0) {
    echo "⚠️  Warning: Overlay configuration may be incomplete\n";
}

echo "\nNext Steps:\n";
echo "===========\n";
echo "1. Convert your Word documents to PDFs (see PDF_OVERLAY_SETUP_GUIDE.md)\n";
echo "2. Place PDFs in: {$templateDir}\n";
echo "3. Go to /admin/bookings/{id}/edit and click 'Debug PDF' buttons\n";
echo "4. Adjust coordinates in config/rental-pdf.php as needed\n";
