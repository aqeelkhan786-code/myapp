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
    public string $locale;

    /**
     * Create a new message instance.
     *
     * @param string $message Email body
     * @param array $pdfPaths Paths to PDF attachments
     * @param string|null $subject Email subject (defaults from locale)
     * @param string|null $locale 'de' or 'en' for header/footer; defaults to app locale (e.g. dashboard)
     */
    public function __construct(string $message, array $pdfPaths, ?string $subject = null, ?string $locale = null)
    {
        $this->emailMessage = $message;
        $this->pdfPaths = $pdfPaths;
        $this->locale = $locale ?? app()->getLocale();
        $this->emailSubject = $subject ?? ($this->locale === 'de' ? 'Check-in Informationen - MaRoom' : 'Check-in Information - MaRoom');
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
                'locale' => $this->locale,
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

