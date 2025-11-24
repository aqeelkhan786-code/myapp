<?php

namespace App\Jobs;

use App\Models\IcalFeed;
use App\Services\IcalService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncIcalFeed implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public IcalFeed $feed;
    public ?int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(IcalFeed $feed, ?int $userId = null)
    {
        $this->feed = $feed;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(IcalService $icalService): void
    {
        $result = $icalService->importFromFeed($this->feed);
        
        // Log sync operation
        \App\Services\AuditService::log('ical_sync', [
            'feed_id' => $this->feed->id,
            'room_id' => $this->feed->room_id,
            'room_name' => $this->feed->room->name,
            'success' => $result['success'],
            'imported' => $result['imported'] ?? 0,
            'updated' => $result['updated'] ?? 0,
            'cancelled' => $result['cancelled'] ?? 0,
            'errors' => $result['errors'] ?? [],
        ], $this->userId);
    }
}
