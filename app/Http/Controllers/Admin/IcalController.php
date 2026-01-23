<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IcalFeed;
use App\Services\IcalService;
use Illuminate\Http\Request;

class IcalController extends Controller
{
    protected $icalService;

    public function __construct(IcalService $icalService)
    {
        $this->middleware('auth');
        $this->icalService = $icalService;
    }

    /**
     * Sync iCal imports manually
     */
    public function sync(Request $request)
    {
        $feeds = IcalFeed::where('direction', 'import')
            ->where('active', true)
            ->get();
        
        // Check if there are no active feeds
        if ($feeds->isEmpty()) {
            return redirect()->route('admin.bookings.index')
                ->with('warning', __('admin.no_active_ical_feeds'));
        }
        
        // Dispatch sync jobs to queue for each feed
        foreach ($feeds as $feed) {
            \App\Jobs\SyncIcalFeed::dispatch($feed, auth()->id());
        }
        
        return redirect()->route('admin.bookings.index')
            ->with('success', __('admin.ical_sync_queued', ['count' => count($feeds)]));
    }
}

