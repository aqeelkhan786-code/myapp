# Convert English Rental Agreement to PDF

The German PDF template has been set up successfully! ✅

You still need to convert the English document:

## Quick Steps:

1. **Open** `C:\Users\Khizer Ali Khan\Downloads\Rental Agreement.docx` in Microsoft Word

2. **Save as PDF:**
   - Click **File** → **Save As**
   - Choose location: `C:\Users\Khizer Ali Khan\Downloads\`
   - File name: `Rental Agreement.pdf` (must match exactly)
   - File type: **PDF** (*.pdf)
   - Click **Save**

3. **Run the setup script again:**
   ```bash
   php setup-rental-pdf-templates.php
   ```

That's it! The script will automatically copy the PDF to the correct location.

## Alternative: Online Converter

If you don't have Word installed, you can use an online converter:
1. Go to https://www.ilovepdf.com/word-to-pdf or similar
2. Upload `Rental Agreement.docx`
3. Download the PDF
4. Save it as `Rental Agreement.pdf` in your Downloads folder
5. Run `php setup-rental-pdf-templates.php` again

## Verify Setup

After conversion, you can verify everything is set up by running:
```bash
php check-pdf-templates.php
```
