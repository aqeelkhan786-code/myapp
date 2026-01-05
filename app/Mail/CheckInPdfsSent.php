<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CheckInPdfsSent extends Mailable
{
    use Queueable, SerializesModels;

    public string $emailMessage;
    public array $pdfPaths;
    public string $emailSubject;

    /**
     * Create a new message instance.
     */
    public function __construct(string $message, array $pdfPaths, string $subject = null)
    {
        $this->emailMessage = $message;
        $this->pdfPaths = $pdfPaths;
        $this->emailSubject = $subject ?? (app()->getLocale() === 'de' ? 'Check-in Informationen - MaRoom' : 'Check-in Information - MaRoom');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.checkin-pdfs-sent',
            with: [
                'emailMessage' => $this->emailMessage,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        $attachments = [];
        
        foreach ($this->pdfPaths as $pdfPath) {
            if (Storage::exists($pdfPath)) {
                $pdfName = basename($pdfPath);
                $attachments[] = Attachment::fromStorage($pdfPath)
                    ->as($pdfName);
            }
        }
        
        return $attachments;
    }
}

