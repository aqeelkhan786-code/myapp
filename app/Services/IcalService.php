<?php

namespace App\Services;

use App\Models\IcalFeed;
use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IcalService
{
    /**
     * Import bookings from an iCal feed
     */
    public function importFromFeed(IcalFeed $feed): array
    {
        if (!$feed->url || !$feed->active) {
            return ['success' => false, 'message' => 'Feed is not configured or inactive'];
        }

        try {
            $response = Http::timeout(30)->get($feed->url);
            
            if (!$response->successful()) {
                throw new \Exception('Failed to fetch iCal feed: ' . $response->status());
            }
            
            $icalContent = $response->body();
            $events = $this->parseIcal($icalContent);
            
            $imported = 0;
            $updated = 0;
            $errors = [];
            
            foreach ($events as $event) {
                try {
                    $result = $this->importEvent($feed->room, $event);
                    if ($result['created']) {
                        $imported++;
                    } else {
                        $updated++;
                    }
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                    Log::error('iCal import error', ['event' => $event, 'error' => $e->getMessage()]);
                }
            }
            
            $feed->update([
                'last_synced_at' => now(),
                'sync_log' => [
                    'imported' => $imported,
                    'updated' => $updated,
                    'errors' => $errors,
                    'synced_at' => now()->toIso8601String(),
                ],
            ]);
            
            return [
                'success' => true,
                'imported' => $imported,
                'updated' => $updated,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            Log::error('iCal import failed', ['feed_id' => $feed->id, 'error' => $e->getMessage()]);
            
            $feed->update([
                'last_synced_at' => now(),
                'sync_log' => [
                    'error' => $e->getMessage(),
                    'synced_at' => now()->toIso8601String(),
                ],
            ]);
            
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Parse iCal content and extract events
     */
    protected function parseIcal(string $icalContent): array
    {
        $events = [];
        $lines = explode("\n", $icalContent);
        $currentEvent = null;
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if ($line === 'BEGIN:VEVENT') {
                $currentEvent = [];
            } elseif ($line === 'END:VEVENT') {
                if ($currentEvent) {
                    $events[] = $currentEvent;
                    $currentEvent = null;
                }
            } elseif ($currentEvent !== null) {
                if (strpos($line, 'UID:') === 0) {
                    $currentEvent['uid'] = substr($line, 4);
                } elseif (strpos($line, 'DTSTART') === 0) {
                    $currentEvent['start'] = $this->parseIcalDate($line);
                } elseif (strpos($line, 'DTEND') === 0) {
                    $currentEvent['end'] = $this->parseIcalDate($line);
                } elseif (strpos($line, 'SUMMARY:') === 0) {
                    $currentEvent['summary'] = substr($line, 8);
                }
            }
        }
        
        return $events;
    }
    
    /**
     * Parse iCal date string
     */
    protected function parseIcalDate(string $line): ?Carbon
    {
        // Handle both DTSTART:20231122T120000 and DTSTART;VALUE=DATE:20231122
        if (preg_match('/DTSTART[^:]*:(.+)/', $line, $matches)) {
            $dateStr = $matches[1];
            
            // Remove timezone if present
            $dateStr = preg_replace('/[TZ].*$/', '', $dateStr);
            
            if (strlen($dateStr) === 8) {
                // Date only format YYYYMMDD
                return Carbon::createFromFormat('Ymd', $dateStr)->setTimezone('Europe/Berlin')->startOfDay();
            } elseif (strlen($dateStr) >= 14) {
                // DateTime format YYYYMMDDHHmmss
                return Carbon::createFromFormat('YmdHis', substr($dateStr, 0, 14))->setTimezone('Europe/Berlin');
            }
        }
        
        return null;
    }
    
    /**
     * Import a single event as a booking
     */
    protected function importEvent(Room $room, array $event): array
    {
        if (!isset($event['uid']) || !isset($event['start']) || !isset($event['end'])) {
            throw new \Exception('Invalid event data');
        }
        
        $startAt = $event['start']->utc();
        $endAt = $event['end']->utc();
        
        // Find existing booking by UID (stored in notes or external identifier)
        $booking = Booking::where('room_id', $room->id)
            ->where('source', 'airbnb')
            ->where('start_at', $startAt)
            ->where('end_at', $endAt)
            ->first();
        
        if ($booking) {
            // Update existing booking
            $booking->update([
                'status' => 'confirmed',
            ]);
            
            return ['created' => false, 'booking' => $booking];
        } else {
            // Create new booking
            $booking = Booking::create([
                'room_id' => $room->id,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'source' => 'airbnb',
                'status' => 'confirmed',
                'guest_first_name' => 'Airbnb',
                'guest_last_name' => 'Guest',
                'email' => 'airbnb@example.com',
                'notes' => 'Imported from Airbnb - UID: ' . $event['uid'],
            ]);
            
            return ['created' => true, 'booking' => $booking];
        }
    }
    
    /**
     * Generate iCal export for a room
     */
    public function generateExport(Room $room): string
    {
        $bookings = Booking::where('room_id', $room->id)
            ->where('status', 'confirmed')
            ->get();
        
        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//MaRoom//Booking System//EN\r\n";
        $ical .= "CALSCALE:GREGORIAN\r\n";
        $ical .= "METHOD:PUBLISH\r\n";
        
        foreach ($bookings as $booking) {
            $start = Carbon::parse($booking->start_at)->setTimezone('Europe/Berlin');
            $end = Carbon::parse($booking->end_at)->setTimezone('Europe/Berlin');
            
            $ical .= "BEGIN:VEVENT\r\n";
            $ical .= "UID:maroom-booking-{$booking->id}@maroom.local\r\n";
            $ical .= "DTSTART:" . $start->format('Ymd\THis') . "\r\n";
            $ical .= "DTEND:" . $end->format('Ymd\THis') . "\r\n";
            $ical .= "SUMMARY:" . $room->name . " - " . $booking->guest_full_name . "\r\n";
            $ical .= "DESCRIPTION:Booking #{$booking->id}\r\n";
            $ical .= "LOCATION:" . $room->property->address . "\r\n";
            $ical .= "STATUS:CONFIRMED\r\n";
            $ical .= "END:VEVENT\r\n";
        }
        
        $ical .= "END:VCALENDAR\r\n";
        
        return $ical;
    }
}

