<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Document;
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
            
            $pdf = Pdf::loadView($template, [
                'booking' => $booking,
                'document' => $document,
            ]);
            
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
}

