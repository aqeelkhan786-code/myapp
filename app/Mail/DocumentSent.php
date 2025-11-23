<?php

namespace App\Mail;

use App\Models\Document;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DocumentSent extends Mailable
{
    use Queueable, SerializesModels;

    public Document $document;
    public Booking $booking;

    /**
     * Create a new message instance.
     */
    public function __construct(Document $document, Booking $booking)
    {
        $this->document = $document;
        $this->booking = $booking;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $docTypeNames = [
            'rental_agreement' => 'Rental Agreement',
            'landlord_confirmation' => 'Landlord Confirmation',
            'rent_arrears' => 'Certificate of Rent Arrears',
        ];
        
        return new Envelope(
            subject: $docTypeNames[$this->document->doc_type] . ' - ' . $this->booking->room->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.document-sent',
            with: [
                'document' => $this->document,
                'booking' => $this->booking,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        if (!$this->document->storage_path || !Storage::exists($this->document->storage_path)) {
            return [];
        }
        
        return [
            Attachment::fromStorage($this->document->storage_path)
                ->as($this->document->doc_type . '.pdf'),
        ];
    }
}
