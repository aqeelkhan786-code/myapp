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
    protected function bookingTimezone(): string
    {
        // Keep behavior consistent with existing controllers, but make it configurable.
        return (string) config('booking.timezone', 'Europe/Berlin');
    }

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
            
            // Get all UIDs from the feed
            $feedUids = array_filter(array_column($events, 'uid'));
            
            // Get existing bookings for this room from this feed
            $existingBookings = Booking::where('room_id', $feed->room_id)
                ->where('source', 'airbnb')
                ->whereNotNull('external_uid')
                ->get();
            
            // Mark bookings as cancelled if their UID is no longer in the feed
            $cancelled = 0;
            foreach ($existingBookings as $booking) {
                if (!in_array($booking->external_uid, $feedUids)) {
                    $booking->update(['status' => 'cancelled']);
                    $cancelled++;
                }
            }
            
            $imported = 0;
            $updated = 0;
            $skippedConflicts = 0;
            $errors = [];
            
            foreach ($events as $event) {
                try {
                    $result = $this->importEvent($feed->room, $event);
                    if (!empty($result['skipped']) && $result['skipped'] === true) {
                        $skippedConflicts++;
                        continue;
                    }
                    if (!empty($result['created']) && $result['created'] === true) {
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
                    'cancelled' => $cancelled,
                    'skipped_conflicts' => $skippedConflicts,
                    'errors' => $errors,
                    'synced_at' => now()->toIso8601String(),
                ],
            ]);
            
            return [
                'success' => true,
                'imported' => $imported,
                'updated' => $updated,
                'cancelled' => $cancelled,
                'skipped_conflicts' => $skippedConflicts,
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
        // Normalize newlines and unfold folded lines (RFC 5545).
        $rawLines = preg_split("/\r\n|\n|\r/", $icalContent) ?: [];
        $lines = [];
        foreach ($rawLines as $rawLine) {
            if ($rawLine === '') {
                $lines[] = '';
                continue;
            }
            // Lines that start with space or tab are continuations.
            if (!empty($lines) && (str_starts_with($rawLine, ' ') || str_starts_with($rawLine, "\t"))) {
                $lines[count($lines) - 1] .= ltrim($rawLine);
            } else {
                $lines[] = $rawLine;
            }
        }
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
                } elseif (strpos($line, 'DESCRIPTION:') === 0) {
                    $currentEvent['description'] = substr($line, 12);
                } elseif (strpos($line, 'ORGANIZER') === 0) {
                    $currentEvent['organizer'] = $line;
                } elseif (strpos($line, 'STATUS:') === 0) {
                    $currentEvent['ical_status'] = substr($line, 7);
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
        // Handle DTSTART/DTEND like:
        // - DTSTART:20231122T120000Z
        // - DTSTART;VALUE=DATE:20231122
        // - DTSTART;TZID=Europe/Berlin:20231122T120000
        if (preg_match('/^(DTSTART|DTEND)[^:]*:(.+)$/', $line, $matches)) {
            $dateStr = trim($matches[2]);

            // If UTC 'Z' is present, keep it and parse as UTC.
            $isUtc = str_ends_with($dateStr, 'Z');
            if ($isUtc) {
                $dateStr = substr($dateStr, 0, -1);
            }

            $tz = $this->bookingTimezone();

            if (preg_match('/^\d{8}$/', $dateStr)) {
                // Date-only (all-day) event
                $dt = Carbon::createFromFormat('Ymd', $dateStr, $tz)->startOfDay();
                return $isUtc ? $dt->utc() : $dt;
            }

            if (preg_match('/^\d{8}T\d{6}$/', $dateStr)) {
                $dt = Carbon::createFromFormat('Ymd\THis', $dateStr, $tz);
                return $isUtc ? $dt->utc() : $dt;
            }

            // Fallback: try parsing via Carbon
            try {
                $dt = Carbon::parse($dateStr, $tz);
                return $isUtc ? $dt->utc() : $dt;
            } catch (\Exception $e) {
                return null;
            }
        }
        
        return null;
    }

    protected function parseGuestName(?string $summary): array
    {
        $summary = trim((string) $summary);
        if ($summary === '') {
            return ['first' => 'Airbnb', 'last' => 'Guest'];
        }

        // Airbnb often uses values like "Reserved", "Not available", etc.
        $lower = strtolower($summary);
        if (str_contains($lower, 'reserved') || str_contains($lower, 'not available') || str_contains($lower, 'blocked')) {
            return ['first' => 'Airbnb', 'last' => 'Guest'];
        }

        // Try to isolate the name portion (before separators).
        $namePart = preg_split('/\s+-\s+|\s+\|\s+|,/', $summary)[0] ?? $summary;
        $namePart = trim($namePart);

        // If it contains the word "Airbnb", it's not useful as a guest name.
        if (str_contains(strtolower($namePart), 'airbnb')) {
            return ['first' => 'Airbnb', 'last' => 'Guest'];
        }

        $parts = preg_split('/\s+/', $namePart) ?: [];
        if (count($parts) >= 2) {
            return ['first' => $parts[0], 'last' => implode(' ', array_slice($parts, 1))];
        }

        return ['first' => $namePart ?: 'Airbnb', 'last' => 'Guest'];
    }

    protected function extractEmail(?string $text): ?string
    {
        $text = (string) $text;
        if ($text === '') {
            return null;
        }
        if (preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $text, $m)) {
            return $m[0];
        }
        return null;
    }

    protected function extractPhone(?string $text): ?string
    {
        $text = (string) $text;
        if ($text === '') {
            return null;
        }
        // Very loose phone matcher (international-ish). Keeps import resilient.
        if (preg_match('/(\+?\d[\d\s().\-]{6,}\d)/', $text, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    protected function mapIcalStatusToBookingStatus(?string $icalStatus): string
    {
        $s = strtoupper(trim((string) $icalStatus));
        return match ($s) {
            'CANCELLED' => 'cancelled',
            'TENTATIVE' => 'pending',
            'CONFIRMED' => 'confirmed',
            default => 'confirmed',
        };
    }

    protected function hasNonAirbnbConflict(Room $room, Carbon $startAtUtc, Carbon $endAtUtc, ?int $excludeBookingId = null): bool
    {
        $q = Booking::where('room_id', $room->id)
            ->where('status', 'confirmed')
            ->where('source', '!=', 'airbnb')
            ->where(function ($q2) use ($startAtUtc, $endAtUtc) {
                $q2->where('start_at', '<', $endAtUtc)
                    ->where(function ($q3) use ($startAtUtc) {
                        $q3->where('end_at', '>', $startAtUtc)->orWhereNull('end_at');
                    });
            });

        if ($excludeBookingId) {
            $q->where('id', '!=', $excludeBookingId);
        }

        return $q->exists();
    }
    
    /**
     * Import a single event as a booking
     */
    protected function importEvent(Room $room, array $event): array
    {
        if (!isset($event['uid']) || !isset($event['start']) || !isset($event['end'])) {
            throw new \Exception('Invalid event data');
        }

        if (!$event['start'] instanceof Carbon || !$event['end'] instanceof Carbon) {
            throw new \Exception('Invalid event dates');
        }

        $startAt = $event['start']->utc();
        $endAt = $event['end']->utc();

        if ($endAt->lte($startAt)) {
            throw new \Exception('Invalid event range: end must be after start');
        }
        
        // Find existing booking by UID
        $booking = Booking::where('room_id', $room->id)
            ->where('external_uid', $event['uid'])
            ->first();

        // Conflict detection (avoid double-booking with non-Airbnb sources)
        $excludeId = $booking?->id;
        if ($this->hasNonAirbnbConflict($room, $startAt, $endAt, $excludeId)) {
            Log::warning('iCal import skipped due to conflict', [
                'room_id' => $room->id,
                'uid' => $event['uid'],
                'start_at' => $startAt->toIso8601String(),
                'end_at' => $endAt->toIso8601String(),
            ]);

            return ['created' => false, 'skipped' => true, 'reason' => 'conflict'];
        }
        
        if ($booking) {
            // Update existing booking
            $booking->update([
                'start_at' => $startAt,
                'end_at' => $endAt,
                'status' => $this->mapIcalStatusToBookingStatus($event['ical_status'] ?? null),
                'notes' => ($booking->notes ?? '') . "\nLast synced: " . now()->toDateTimeString(),
            ]);
            
            return ['created' => false, 'booking' => $booking];
        } else {
            $guest = $this->parseGuestName($event['summary'] ?? null);
            $description = $event['description'] ?? null;
            $organizerLine = $event['organizer'] ?? null;

            $email = $this->extractEmail($description) ?? $this->extractEmail($organizerLine);
            $phone = $this->extractPhone($description);
            
            // Create new booking
            $booking = Booking::create([
                'room_id' => $room->id,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'source' => 'airbnb',
                'status' => $this->mapIcalStatusToBookingStatus($event['ical_status'] ?? null),
                'guest_first_name' => $guest['first'],
                'guest_last_name' => $guest['last'],
                'email' => $email,
                'phone' => $phone,
                'notes' => 'Imported from Airbnb - UID: ' . $event['uid'],
                'external_uid' => $event['uid'],
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
            // Prevent circular sync: never export Airbnb-imported bookings back to Airbnb.
            ->where('source', '!=', 'airbnb')
            // iCal requires an end date; skip long-term bookings without end date.
            ->whereNotNull('end_at')
            ->get();

        $room->loadMissing('property');
        
        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//MaRoom//Booking System//EN\r\n";
        $ical .= "CALSCALE:GREGORIAN\r\n";
        $ical .= "METHOD:PUBLISH\r\n";
        
        foreach ($bookings as $booking) {
            $start = Carbon::parse($booking->start_at)->setTimezone($this->bookingTimezone());
            $end = Carbon::parse($booking->end_at)->setTimezone($this->bookingTimezone());
            
            $ical .= "BEGIN:VEVENT\r\n";
            $ical .= "UID:maroom-booking-{$booking->id}@maroom.local\r\n";
            $ical .= "DTSTART:" . $start->format('Ymd\THis') . "\r\n";
            $ical .= "DTEND:" . $end->format('Ymd\THis') . "\r\n";
            $ical .= "SUMMARY:" . $room->name . " - " . $booking->guest_full_name . "\r\n";
            $ical .= "DESCRIPTION:Booking #{$booking->id}\r\n";
            $ical .= "LOCATION:" . (($room->property?->address) ?? '') . "\r\n";
            $ical .= "STATUS:CONFIRMED\r\n";
            $ical .= "END:VEVENT\r\n";
        }
        
        $ical .= "END:VCALENDAR\r\n";
        
        return $ical;
    }
}

