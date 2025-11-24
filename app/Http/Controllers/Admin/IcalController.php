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
        
        // Dispatch sync jobs to queue for each feed
        foreach ($feeds as $feed) {
            \App\Jobs\SyncIcalFeed::dispatch($feed, auth()->id());
        }
        
        return redirect()->route('admin.bookings.index')
            ->with('success', 'iCal sync has been queued for ' . count($feeds) . ' feed(s). Please wait a moment and refresh to see results.');
    }
}

