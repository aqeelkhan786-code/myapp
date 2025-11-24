<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->middleware('auth');
        $this->bookingService = $bookingService;
    }

    /**
     * Display calendar view of bookings
     */
    public function calendar(Request $request)
    {
        $view = $request->get('view', 'month'); // month, week, day
        $date = $request->get('date', now('Europe/Berlin')->format('Y-m-d'));
        
        $startDate = Carbon::parse($date)->setTimezone('Europe/Berlin');
        
        if ($view === 'month') {
            $startDate->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } elseif ($view === 'week') {
            $startDate->startOfWeek();
            $endDate = $startDate->copy()->endOfWeek();
        } else { // day
            $startDate->startOfDay();
            $endDate = $startDate->copy()->endOfDay();
        }
        
        $bookings = Booking::with(['room', 'room.property'])
            ->where('status', 'confirmed')
            ->whereBetween('start_at', [
                $startDate->utc(),
                $endDate->utc()
            ])
            ->get();
        
        $rooms = Room::with('property')->get();
        
        return view('admin.bookings.calendar', compact('bookings', 'rooms', 'view', 'date', 'startDate', 'endDate'));
    }

    /**
     * Display a listing of bookings
     */
    public function index(Request $request)
    {
        $query = Booking::with(['room', 'room.property']);
        
        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }
        
        // Filter by room
        if ($request->has('room_id') && $request->room_id !== '') {
            $query->where('room_id', $request->room_id);
        }
        
        // Filter by date range
        if ($request->has('date_from') && $request->date_from !== '') {
            $query->where('start_at', '>=', Carbon::parse($request->date_from)->setTimezone('Europe/Berlin')->startOfDay()->utc());
        }
        
        if ($request->has('date_to') && $request->date_to !== '') {
            $query->where('end_at', '<=', Carbon::parse($request->date_to)->setTimezone('Europe/Berlin')->endOfDay()->utc());
        }
        
        // Filter by source
        if ($request->has('source') && $request->source !== '') {
            $query->where('source', $request->source);
        }
        
        $bookings = $query->orderBy('start_at', 'desc')->paginate(20)->withQueryString();
        $rooms = \App\Models\Room::with('property')->get();
        
        return view('admin.bookings.index', compact('bookings', 'rooms'));
    }

    /**
     * Show the form for creating a new booking
     */
    public function create()
    {
        $rooms = Room::with('property')->get();
        return view('admin.bookings.create', compact('rooms'));
    }

    /**
     * Store a newly created booking
     */
    public function store(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'start_at' => 'required|date|after:yesterday',
            'end_at' => 'required|date|after:start_at',
            'guest_first_name' => 'required|string|max:255',
            'guest_last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:255',
            'status' => 'required|in:pending,confirmed,cancelled',
            'source' => 'required|in:manual,website,airbnb',
            'notes' => 'nullable|string',
        ]);

        $room = Room::findOrFail($request->room_id);
        $startAt = Carbon::parse($request->start_at)->setTimezone('Europe/Berlin')->startOfDay();
        $endAt = Carbon::parse($request->end_at)->setTimezone('Europe/Berlin')->startOfDay();

        // Check for conflicts
        $conflicts = $this->bookingService->getConflicts($room, $startAt, $endAt);
        $hasConflicts = !empty($conflicts);

        // Calculate total
        $totalAmount = $this->bookingService->calculateTotal($room, $startAt, $endAt);
        $isShortTerm = $room->short_term_allowed && $startAt->diffInDays($endAt) <= 30;

        $booking = Booking::create([
            'room_id' => $room->id,
            'start_at' => $startAt->utc(),
            'end_at' => $endAt->utc(),
            'source' => $request->source,
            'status' => $request->status,
            'guest_first_name' => $request->guest_first_name,
            'guest_last_name' => $request->guest_last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'notes' => $request->notes,
            'is_short_term' => $isShortTerm,
            'total_amount' => $totalAmount,
        ]);

        // Audit log: booking created
        \App\Services\AuditService::log('booking_created', [
            'room_id' => $room->id,
            'room_name' => $room->name,
            'guest_name' => "{$request->guest_first_name} {$request->guest_last_name}",
            'start_at' => $startAt->format('Y-m-d'),
            'end_at' => $endAt->format('Y-m-d'),
            'status' => $request->status,
            'source' => $request->source,
            'has_conflicts' => $hasConflicts,
            'conflicts' => $hasConflicts ? $conflicts->pluck('id')->toArray() : [],
        ], auth()->id(), $booking);

        if ($hasConflicts) {
            return redirect()->route('admin.bookings.index')
                ->with('warning', 'Booking created with conflicts. Please review.');
        }

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Booking created successfully.');
    }

    /**
     * Show the form for editing a booking
     */
    public function edit(Booking $booking)
    {
        $booking->load('room', 'room.property', 'documents', 'paymentLogs');
        $rooms = Room::with('property')->get();
        return view('admin.bookings.edit', compact('booking', 'rooms'));
    }

    /**
     * Mark payment as paid manually
     */
    public function markAsPaid(Request $request, Booking $booking)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0|max:' . ($booking->total_amount ?? 999999),
            'notes' => 'nullable|string',
        ]);

        $amount = $request->amount;
        $newPaidAmount = $booking->paid_amount + $amount;

        // Create payment log
        \App\Models\PaymentLog::create([
            'booking_id' => $booking->id,
            'amount' => $amount,
            'type' => 'manual_adjustment',
            'status' => 'completed',
            'payment_method' => 'manual',
            'notes' => $request->notes ?? 'Manual payment recorded by admin',
            'user_id' => auth()->id(),
        ]);

        // Update booking
        $booking->update([
            'paid_amount' => min($newPaidAmount, $booking->total_amount ?? 0),
            'payment_status' => $newPaidAmount >= ($booking->total_amount ?? 0) ? 'paid' : 'pending',
        ]);

        return back()->with('success', 'Payment recorded successfully.');
    }

    /**
     * Regenerate a document
     */
    public function regenerateDocument(Request $request, Booking $booking, $documentId)
    {
        $document = \App\Models\Document::where('booking_id', $booking->id)
            ->findOrFail($documentId);
        
        // Delete old PDF if exists
        if ($document->storage_path && \Storage::exists($document->storage_path)) {
            \Storage::delete($document->storage_path);
        }
        
        // Reset document
        $document->update([
            'storage_path' => '',
            'version' => $document->version + 1,
        ]);
        
        // Regenerate
        \App\Jobs\GenerateDocumentPdf::dispatch($document);
        
        return back()->with('success', 'Document regeneration queued. Please wait a moment and refresh.');
    }

    /**
     * Update the specified booking
     */
    public function update(Request $request, Booking $booking)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'guest_first_name' => 'required|string|max:255',
            'guest_last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:255',
            'status' => 'required|in:pending,confirmed,cancelled',
            'source' => 'required|in:manual,website,airbnb',
            'notes' => 'nullable|string',
            'override_conflict' => 'nullable|boolean',
        ]);

        $room = Room::findOrFail($request->room_id);
        $startAt = Carbon::parse($request->start_at)->setTimezone('Europe/Berlin')->startOfDay();
        $endAt = Carbon::parse($request->end_at)->setTimezone('Europe/Berlin')->startOfDay();

        // Check for conflicts (excluding current booking)
        $conflicts = $this->bookingService->getConflicts($room, $startAt, $endAt, $booking->id);
        $hasConflicts = !empty($conflicts);

        if ($hasConflicts && !$request->override_conflict) {
            return back()
                ->withErrors(['conflict' => 'There are conflicting bookings for these dates. Check "Override Conflict" to proceed.'])
                ->withInput()
                ->with('conflicts', $conflicts);
        }

        // Audit log: conflict override
        if ($hasConflicts && $request->override_conflict) {
            \App\Services\AuditService::log('conflict_override', [
                'booking_id' => $booking->id,
                'room_id' => $room->id,
                'room_name' => $room->name,
                'start_at' => $startAt->format('Y-m-d'),
                'end_at' => $endAt->format('Y-m-d'),
                'conflicting_booking_ids' => $conflicts->pluck('id')->toArray(),
            ], auth()->id(), $booking, 'Booking conflict was overridden by admin');
        }

        // Track changes for audit log
        $oldStatus = $booking->status;
        $oldRoomId = $booking->room_id;
        $oldStartAt = $booking->start_at;
        $oldEndAt = $booking->end_at;
        $changes = [
            'old' => [
                'room_id' => $oldRoomId,
                'start_at' => $oldStartAt->format('Y-m-d'),
                'end_at' => $oldEndAt->format('Y-m-d'),
                'status' => $oldStatus,
                'guest_first_name' => $booking->guest_first_name,
                'guest_last_name' => $booking->guest_last_name,
                'email' => $booking->email,
            ],
        ];

        // Recalculate total if dates or room changed
        if ($booking->room_id != $room->id || 
            $booking->start_at->notEqualTo($startAt->utc()) || 
            $booking->end_at->notEqualTo($endAt->utc())) {
            $totalAmount = $this->bookingService->calculateTotal($room, $startAt, $endAt);
            $isShortTerm = $room->short_term_allowed && $startAt->diffInDays($endAt) <= 30;
        } else {
            $totalAmount = $booking->total_amount;
            $isShortTerm = $booking->is_short_term;
        }

        $booking->update([
            'room_id' => $room->id,
            'start_at' => $startAt->utc(),
            'end_at' => $endAt->utc(),
            'source' => $request->source,
            'status' => $request->status,
            'guest_first_name' => $request->guest_first_name,
            'guest_last_name' => $request->guest_last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'notes' => $request->notes,
            'is_short_term' => $isShortTerm,
            'total_amount' => $totalAmount,
        ]);

        // Track new values
        $changes['new'] = [
            'room_id' => $room->id,
            'start_at' => $startAt->format('Y-m-d'),
            'end_at' => $endAt->format('Y-m-d'),
            'status' => $request->status,
            'guest_first_name' => $request->guest_first_name,
            'guest_last_name' => $request->guest_last_name,
            'email' => $request->email,
        ];

        // Audit log: booking updated
        \App\Services\AuditService::log('booking_updated', $changes, auth()->id(), $booking);

        // Audit log: status change (if status changed)
        if ($oldStatus !== $request->status) {
            \App\Services\AuditService::log('booking_status_changed', [
                'old_status' => $oldStatus,
                'new_status' => $request->status,
            ], auth()->id(), $booking);
        }

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Booking updated successfully.');
    }

    /**
     * Remove the specified booking
     */
    public function destroy(Booking $booking)
    {
        // Audit log: booking deleted (before deletion)
        \App\Services\AuditService::log('booking_deleted', [
            'booking_id' => $booking->id,
            'room_id' => $booking->room_id,
            'guest_name' => "{$booking->guest_first_name} {$booking->guest_last_name}",
            'start_at' => $booking->start_at->format('Y-m-d'),
            'end_at' => $booking->end_at->format('Y-m-d'),
            'status' => $booking->status,
        ], auth()->id(), $booking);

        $booking->delete();
        
        return redirect()->route('admin.bookings.index')
            ->with('success', 'Booking deleted successfully.');
    }
}

