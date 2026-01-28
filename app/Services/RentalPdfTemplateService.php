<?php

namespace App\Services;

use App\Models\Document;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RentalPdfTemplateService
{
    /**
     * Generate rental agreement PDF from user's template (Rental Agreement (1).pdf / Mietvertrag (1).pdf).
     * Overlays all booking data + owner + tenant signature. Returns storage path or null on failure.
     * 
     * @param bool $debug If true, draws visible markers showing where each field is placed
     */
    public function generateFromTemplate(Document $document, bool $debug = false): ?string
    {
        if (!class_exists(\setasign\Fpdi\Fpdi::class)) {
            return null;
        }

        if (!config('rental-pdf.use_pdf_templates', true)) {
            return null;
        }

        $booking = $document->booking->load('room.property');
        $locale = $document->locale === 'de' ? 'de' : 'en';
        $templateRel = config("rental-pdf.templates.{$locale}", "rental-templates/rental-agreement-{$locale}.pdf");
        $templatePath = storage_path('app/' . $templateRel);

        if (!is_file($templatePath) || !is_readable($templatePath)) {
            \Log::warning('Rental PDF template not found', ['path' => $templatePath, 'locale' => $locale]);
            return null;
        }

        $overlay = config("rental-pdf.overlay.{$locale}", config('rental-pdf.overlay.en', []));

        try {
            $pdf = new \setasign\Fpdi\Fpdi();
            
            // Get total number of pages in template (setSourceFile returns page count)
            $pageCount = $pdf->setSourceFile($templatePath);
            
            // Prepare overlay data
            $overlayData = $this->prepareOverlayData($booking, $document, $overlay, $locale);
            
            // Import and add pages from template, writing overlays as we go
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $tplIdx = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($tplIdx);
                $pdf->AddPage($size['orientation'] ?? 'P', [$size['width'], $size['height']]);
                $pdf->useImportedPage($tplIdx, 0, 0, $size['width'] ?? 210, $size['height'] ?? 297);
                
                // Write overlays for this page
                $this->writePageOverlays($pdf, $overlayData, $pageNo, $debug);
                
                // Handle signatures on their specified pages
                if ($pageNo == ($overlay['owner_signature']['page'] ?? $pageCount)) {
                    $ownerPath = public_path(config('landlord.owner_signature_path', 'images/owner-signature.png'));
                    if (is_file($ownerPath) && isset($overlay['owner_signature'])) {
                        $o = $overlay['owner_signature'];
                        $pdf->Image($ownerPath, $o['x'], $o['y'], $o['w'] ?? 50, $o['h'] ?? 20);
                    }
                }
                
                if ($pageNo == ($overlay['tenant_signature']['page'] ?? $pageCount)) {
                    // Get tenant signature - handle both array and JSON string
                    $signatureData = $document->signature_data;
                    if (is_string($signatureData)) {
                        $signatureData = json_decode($signatureData, true);
                    }
                    $tenantSig = $signatureData['signature'] ?? null;
                    
                    if ($tenantSig && isset($overlay['tenant_signature'])) {
                        $tmp = $this->signatureDataUrlToTempFile($tenantSig);
                        if ($tmp) {
                            try {
                                $t = $overlay['tenant_signature'];
                                $pdf->Image($tmp, $t['x'], $t['y'], $t['w'] ?? 50, $t['h'] ?? 20);
                            } catch (\Exception $e) {
                                \Log::warning('Failed to add tenant signature to PDF', [
                                    'document_id' => $document->id,
                                    'error' => $e->getMessage(),
                                ]);
                            } finally {
                                @unlink($tmp);
                            }
                        } else {
                            \Log::warning('Failed to convert tenant signature to temp file', [
                                'document_id' => $document->id,
                                'has_signature' => !empty($tenantSig),
                            ]);
                        }
                    } else {
                        \Log::debug('Tenant signature not found or overlay not configured', [
                            'document_id' => $document->id,
                            'has_signature_data' => !empty($signatureData),
                            'has_signature_key' => isset($signatureData['signature']),
                            'has_overlay_config' => isset($overlay['tenant_signature']),
                        ]);
                    }
                }
            }

            $filename = Str::slug('rental_agreement-' . $booking->id . '-' . $document->version) . '.pdf';
            $path = 'documents/' . $filename;
            Storage::makeDirectory('documents');
            $pdf->Output('F', Storage::path($path));

            $document->update([
                'storage_path' => $path,
                'generated_at' => now(),
            ]);

            return $path;
        } catch (\Throwable $e) {
            \Log::error('Rental PDF template overlay failed', [
                'document_id' => $document->id,
                'locale' => $locale,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Generate a debug PDF with visible markers showing overlay positions
     */
    public function generateDebugPdf(Document $document, string $locale): ?string
    {
        if (!class_exists(\setasign\Fpdi\Fpdi::class)) {
            return null;
        }

        $templateRel = config("rental-pdf.templates.{$locale}", "rental-templates/rental-agreement-{$locale}.pdf");
        $templatePath = storage_path('app/' . $templateRel);

        if (!is_file($templatePath) || !is_readable($templatePath)) {
            \Log::warning('Rental PDF template not found for debug', ['path' => $templatePath, 'locale' => $locale]);
            return null;
        }

        $overlay = config("rental-pdf.overlay.{$locale}", config('rental-pdf.overlay.en', []));
        $booking = $document->booking->load('room.property');

        try {
            $pdf = new \setasign\Fpdi\Fpdi();
            
            // Get total number of pages in template (setSourceFile returns page count)
            $pageCount = $pdf->setSourceFile($templatePath);
            
            // Prepare overlay data
            $overlayData = $this->prepareOverlayData($booking, $document, $overlay, $locale);
            
            // Import and add pages from template, writing overlays as we go
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $tplIdx = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($tplIdx);
                $pdf->AddPage($size['orientation'] ?? 'P', [$size['width'], $size['height']]);
                $pdf->useImportedPage($tplIdx, 0, 0, $size['width'] ?? 210, $size['height'] ?? 297);
                
                // Write overlays for this page with debug markers
                $this->writePageOverlays($pdf, $overlayData, $pageNo, true);
                
                // Draw debug markers for signatures
                if ($pageNo == ($overlay['owner_signature']['page'] ?? $pageCount)) {
                    $o = $overlay['owner_signature'];
                    $pdf->SetDrawColor(255, 0, 0);
                    $pdf->SetFillColor(255, 200, 200);
                    $pdf->Rect($o['x'], $o['y'], $o['w'] ?? 50, $o['h'] ?? 20, 'FD');
                    $pdf->SetFont('Helvetica', 'B', 8);
                    $pdf->SetTextColor(0, 0, 0);
                    $pdf->SetXY($o['x'], $o['y'] - 2);
                    $pdf->Cell($o['w'] ?? 50, 3, 'owner_signature (' . $o['x'] . ',' . $o['y'] . ') [Page ' . $pageNo . ']', 0, 0, 'L');
                }

                if ($pageNo == ($overlay['tenant_signature']['page'] ?? $pageCount)) {
                    $t = $overlay['tenant_signature'];
                    $pdf->SetDrawColor(0, 0, 255);
                    $pdf->SetFillColor(200, 200, 255);
                    $pdf->Rect($t['x'], $t['y'], $t['w'] ?? 50, $t['h'] ?? 20, 'FD');
                    $pdf->SetFont('Helvetica', 'B', 8);
                    $pdf->SetTextColor(0, 0, 0);
                    $pdf->SetXY($t['x'], $t['y'] - 2);
                    $pdf->Cell($t['w'] ?? 50, 3, 'tenant_signature (' . $t['x'] . ',' . $t['y'] . ') [Page ' . $pageNo . ']', 0, 0, 'L');
                }
            }

            $filename = 'pdf-overlay-debug-' . $locale . '-' . time() . '.pdf';
            $path = 'documents/' . $filename;
            Storage::makeDirectory('documents');
            $pdf->Output('F', Storage::path($path));

            return $path;
        } catch (\Throwable $e) {
            \Log::error('Debug PDF generation failed', [
                'document_id' => $document->id,
                'locale' => $locale,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Prepare all overlay data once
     */
    protected function prepareOverlayData($booking, $document, array $overlay, string $locale): array
    {
        $fmt = $locale === 'de' ? 'd.m.Y' : 'M d, Y';
        $data = [];
        
        $data['date'] = ['text' => Carbon::now()->format($fmt), 'config' => $overlay['date'] ?? null];
        $data['landlord_name'] = ['text' => config('landlord.name', 'Martin Assies'), 'config' => $overlay['landlord_name'] ?? null];
        
        $addr = array_filter([
            config('landlord.address'),
            trim((config('landlord.postal_code') ?? '') . ' ' . (config('landlord.city') ?? '')),
        ]);
        $data['landlord_address'] = ['text' => implode(', ', $addr), 'config' => $overlay['landlord_address'] ?? null];
        
        $data['tenant_name'] = ['text' => trim($booking->guest_first_name . ' ' . $booking->guest_last_name), 'config' => $overlay['tenant_name'] ?? null];
        
        $tenantAddr = array_filter([
            $booking->renter_address,
            trim(($booking->renter_postal_code ?? '') . ' ' . ($booking->renter_city ?? '')),
        ]);
        $data['tenant_address'] = ['text' => implode(', ', $tenantAddr), 'config' => $overlay['tenant_address'] ?? null];
        $data['tenant_email'] = ['text' => $booking->email ?? '', 'config' => $overlay['tenant_email'] ?? null];
        
        $room = $booking->room;
        $data['room_name'] = ['text' => $room->name ?? '', 'config' => $overlay['room_name'] ?? null];
        $prop = $room->property;
        $propAddr = $prop ? trim(($prop->address ?? '') . ', ' . ($prop->postal_code ?? '') . ' ' . ($prop->city ?? '')) : '';
        $data['property_address'] = ['text' => $propAddr, 'config' => $overlay['property_address'] ?? null];
        
        $data['start_at'] = ['text' => $booking->start_at ? Carbon::parse($booking->start_at)->format($fmt) : '', 'config' => $overlay['start_at'] ?? null];
        $data['end_at'] = ['text' => $booking->end_at ? Carbon::parse($booking->end_at)->format($fmt) : ($locale === 'de' ? 'unbefristet' : 'indefinite'), 'config' => $overlay['end_at'] ?? null];
        
        $data['rent'] = ['text' => '€' . number_format((float) $booking->total_amount, 2, ',', '.'), 'config' => $overlay['rent'] ?? null];
        
        if ($document->signed_at && isset($overlay['tenant_signed_at'])) {
            $data['tenant_signed_at'] = ['text' => Carbon::parse($document->signed_at)->format($fmt), 'config' => $overlay['tenant_signed_at']];
        }
        
        return $data;
    }
    
    /**
     * Write overlays for a specific page
     */
    protected function writePageOverlays($pdf, array $overlayData, int $pageNo, bool $debug = false): void
    {
        foreach ($overlayData as $key => $item) {
            if (!$item['config'] || $item['text'] === '') {
                continue;
            }
            
            $c = $item['config'];
            $fieldPage = $c['page'] ?? 1;
            
            // Only write if this field belongs to current page
            if ($fieldPage != $pageNo) {
                continue;
            }
            
            $size = $c['size'] ?? 10;
            $font = $c['font'] ?? 'Helvetica';
            $style = $c['style'] ?? '';
            
            $pdf->SetFont($font, $style, $size);
            $pdf->SetTextColor($c['color'][0] ?? 0, $c['color'][1] ?? 0, $c['color'][2] ?? 0);
            $pdf->SetXY($c['x'], $c['y']);
            
            // Debug mode: draw a red box and label to show position
            if ($debug) {
                $pdf->SetDrawColor(255, 0, 0);
                $pdf->SetFillColor(255, 200, 200);
                $pdf->Rect($c['x'], $c['y'], 50, 5, 'FD');
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFont('Helvetica', 'B', 8);
                $pdf->SetXY($c['x'], $c['y'] - 2);
                $pdf->Cell(50, 3, $key . ' (' . $c['x'] . ',' . $c['y'] . ') [Page ' . $pageNo . ']', 0, 0, 'L');
                $pdf->SetXY($c['x'], $c['y']);
                $pdf->SetFont($font, $style, $size);
                $pdf->SetTextColor($c['color'][0] ?? 0, $c['color'][1] ?? 0, $c['color'][2] ?? 0);
            }
            
            // Handle multi-line text if needed
            $width = $c['width'] ?? 0;
            if ($width > 0) {
                $pdf->MultiCell($width, 5, $item['text'], 0, 'L');
            } else {
                $pdf->Cell(0, 5, $item['text'], 0, 1, 'L');
            }
        }
    }

    protected function signatureDataUrlToTempFile(?string $dataUrl): ?string
    {
        if (!$dataUrl || !preg_match('#^data:image/(\w+);base64,(.+)$#', $dataUrl, $m)) {
            return null;
        }
        $ext = $m[1] === 'jpeg' ? 'jpg' : $m[1];
        $tmp = tempnam(sys_get_temp_dir(), 'sig_') . '.' . $ext;
        $data = base64_decode($m[2], true);
        if ($data === false || file_put_contents($tmp, $data) === false) {
            return null;
        }
        return $tmp;
    }
}
