<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Services\BookingService;
use App\Mail\BookingConfirmation;
use App\Mail\CheckInPdfsSent;
use App\Mail\DocumentSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
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
     * Display table-style calendar view (like PDF format)
     */
    public function calendarTable(Request $request)
    {
        // Get start month from request or default to current month
        $startMonth = $request->get('month', now('Europe/Berlin')->format('Y-m'));
        $startDate = Carbon::parse($startMonth . '-01')->setTimezone('Europe/Berlin');
        
        // Number of months to display (default 5 months like PDF)
        $monthsCount = $request->get('months', 5);
        
        // Set Carbon locale for month names
        $locale = app()->getLocale();
        Carbon::setLocale($locale);
        
        // Generate months array
        $months = [];
        for ($i = 0; $i < $monthsCount; $i++) {
            $monthDate = $startDate->copy()->addMonths($i);
            $months[] = [
                'date' => $monthDate,
                'name' => $monthDate->translatedFormat('F'), // Use translatedFormat for locale-aware month names
                'year' => $monthDate->format('Y'),
                'start' => $monthDate->copy()->startOfMonth(),
                'end' => $monthDate->copy()->endOfMonth(),
            ];
        }
        
        // Get all locations with houses and rooms
        $locations = \App\Models\Location::with(['houses.rooms' => function($query) {
            $query->orderBy('name');
        }])->orderBy('name')->get();
        
        // Get all confirmed bookings for the date range
        $bookingsStart = $months[0]['start']->copy()->startOfDay();
        $bookingsEnd = $months[count($months) - 1]['end']->copy()->endOfDay();
        
        // Get all confirmed bookings that overlap with the date range
        // A booking overlaps if: start_at <= range_end AND (end_at >= range_start OR end_at IS NULL)
        $bookings = Booking::with(['room.house.location'])
            ->where('status', 'confirmed')
            ->where('start_at', '<=', $bookingsEnd->utc())
            ->where(function($query) use ($bookingsStart) {
                $query->whereNull('end_at')
                      ->orWhere('end_at', '>=', $bookingsStart->utc());
            })
            ->get();
        
        // Organize bookings by room and month for easy lookup
        $bookingsByRoomMonth = [];
        foreach ($bookings as $booking) {
            $roomId = $booking->room_id;
            $bookingStart = Carbon::parse($booking->start_at)->setTimezone('Europe/Berlin');
            $bookingEnd = $booking->end_at ? Carbon::parse($booking->end_at)->setTimezone('Europe/Berlin') : null;
            
            foreach ($months as $monthIndex => $month) {
                $monthStart = $month['start'];
                $monthEnd = $month['end'];
                
                // Check if booking overlaps with this month
                $overlaps = false;
                if ($bookingEnd) {
                    $overlaps = $bookingStart->lte($monthEnd) && $bookingEnd->gte($monthStart);
                } else {
                    // Long-term booking (no end date)
                    $overlaps = $bookingStart->lte($monthEnd);
                }
                
                if ($overlaps) {
                    if (!isset($bookingsByRoomMonth[$roomId])) {
                        $bookingsByRoomMonth[$roomId] = [];
                    }
                    if (!isset($bookingsByRoomMonth[$roomId][$monthIndex])) {
                        $bookingsByRoomMonth[$roomId][$monthIndex] = [];
                    }
                    $bookingsByRoomMonth[$roomId][$monthIndex][] = $booking;
                }
            }
        }
        
        return view('admin.bookings.calendar-table', compact(
            'locations', 
            'months', 
            'bookingsByRoomMonth',
            'startMonth',
            'monthsCount'
        ));
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
        
        // Get status counts for filter optimization
        $statusCounts = [
            'all' => Booking::count(),
            'pending' => Booking::where('status', 'pending')->count(),
            'confirmed' => Booking::where('status', 'confirmed')->count(),
            'cancelled' => Booking::where('status', 'cancelled')->count(),
        ];
        
        return view('admin.bookings.index', compact('bookings', 'rooms', 'statusCounts'));
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
            'end_at' => 'nullable|date|after:start_at',
            'guest_first_name' => 'required|string|max:255',
            'guest_last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:255',
            'language' => 'nullable|string|in:Deutsch,Englisch',
            'status' => 'required|in:pending,confirmed,cancelled',
            'source' => 'required|in:manual,website,airbnb',
            'notes' => 'nullable|string',
        ]);

        $room = Room::findOrFail($request->room_id);
        $startAt = Carbon::parse($request->start_at)->setTimezone('Europe/Berlin')->startOfDay();
        $endAt = $request->end_at ? Carbon::parse($request->end_at)->setTimezone('Europe/Berlin')->startOfDay() : null;

        // Check for conflicts
        $conflicts = $this->bookingService->getConflicts($room, $startAt, $endAt);
        $hasConflicts = !empty($conflicts);

        // Calculate total
        $totalAmount = $this->bookingService->calculateTotal($room, $startAt, $endAt);
        $isShortTerm = $endAt && $room->short_term_allowed && $startAt->diffInDays($endAt) <= 30;

        $booking = Booking::create([
            'room_id' => $room->id,
            'start_at' => $startAt->utc(),
            'end_at' => $endAt ? $endAt->utc() : null,
            'source' => $request->source,
            'status' => $request->status,
            'guest_first_name' => $request->guest_first_name,
            'guest_last_name' => $request->guest_last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'language' => $request->language ?? 'Deutsch',
            'notes' => $request->notes,
            'is_short_term' => $isShortTerm,
            'total_amount' => $totalAmount,
        ]);

        // Generate documents for manual bookings
        $documentService = new \App\Services\DocumentService();
        if ($request->source === 'manual') {
            $locale = $booking->getLocaleFromLanguage() ?: 'de'; // Default to German for manual bookings
            
            // Create rental agreement document and generate PDF synchronously
            $rentalAgreement = $documentService->createDocument(
                $booking,
                'rental_agreement',
                $locale,
                []
            );
            try {
                $documentService->generatePdf($rentalAgreement);
            } catch (\Exception $e) {
                \Log::error('Failed to generate rental agreement PDF for manual booking', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
            }
            
            // Create landlord confirmation document and generate PDF synchronously
            $landlordConfirmation = $documentService->createDocument(
                $booking,
                'landlord_confirmation',
                $locale,
                []
            );
            try {
                $documentService->generatePdf($landlordConfirmation);
            } catch (\Exception $e) {
                \Log::error('Failed to generate landlord confirmation PDF for manual booking', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
            }
            
            // Create rent arrears document and generate PDF synchronously
            $rentArrears = $documentService->createDocument(
                $booking,
                'rent_arrears',
                $locale,
                []
            );
            try {
                $documentService->generatePdf($rentArrears);
            } catch (\Exception $e) {
                \Log::error('Failed to generate rent arrears PDF for manual booking', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Audit log: booking created
        \App\Services\AuditService::log('booking_created', [
            'room_id' => $room->id,
            'room_name' => $room->name,
            'guest_name' => "{$request->guest_first_name} {$request->guest_last_name}",
            'start_at' => $startAt->format('Y-m-d'),
            'end_at' => $endAt ? $endAt->format('Y-m-d') : null,
            'status' => $request->status,
            'source' => $request->source,
            'has_conflicts' => $hasConflicts,
            'conflicts' => $hasConflicts ? collect($conflicts)->pluck('id')->toArray() : [],
        ], auth()->id(), $booking);

            // Send confirmation email and documents if booking is created as confirmed
            if ($request->status === 'confirmed') {
                $this->sendConfirmationDocuments($booking, $documentService);
            }

        if ($hasConflicts) {
            return redirect()->route('admin.bookings.index')
                ->with('warning', __('admin.booking_created_with_conflicts'));
        }

        return redirect()->route('admin.bookings.index')
            ->with('success', __('admin.booking_created_successfully'));
    }

    /**
     * Display the specified booking
     */
    public function show(Booking $booking)
    {
        // Redirect to edit page since we don't have a separate show view
        return redirect()->route('admin.bookings.edit', $booking);
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
        $oldStatus = $booking->status;

        // Get booking's locale and temporarily set it for translation
        $bookingLocale = $booking->getLocaleFromLanguage();
        $originalLocale = app()->getLocale();
        app()->setLocale($bookingLocale);

        // Create payment log with translated notes
        $paymentNotes = $request->notes ?? __('admin.manual_payment_recorded_by_admin');
        
        // Restore original locale
        app()->setLocale($originalLocale);

        \App\Models\PaymentLog::create([
            'booking_id' => $booking->id,
            'amount' => $amount,
            'type' => 'manual_adjustment',
            'status' => 'completed',
            'payment_method' => 'manual',
            'notes' => $paymentNotes,
            'user_id' => auth()->id(),
        ]);

        // Determine new status: if pending and payment is made, change to confirmed
        $newStatus = $booking->status;
        if ($booking->status === 'pending' && $newPaidAmount > 0) {
            $newStatus = 'confirmed';
        }

        // Update booking
        $updateData = [
            'paid_amount' => min($newPaidAmount, $booking->total_amount ?? 0),
            'payment_status' => $newPaidAmount >= ($booking->total_amount ?? 0) ? 'paid' : 'pending',
        ];

        // Only update status if it changed
        if ($newStatus !== $oldStatus) {
            $updateData['status'] = $newStatus;
        }

        $booking->update($updateData);

        // Audit log: status change if status was updated
        if ($newStatus !== $oldStatus) {
            \App\Services\AuditService::log('booking_status_changed', [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'reason' => 'Payment marked as paid',
            ], auth()->id(), $booking);
            
            // Send confirmation email and documents if status changed to confirmed
            if ($newStatus === 'confirmed') {
                $documentService = new \App\Services\DocumentService();
                $this->sendConfirmationDocuments($booking, $documentService);
            }
        }

        $message = __('admin.payment_recorded_successfully');
        if ($newStatus !== $oldStatus) {
            $message .= ' ' . __('admin.booking_status_auto_changed');
        }

        return back()->with('success', $message);
    }

    /**
     * Send rental agreement and check-in details to guest
     */
    public function sendDocuments(Request $request, Booking $booking)
    {
        $documentService = new \App\Services\DocumentService();
        $this->sendConfirmationDocuments($booking, $documentService);
        
        return back()->with('success', __('admin.documents_sent_successfully'));
    }

    /**
     * Regenerate a document
     */
    public function regenerateDocument(Request $request, Booking $booking, $documentId)
    {
        try {
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
            
            // Generate PDF synchronously (immediately) instead of queuing
            $documentService = new \App\Services\DocumentService();
            $documentService->generatePdf($document);
            
            // Refresh document to get updated storage_path
            $document->refresh();
            
            return back()->with('success', __('admin.document_regenerated_successfully'));
        } catch (\Exception $e) {
            \Log::error('Document regeneration error', [
                'document_id' => $documentId,
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['error' => __('admin.regenerate_document_failed', ['error' => $e->getMessage()])]);
        }
    }

    /**
     * Send a single document via email
     */
    public function sendDocument(Request $request, Booking $booking, $documentId)
    {
        try {
            $document = \App\Models\Document::where('booking_id', $booking->id)
                ->findOrFail($documentId);
            
            // Check if document has PDF
            if (!$document->storage_path || !\Storage::exists($document->storage_path)) {
                return back()->withErrors(['error' => __('admin.document_pdf_not_available')]);
            }
            
            // Log before sending
            \Log::info('Sending document via email', [
                'document_id' => $document->id,
                'document_type' => $document->doc_type,
                'booking_id' => $booking->id,
                'recipient_email' => $booking->email,
                'guest_name' => "{$booking->guest_first_name} {$booking->guest_last_name}",
                'sent_by' => auth()->user()->name ?? 'System',
                'sent_at' => now()->toDateTimeString(),
                'mail_config' => [
                    'mailer' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'from_address' => config('mail.from.address'),
                ],
            ]);
            
            // Send document via email directly (synchronously)
            try {
                Mail::to($booking->email)->send(new DocumentSent($document, $booking));
                
                // Update sent timestamp
                $document->update(['sent_to_customer_at' => now()]);
                
                // Log successful send
                \Log::info('Document sent successfully via email', [
                    'document_id' => $document->id,
                    'document_type' => $document->doc_type,
                    'booking_id' => $booking->id,
                    'recipient_email' => $booking->email,
                    'sent_at' => now()->toDateTimeString(),
                ]);
            } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
                // SMTP/Transport connection errors
                \Log::error('Mail Transport Exception when sending document', [
                    'document_id' => $document->id,
                    'booking_id' => $booking->id,
                    'recipient_email' => $booking->email,
                    'error' => $e->getMessage(),
                    'error_code' => method_exists($e, 'getCode') ? $e->getCode() : null,
                    'error_class' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            } catch (\Symfony\Component\Mime\Exception\RfcComplianceException $e) {
                // Invalid email address
                \Log::error('Invalid email address when sending document', [
                    'document_id' => $document->id,
                    'booking_id' => $booking->id,
                    'recipient_email' => $booking->email,
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                ]);
                throw $e;
            } catch (\Exception $e) {
                // Other mail-related errors
                \Log::error('Mail exception when sending document', [
                    'document_id' => $document->id,
                    'booking_id' => $booking->id,
                    'recipient_email' => $booking->email,
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }
            
            // Get document type name for success message
            $docTypeNames = [
                'rental_agreement' => __('admin.rental_agreement'),
                'landlord_confirmation' => __('admin.landlord_confirmation'),
                'rent_arrears' => __('admin.rent_arrears'),
            ];
            $docTypeName = $docTypeNames[$document->doc_type] ?? $document->doc_type;
            
            return back()->with('success', __('admin.document_sent_successfully') . ' (' . $docTypeName . ' → ' . $booking->email . ')');
        } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
            // SMTP/Transport connection errors
            \Log::error('Document send error - SMTP/Transport failure', [
                'document_id' => $documentId,
                'booking_id' => $booking->id,
                'recipient_email' => $booking->email ?? 'N/A',
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'mail_config' => [
                    'mailer' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'from_address' => config('mail.from.address'),
                ],
                'trace' => $e->getTraceAsString(),
            ]);

            $errorMessage = __('admin.send_document_failed', ['error' => $e->getMessage()]);
            $errorMessage .= ' ' . __('admin.check_smtp_configuration');
            return back()->withErrors(['error' => $errorMessage]);
        } catch (\Exception $e) {
            \Log::error('Document send error', [
                'document_id' => $documentId,
                'booking_id' => $booking->id,
                'recipient_email' => $booking->email ?? 'N/A',
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['error' => __('admin.send_document_failed', ['error' => $e->getMessage()])]);
        }
    }

    /**
     * Update the specified booking
     */
    public function update(Request $request, Booking $booking)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after:start_at',
            'guest_first_name' => 'required|string|max:255',
            'guest_last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:255',
            'language' => 'nullable|string|in:Deutsch,Englisch',
            'status' => 'required|in:pending,confirmed,cancelled',
            'source' => 'required|in:manual,website,airbnb',
            'notes' => 'nullable|string',
            'override_conflict' => 'nullable|boolean',
        ]);

        $room = Room::findOrFail($request->room_id);
        $startAt = Carbon::parse($request->start_at)->setTimezone('Europe/Berlin')->startOfDay();
        $endAt = $request->end_at ? Carbon::parse($request->end_at)->setTimezone('Europe/Berlin')->startOfDay() : null;

        // Check for conflicts (excluding current booking)
        $conflicts = $this->bookingService->getConflicts($room, $startAt, $endAt, $booking->id);
        $hasConflicts = !empty($conflicts);

        if ($hasConflicts && !$request->override_conflict) {
            return back()
                ->withErrors(['conflict' => __('admin.conflict_error')])
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
                'end_at' => $endAt ? $endAt->format('Y-m-d') : null,
                'conflicting_booking_ids' => collect($conflicts)->pluck('id')->toArray(),
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
                'start_at' => $oldStartAt ? $oldStartAt->format('Y-m-d') : null,
                'end_at' => $oldEndAt ? $oldEndAt->format('Y-m-d') : null,
                'status' => $oldStatus,
                'guest_first_name' => $booking->guest_first_name,
                'guest_last_name' => $booking->guest_last_name,
                'email' => $booking->email,
            ],
        ];

        // Recalculate total if dates or room changed
        if ($booking->room_id != $room->id || 
            ($oldStartAt && $oldStartAt->notEqualTo($startAt->utc())) || 
            ($oldEndAt && $endAt && $oldEndAt->notEqualTo($endAt->utc())) ||
            ($oldEndAt === null && $endAt !== null) ||
            ($oldEndAt !== null && $endAt === null) ||
            !$oldStartAt) {
            $totalAmount = $this->bookingService->calculateTotal($room, $startAt, $endAt);
            $isShortTerm = $endAt && $room->short_term_allowed && $startAt->diffInDays($endAt) <= 30;
        } else {
            $totalAmount = $booking->total_amount;
            $isShortTerm = $booking->is_short_term;
        }

        $booking->update([
            'room_id' => $room->id,
            'start_at' => $startAt->utc(),
            'end_at' => $endAt ? $endAt->utc() : null,
            'source' => $request->source,
            'status' => $request->status,
            'guest_first_name' => $request->guest_first_name,
            'guest_last_name' => $request->guest_last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'language' => $request->language ?? $booking->language ?? 'Deutsch',
            'notes' => $request->notes,
            'is_short_term' => $isShortTerm,
            'total_amount' => $totalAmount,
        ]);

        // Track new values
        $changes['new'] = [
            'room_id' => $room->id,
            'start_at' => $startAt->format('Y-m-d'),
            'end_at' => $endAt ? $endAt->format('Y-m-d') : null,
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
            
            // Send confirmation email and documents if status changed to confirmed
            if ($request->status === 'confirmed' && $oldStatus !== 'confirmed') {
                $documentService = new \App\Services\DocumentService();
                $this->sendConfirmationDocuments($booking, $documentService);
            }
        }

        return redirect()->route('admin.bookings.index')
            ->with('success', __('admin.booking_updated_successfully'));
    }

    /**
     * Send confirmation documents (rental agreement and check-in details) to guest
     */
    protected function sendConfirmationDocuments(Booking $booking, \App\Services\DocumentService $documentService = null)
    {
        if (!$documentService) {
            $documentService = new \App\Services\DocumentService();
        }
        
        try {
            $booking->refresh();
            
            // Send confirmation email
            Mail::to($booking->email)->send(new BookingConfirmation($booking));
            
            // Ensure rental agreement document exists and is generated
            $rentalAgreement = $booking->documents()
                ->where('doc_type', 'rental_agreement')
                ->first();
            
            if (!$rentalAgreement) {
                // Create rental agreement if it doesn't exist (for manual bookings)
                $locale = $booking->getLocaleFromLanguage() ?: 'de';
                $rentalAgreement = $documentService->createDocument(
                    $booking,
                    'rental_agreement',
                    $locale,
                    []
                );
            }
            
            // Generate PDF if not already generated
            if (!$rentalAgreement->storage_path || !\Storage::exists($rentalAgreement->storage_path)) {
                try {
                    $documentService->generatePdf($rentalAgreement);
                    $rentalAgreement->refresh();
                } catch (\Exception $e) {
                    \Log::error('Failed to generate rental agreement PDF', [
                        'booking_id' => $booking->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            
            // Send rental agreement if it exists and has PDF
            if ($rentalAgreement->storage_path && \Storage::exists($rentalAgreement->storage_path)) {
                \App\Jobs\SendDocumentEmail::dispatch($rentalAgreement, [$booking->email], false)->afterResponse();
            }
            
            // Send check-in PDF if available (use booking language, not dashboard locale)
            $checkInPdfPath = $documentService->getCheckInPdfPath($booking->room);
            if ($checkInPdfPath && \Storage::exists($checkInPdfPath)) {
                $locale = $booking->getLocaleFromLanguage();
                $message = $locale === 'de'
                    ? "Sehr geehrter Kunde,\n\nBitte finden Sie die Check-in Informationen im Anhang.\n\nMit freundlichen Grüßen,\nMaRoom Team"
                    : "Dear customer,\n\nPlease find attached the check-in information document.\n\nBest regards,\nMaRoom Team";

                $subject = $locale === 'de'
                    ? 'Check-in Informationen - MaRoom'
                    : 'Check-in Information - MaRoom';

                Mail::to($booking->email)->send(new CheckInPdfsSent($message, [$checkInPdfPath], $subject, $locale));
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send booking confirmation email and documents', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Debug PDF overlay positions - generates a test PDF with visible markers
     */
    public function debugPdfOverlay(Booking $booking, Request $request)
    {
        $locale = $request->get('locale', $booking->getLocaleFromLanguage() === 'de' ? 'de' : 'en');
        
        // Get or create a rental agreement document for testing
        $documentService = new \App\Services\DocumentService();
        $document = $booking->documents()
            ->where('doc_type', 'rental_agreement')
            ->where('locale', $locale)
            ->first();
        
        if (!$document) {
            $document = $documentService->createDocument($booking, 'rental_agreement', $locale, []);
        }
        
        // Generate PDF with debug mode enabled
        $templateService = app(\App\Services\RentalPdfTemplateService::class);
        $debugPath = $templateService->generateDebugPdf($document, $locale);
        
        if (!$debugPath || !\Storage::exists($debugPath)) {
            return redirect()->route('admin.bookings.edit', $booking)
                ->with('error', 'Failed to generate debug PDF. Make sure PDF templates exist in storage/app/rental-templates/ as rental-agreement-en.pdf and rental-agreement-de.pdf');
        }
        
        return response()->download(
            \Storage::path($debugPath),
            'pdf-overlay-debug-' . $locale . '.pdf',
            ['Content-Type' => 'application/pdf']
        )->deleteFileAfterSend(true);
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
            'start_at' => $booking->start_at ? $booking->start_at->format('Y-m-d') : null,
            'end_at' => $booking->end_at ? $booking->end_at->format('Y-m-d') : null,
            'status' => $booking->status,
        ], auth()->id(), $booking);

        $booking->delete();

        return redirect()->route('admin.bookings.index')
            ->with('success', __('admin.booking_deleted_successfully'));
    }
}

