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