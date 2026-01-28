<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Document;
use App\Models\Room;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentService
{
    /**
     * Generate PDF for a document
     */
    public function generatePdf(Document $document): string
    {
        try {
            $booking = $document->booking->load('room.property');

            // Long-term rental agreement: use user's PDF templates (Rental Agreement / Mietvertrag) when enabled
            if ($document->doc_type === 'rental_agreement' && config('rental-pdf.use_pdf_templates', true)) {
                $templateService = app(RentalPdfTemplateService::class);
                $path = $templateService->generateFromTemplate($document);
                if ($path !== null) {
                    return $path;
                }
            }
            
            $templateMap = [
                'rental_agreement' => [
                    'en' => 'pdf.rental_agreement_en',
                    'de' => 'pdf.rental_agreement_de',
                ],
                'landlord_confirmation' => [
                    'en' => 'pdf.landlord_confirmation_en',
                    'de' => 'pdf.landlord_confirmation_de',
                ],
                'rent_arrears' => [
                    'en' => 'pdf.rent_arrears_en',
                    'de' => 'pdf.rent_arrears_de',
                ],
            ];
            
            if (!isset($templateMap[$document->doc_type])) {
                throw new \Exception("Unknown document type: {$document->doc_type}");
            }
            
            $template = $templateMap[$document->doc_type][$document->locale] ?? $templateMap[$document->doc_type]['en'];
            
            // Check if template view exists
            if (!view()->exists($template)) {
                throw new \Exception("PDF template not found: {$template}");
            }

            $viewData = [
                'booking' => $booking,
                'document' => $document,
            ];

            // Pass owner (landlord) signature for rental agreements – used in EN/DE templates
            if ($document->doc_type === 'rental_agreement') {
                $ownerSignature = $this->getOwnerSignatureDataUrl();
                if ($ownerSignature) {
                    $viewData['landlordSignature'] = $ownerSignature;
                }
            }
            
            $pdf = Pdf::loadView($template, $viewData);
            
            // Generate filename
            $filename = Str::slug($document->doc_type . '-' . $booking->id . '-' . $document->version) . '.pdf';
            $path = 'documents/' . $filename;
            
            // Store PDF
            Storage::put($path, $pdf->output());
            
            // Update document
            $document->update([
                'storage_path' => $path,
                'generated_at' => now(),
            ]);
            
            return $path;
        } catch (\Exception $e) {
            \Log::error('PDF generation failed', [
                'document_id' => $document->id,
                'booking_id' => $document->booking_id,
                'doc_type' => $document->doc_type,
                'locale' => $document->locale,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Re-throw the exception so it can be handled by the caller
            throw $e;
        }
    }

    /**
     * Get owner (landlord) signature as base64 data URL for embedding in PDF.
     * Uses config('landlord.owner_signature_path') (default: images/owner-signature.png) under public.
     */
    protected function getOwnerSignatureDataUrl(): ?string
    {
        $path = config('landlord.owner_signature_path', 'images/owner-signature.png');
        $fullPath = public_path($path);
        if (!is_file($fullPath) || !is_readable($fullPath)) {
            return null;
        }
        $mime = mime_content_type($fullPath) ?: 'image/png';
        $data = file_get_contents($fullPath);
        if ($data === false) {
            return null;
        }
        return 'data:' . $mime . ';base64,' . base64_encode($data);
    }
    
    /**
     * Create or update document for a booking
     */
    public function createDocument(Booking $booking, string $docType, string $locale = 'en', array $signatureData = null): Document
    {
        $document = Document::where('booking_id', $booking->id)
            ->where('doc_type', $docType)
            ->first();
        
        if ($document) {
            // Increment version
            $document->update([
                'version' => $document->version + 1,
                'locale' => $locale,
                'signature_data' => $signatureData,
            ]);
        } else {
            $document = Document::create([
                'booking_id' => $booking->id,
                'doc_type' => $docType,
                'locale' => $locale,
                'storage_path' => '',
                'version' => 1,
                'signature_data' => $signatureData,
            ]);
        }
        
        return $document;
    }

    /**
     * Get the check-in PDF path for a room
     * First checks if room has a manually set path, otherwise auto-maps based on room/house name
     */
    public function getCheckInPdfPath(Room $room): ?string
    {
        // If room has a manually set check-in PDF path, use it
        if ($room->check_in_pdf_path) {
            $path = 'public/check-in-pdfs/' . $room->check_in_pdf_path;
            if (Storage::exists($path)) {
                return $path;
            }
        }

        // Auto-map based on room and house names
        $roomName = strtolower($room->name ?? '');
        $houseName = $room->house ? strtolower($room->house->name ?? '') : '';
        
        // Available PDF files
        $availablePdfs = [
            'Check in Haus Hoppe.pdf',
            'Check In Haus Rosa OG.pdf',
            'Check In Haus Rosa Room 7.pdf',
            'Check In L 1-3 (1).pdf',
            'Check In L 4-6.pdf',
            'Check In L 7-8.pdf',
            'Check In L 9-11.pdf',
        ];

        // Mapping logic based on room/house names
        $pdfMap = [];
        
        // Map Haus Hoppe
        if (str_contains($houseName, 'hoppe') || str_contains($roomName, 'hoppe')) {
            $pdfMap[] = 'Check in Haus Hoppe.pdf';
        }
        
        // Map Haus Rosa OG (ground floor)
        if (str_contains($houseName, 'rosa') && (str_contains($roomName, 'og') || str_contains($roomName, 'ground') || str_contains($roomName, 'eg'))) {
            $pdfMap[] = 'Check In Haus Rosa OG.pdf';
        }
        
        // Map Haus Rosa Room 7
        if (str_contains($houseName, 'rosa') && (str_contains($roomName, '7') || str_contains($roomName, 'room 7'))) {
            $pdfMap[] = 'Check In Haus Rosa Room 7.pdf';
        }
        
        // Map L rooms (L 1-3)
        if (preg_match('/\bL\s*[1-3]\b/i', $roomName) || preg_match('/\bL\s*0?[1-3]\b/i', $roomName)) {
            $pdfMap[] = 'Check In L 1-3 (1).pdf';
        }
        
        // Map L rooms (L 4-6)
        if (preg_match('/\bL\s*[4-6]\b/i', $roomName) || preg_match('/\bL\s*0?[4-6]\b/i', $roomName)) {
            $pdfMap[] = 'Check In L 4-6.pdf';
        }
        
        // Map L rooms (L 7-8)
        if (preg_match('/\bL\s*[7-8]\b/i', $roomName) || preg_match('/\bL\s*0?[7-8]\b/i', $roomName)) {
            $pdfMap[] = 'Check In L 7-8.pdf';
        }
        
        // Map L rooms (L 9-11)
        if (preg_match('/\bL\s*(9|10|11)\b/i', $roomName) || preg_match('/\bL\s*0?(9|10|11)\b/i', $roomName)) {
            $pdfMap[] = 'Check In L 9-11.pdf';
        }

        // Return the first matching PDF that exists
        foreach ($pdfMap as $pdfFile) {
            $path = 'public/check-in-pdfs/' . $pdfFile;
            if (Storage::exists($path)) {
                return $path;
            }
        }

        // If no specific match, try to find a general match based on house name
        if ($houseName) {
            foreach ($availablePdfs as $pdfFile) {
                $pdfNameLower = strtolower($pdfFile);
                if (str_contains($pdfNameLower, $houseName)) {
                    $path = 'public/check-in-pdfs/' . $pdfFile;
                    if (Storage::exists($path)) {
                        return $path;
                    }
                }
            }
        }

        return null;
    }
}

