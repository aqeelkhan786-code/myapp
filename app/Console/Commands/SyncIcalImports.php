<?php

namespace App\Console\Commands;

use App\Models\IcalFeed;
use App\Services\IcalService;
use Illuminate\Console\Command;

class SyncIcalImports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ical:sync-imports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync bookings from Airbnb iCal feeds';

    protected IcalService $icalService;

    /**
     * Create a new command instance.
     */
    public function __construct(IcalService $icalService)
    {
        parent::__construct();
        $this->icalService = $icalService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $feeds = IcalFeed::where('direction', 'import')
            ->where('active', true)
            ->get();
        
        $this->info("Found {$feeds->count()} active import feed(s)");
        
        foreach ($feeds as $feed) {
            $this->info("Syncing feed for room: {$feed->room->name}");
            
            $result = $this->icalService->importFromFeed($feed);
            
            if ($result['success']) {
                $this->info("  ✓ Imported: {$result['imported']}, Updated: {$result['updated']}");
                if (!empty($result['errors'])) {
                    $this->warn("  ⚠ Errors: " . count($result['errors']));
                }
            } else {
                $this->error("  ✗ Failed: {$result['message']}");
            }
        }
        
        $this->info('Sync completed!');
        
        return 0;
    }
}
