<?php

namespace App\Jobs;

use App\Models\Document;
use App\Mail\DocumentSent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SendDocumentEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Document $document;
    public array $recipients;
    public bool $sendToOwner;

    /**
     * Create a new job instance.
     */
    public function __construct(Document $document, array $recipients, bool $sendToOwner = false)
    {
        $this->document = $document;
        $this->recipients = $recipients;
        $this->sendToOwner = $sendToOwner;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $booking = $this->document->booking;
        
        // Send to recipients
        foreach ($this->recipients as $email) {
            Mail::to($email)->send(new DocumentSent($this->document, $booking));
        }
        
        // Update sent timestamps
        if (in_array($booking->email, $this->recipients)) {
            $this->document->update(['sent_to_customer_at' => now()]);
        }
        
        if ($this->sendToOwner) {
            // Owner email would come from config
            $ownerEmail = config('mail.owner_email', 'owner@example.com');
            Mail::to($ownerEmail)->send(new DocumentSent($this->document, $booking));
            $this->document->update(['sent_to_owner_at' => now()]);
        }
    }
}
