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
        $locale = $this->booking->getLocaleFromLanguage();
        app()->setLocale($locale);

        $docTypeKeys = [
            'rental_agreement' => 'rental_agreement',
            'landlord_confirmation' => 'landlord_confirmation',
            'rent_arrears' => 'rent_arrears_certificate',
        ];
        $key = $docTypeKeys[$this->document->doc_type] ?? 'rental_agreement';
        $docTypeName = __('booking.' . $key);

        return new Envelope(
            subject: $docTypeName . ' - ' . $this->booking->room->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $locale = $this->booking->getLocaleFromLanguage();
        app()->setLocale($locale);

        return new Content(
            view: 'emails.document-sent',
            with: [
                'document' => $this->document,
                'booking' => $this->booking,
                'locale' => $locale,
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
