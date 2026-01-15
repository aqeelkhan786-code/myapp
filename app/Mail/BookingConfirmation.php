<?php

namespace App\Mail;

use App\Models\Booking;
use App\Services\DocumentService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class BookingConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Set locale based on booking language
        $locale = $this->booking->getLocaleFromLanguage();
        app()->setLocale($locale);
        
        return new Envelope(
            subject: __('booking.email_subject') . ' - ' . $this->booking->room->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Set locale based on booking language
        $locale = $this->booking->getLocaleFromLanguage();
        app()->setLocale($locale);
        
        return new Content(
            view: 'emails.booking-confirmation',
            with: [
                'booking' => $this->booking,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        $attachments = [];
        
        // Get check-in PDF if available
        $documentService = app(DocumentService::class);
        $checkInPdfPath = $documentService->getCheckInPdfPath($this->booking->room);
        
        if ($checkInPdfPath && Storage::exists($checkInPdfPath)) {
            $attachments[] = Attachment::fromStorage($checkInPdfPath)
                ->as('Check-In-Information.pdf');
        }
        
        return $attachments;
    }
}
