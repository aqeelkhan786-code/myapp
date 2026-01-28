<?php

/**
 * Setup script to convert Word documents to PDF templates
 * Run: php setup-rental-pdf-templates.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$downloadsPath = 'C:\Users\Khizer Ali Khan\Downloads';
$enWordDoc = $downloadsPath . '\Rental Agreement.docx';
$deWordDoc = $downloadsPath . '\Mietvertrag (1).docx';

$templateDir = storage_path('app/rental-templates');
$enPdfTarget = $templateDir . '/rental-agreement-en.pdf';
$dePdfTarget = $templateDir . '/rental-agreement-de.pdf';

echo "Rental PDF Template Setup\n";
echo "========================\n\n";

// Check if Word documents exist
$enWordExists = is_file($enWordDoc);
$deWordExists = is_file($deWordDoc);

echo "Source Files Check:\n";
echo "-------------------\n";
if ($enWordExists) {
    echo "✅ Found: Rental Agreement.docx\n";
} else {
    echo "❌ Not found: Rental Agreement.docx\n";
    echo "   Expected: {$enWordDoc}\n";
}

if ($deWordExists) {
    echo "✅ Found: Mietvertrag (1).docx\n";
} else {
    echo "❌ Not found: Mietvertrag (1).docx\n";
    echo "   Expected: {$deWordDoc}\n";
}

// Check if PDFs already exist in Downloads (user may have converted them)
// Try multiple variations of the filename
$enPdfVariations = [
    $downloadsPath . '\Rental Agreement.pdf',
    $downloadsPath . '\Rental Agreement .pdf', // With space before .pdf
    $downloadsPath . '\Rental Agreement.docx.pdf',
];
$dePdfSource = $downloadsPath . '\Mietvertrag (1).pdf';

// Find the English PDF (try variations)
$enPdfSource = null;
foreach ($enPdfVariations as $variation) {
    if (is_file($variation)) {
        $enPdfSource = $variation;
        break;
    }
}

$enPdfExists = $enPdfSource !== null;
$dePdfExists = is_file($dePdfSource);

echo "\nConverted PDFs Check:\n";
echo "---------------------\n";
if ($enPdfExists) {
    echo "✅ Found: " . basename($enPdfSource) . " (ready to copy)\n";
} else {
    echo "ℹ️  Not found: Rental Agreement.pdf (needs conversion)\n";
}

if ($dePdfExists) {
    echo "✅ Found: Mietvertrag (1).pdf (ready to copy)\n";
} else {
    echo "ℹ️  Not found: Mietvertrag (1).pdf (needs conversion)\n";
}

// Create target directory
if (!is_dir($templateDir)) {
    echo "\n📁 Creating template directory: {$templateDir}\n";
    if (!mkdir($templateDir, 0755, true)) {
        echo "❌ Failed to create directory!\n";
        exit(1);
    }
    echo "✅ Directory created\n";
} else {
    echo "\n✅ Template directory exists: {$templateDir}\n";
}

// Remove existing PDFs if they exist
if (is_file($enPdfTarget)) {
    echo "\n🗑️  Removing existing: rental-agreement-en.pdf\n";
    unlink($enPdfTarget);
    echo "✅ Removed\n";
}

if (is_file($dePdfTarget)) {
    echo "🗑️  Removing existing: rental-agreement-de.pdf\n";
    unlink($dePdfTarget);
    echo "✅ Removed\n";
}

// Copy PDFs if they exist
echo "\n📋 Copying PDFs:\n";
echo "---------------\n";

$copied = false;

if ($dePdfExists) {
    if (copy($dePdfSource, $dePdfTarget)) {
        echo "✅ Copied: Mietvertrag (1).pdf → rental-agreement-de.pdf\n";
        $copied = true;
    } else {
        echo "❌ Failed to copy: Mietvertrag (1).pdf\n";
    }
}

if ($enPdfExists) {
    if (copy($enPdfSource, $enPdfTarget)) {
        echo "✅ Copied: Rental Agreement.pdf → rental-agreement-en.pdf\n";
        $copied = true;
    } else {
        echo "❌ Failed to copy: Rental Agreement.pdf\n";
    }
}

if (!$enPdfExists || !$dePdfExists) {
    // Need to convert Word to PDF
    echo "\n⚠️  PDFs not found in Downloads folder.\n";
    echo "\nTo convert Word documents to PDF:\n";
    echo "-----------------------------------\n";
    echo "Option 1: Manual Conversion (Recommended)\n";
    echo "1. Open 'Rental Agreement.docx' in Microsoft Word\n";
    echo "2. Go to File → Save As\n";
    echo "3. Choose 'PDF' as file type\n";
    echo "4. Save as 'Rental Agreement.pdf' in Downloads folder\n";
    echo "5. Repeat for 'Mietvertrag (1).docx' → 'Mietvertrag (1).pdf'\n";
    echo "6. Run this script again\n\n";
    
    echo "Option 2: Use PowerShell Script\n";
    echo "A PowerShell script will be created to automate conversion.\n";
    echo "Run: powershell -ExecutionPolicy Bypass -File convert-word-to-pdf.ps1\n\n";
    
    // Create PowerShell script for conversion
    $psScript = <<<'POWERSHELL'
# PowerShell script to convert Word documents to PDF
# Requires Microsoft Word to be installed

$downloadsPath = "C:\Users\Khizer Ali Khan\Downloads"
$wordApp = New-Object -ComObject Word.Application
$wordApp.Visible = $false

# Convert English document
$enDocPath = Join-Path $downloadsPath "Rental Agreement.docx"
$enPdfPath = Join-Path $downloadsPath "Rental Agreement.pdf"

if (Test-Path $enDocPath) {
    Write-Host "Converting: Rental Agreement.docx"
    $doc = $wordApp.Documents.Open($enDocPath)
    $doc.SaveAs([ref]$enPdfPath, [ref]17) # 17 = PDF format
    $doc.Close()
    Write-Host "✅ Created: Rental Agreement.pdf"
} else {
    Write-Host "❌ Not found: Rental Agreement.docx"
}

# Convert German document
$deDocPath = Join-Path $downloadsPath "Mietvertrag (1).docx"
$dePdfPath = Join-Path $downloadsPath "Mietvertrag (1).pdf"

if (Test-Path $deDocPath) {
    Write-Host "Converting: Mietvertrag (1).docx"
    $doc = $wordApp.Documents.Open($deDocPath)
    $doc.SaveAs([ref]$dePdfPath, [ref]17) # 17 = PDF format
    $doc.Close()
    Write-Host "✅ Created: Mietvertrag (1).pdf"
} else {
    Write-Host "❌ Not found: Mietvertrag (1).docx"
}

$wordApp.Quit()
[System.Runtime.Interopservices.Marshal]::ReleaseComObject($wordApp) | Out-Null

Write-Host "`n✅ Conversion complete! Run setup-rental-pdf-templates.php again to copy PDFs."
POWERSHELL;
    
    file_put_contents(__DIR__ . '/convert-word-to-pdf.ps1', $psScript);
    echo "✅ Created: convert-word-to-pdf.ps1\n";
}

// Final check
echo "\n📊 Final Status:\n";
echo "---------------\n";
if (is_file($enPdfTarget)) {
    $size = filesize($enPdfTarget);
    echo "✅ rental-agreement-en.pdf exists ({$size} bytes)\n";
} else {
    echo "❌ rental-agreement-en.pdf missing\n";
}

if (is_file($dePdfTarget)) {
    $size = filesize($dePdfTarget);
    echo "✅ rental-agreement-de.pdf exists ({$size} bytes)\n";
} else {
    echo "❌ rental-agreement-de.pdf missing\n";
}

if (is_file($enPdfTarget) && is_file($dePdfTarget)) {
    echo "\n🎉 Setup complete! PDF templates are ready.\n";
    echo "You can now test them using the Debug PDF buttons on booking edit pages.\n";
} else {
    echo "\n⚠️  Setup incomplete. Please convert Word documents to PDF and run this script again.\n";
}
