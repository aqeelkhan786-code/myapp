<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IcalFeed;
use App\Services\IcalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected $icalService;

    public function __construct(IcalService $icalService)
    {
        $this->icalService = $icalService;
    }

    /**
     * Webhook endpoint to trigger immediate iCal import sync
     * 
     * Optional query parameters:
     * - room_id: Sync specific room only
     * - token: Optional authentication token (can be configured in .env)
     */
    public function syncIcal(Request $request)
    {
        // Optional token authentication
        $expectedToken = config('app.webhook_token');
        if ($expectedToken && $request->get('token') !== $expectedToken) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $roomId = $request->get('room_id');
            
            if ($roomId) {
                // Sync specific room
                $feeds = IcalFeed::where('room_id', $roomId)
                    ->where('direction', 'import')
                    ->where('active', true)
                    ->get();
            } else {
                // Sync all active feeds
                $feeds = IcalFeed::where('direction', 'import')
                    ->where('active', true)
                    ->get();
            }

            if ($feeds->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active import feeds found',
                ], 404);
            }

            $results = [];
            foreach ($feeds as $feed) {
                // Dispatch sync job to queue
                \App\Jobs\SyncIcalFeed::dispatch($feed, null);
                
                $results[] = [
                    'room_id' => $feed->room_id,
                    'room_name' => $feed->room->name,
                    'status' => 'queued',
                ];
            }

            Log::info('iCal sync webhook triggered', [
                'feeds_count' => $feeds->count(),
                'room_id' => $roomId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'iCal sync queued successfully',
                'feeds_queued' => count($feeds),
                'results' => $results,
            ], 200);

        } catch (\Exception $e) {
            Log::error('iCal sync webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the webhook',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
