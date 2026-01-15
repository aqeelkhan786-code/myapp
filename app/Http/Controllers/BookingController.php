<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Booking;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use App\Services\BookingService;
use App\Services\DocumentService;
use App\Services\IcalService;
use App\Services\PaymentService;
use App\Jobs\GenerateDocumentPdf;
use App\Jobs\SendDocumentEmail;
use App\Mail\BookingConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BookingController extends Controller
{
    protected $bookingService;
    protected $documentService;
    protected $icalService;
    protected $paymentService;

    public function __construct(BookingService $bookingService, DocumentService $documentService, IcalService $icalService, PaymentService $paymentService)
    {
        $this->bookingService = $bookingService;
        $this->documentService = $documentService;
        $this->icalService = $icalService;
        $this->paymentService = $paymentService;
    }

    /**
     * Display the apartment selection page
     */
    public function index(Request $request)
    {
        $query = Room::with('images');
        
        // Determine if this is a long-term booking (no check_out date)
        $checkIn = $request->get('check_in');
        $checkOut = $request->get('check_out');
        $isLongTerm = !empty($checkIn) && (empty($checkOut) || trim($checkOut) === '');
        
        // Filter by availability if dates are provided
        if ($request->has('check_in') && $request->has('check_out') && !$isLongTerm) {
            try {
                $checkInDate = Carbon::parse($request->check_in)->setTimezone('Europe/Berlin')->startOfDay();
                $checkOutDate = Carbon::parse($request->check_out)->setTimezone('Europe/Berlin')->startOfDay();
                
                // Get room IDs that have confirmed bookings for these dates
                $unavailableRoomIds = Booking::where('status', 'confirmed')
                    ->where(function ($q) use ($checkInDate, $checkOutDate) {
                        $q->where(function ($q2) use ($checkInDate, $checkOutDate) {
                            $q2->where('start_at', '<', $checkOutDate->utc())
                               ->where('end_at', '>', $checkInDate->utc());
                        });
                    })
                    ->pluck('room_id')
                    ->unique();
                
                // Exclude unavailable rooms
                $query->whereNotIn('id', $unavailableRoomIds);
            } catch (\Exception $e) {
                // Invalid dates, show all rooms
            }
        } elseif ($isLongTerm && $checkIn) {
            // For long-term bookings, check if room is available from check_in date onwards
            try {
                $checkInDate = Carbon::parse($request->check_in)->setTimezone('Europe/Berlin')->startOfDay();
                
                // Get room IDs that have confirmed bookings starting from check_in date
                $unavailableRoomIds = Booking::where('status', 'confirmed')
                    ->where(function ($q) use ($checkInDate) {
                        $q->where('start_at', '>=', $checkInDate->utc())
                          ->orWhere(function ($q2) use ($checkInDate) {
                              // Also exclude rooms with ongoing bookings that extend past check_in
                              $q2->where('start_at', '<', $checkInDate->utc())
                                 ->where(function ($q3) {
                                     $q3->whereNull('end_at')
                                        ->orWhere('end_at', '>', $checkInDate->utc());
                                 });
                          });
                    })
                    ->pluck('room_id')
                    ->unique();
                
                // Exclude unavailable rooms
                $query->whereNotIn('id', $unavailableRoomIds);
            } catch (\Exception $e) {
                // Invalid dates, show all rooms
            }
        }
        
        $rooms = $query->get();
        
        return view('booking.index', compact('rooms', 'isLongTerm', 'checkIn', 'checkOut'));
    }

    /**
     * Show room details with calendar
     */
    public function show(Room $room)
    {
        // Ensure room exists and has required data
        if (!$room || !$room->exists) {
            abort(404, 'Room not found');
        }
        
        // Load relationships, but property might be null
        $room->load('images', 'property');
        
        // Ensure room has a name
        if (!$room->name) {
            abort(404, 'Room data is incomplete');
        }
        
        // Get confirmed bookings for calendar
        $bookings = Booking::where('room_id', $room->id)
            ->where('status', 'confirmed')
            ->get(['start_at', 'end_at']);
        
        return view('booking.show', compact('room', 'bookings'));
    }

    /**
     * Store a new booking
     */
    public function store(Request $request, Room $room)
    {
        $request->validate([
            'start_at' => 'required|date|after:yesterday',
            'end_at' => 'required|date|after:start_at',
        ], [
            'start_at.required' => 'Please select a check-in date.',
            'start_at.after' => 'Check-in date must be today or later.',
            'end_at.required' => 'Please select a check-out date.',
            'end_at.after' => 'Check-out date must be after check-in date.',
        ]);

        try {
            $startAt = Carbon::parse($request->start_at)->setTimezone('Europe/Berlin')->startOfDay();
            $endAt = Carbon::parse($request->end_at)->setTimezone('Europe/Berlin')->startOfDay();

            // Check availability
            if (!$this->bookingService->isAvailable($room, $startAt, $endAt)) {
                return back()->withErrors(['dates' => 'The selected dates are not available.'])->withInput();
            }

            // Calculate total
            $totalAmount = $this->bookingService->calculateTotal($room, $startAt, $endAt);
            $isShortTerm = $room->short_term_allowed && $startAt->diffInDays($endAt) <= 30;

            // Create booking with temporary guest info (will be updated in step 1)
            $booking = Booking::create([
                'room_id' => $room->id,
                'start_at' => $startAt->utc(),
                'end_at' => $endAt->utc(),
                'source' => 'website',
                'status' => 'pending',
                'is_short_term' => $isShortTerm,
                'total_amount' => $totalAmount,
                'guest_first_name' => 'Pending',
                'guest_last_name' => 'Guest',
                'email' => 'pending@example.com', // Will be updated in step 1
            ]);

            return redirect()->route('booking.step', ['booking' => $booking->id, 'step' => 1]);
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Booking creation failed: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'room_id' => $room->id ?? null,
            ]);
            
            // Show user-friendly error message
            return back()->withErrors(['error' => 'An error occurred while creating your booking. Please try again.'])->withInput();
        }
    }

    /**
     * Show booking step form
     * Steps 2 and 3 are admin-only
     */
    public function step(Booking $booking, int $step)
    {
        $booking->load('room', 'documents', 'room.property');
        
        if ($step < 1 || $step > 3) {
            abort(404);
        }

        // Steps 2 and 3 are admin-only
        if ($step === 2 || $step === 3) {
            // Check if user is admin (using Spatie Permission roles)
            if (!auth()->check() || !auth()->user()->hasRole('admin')) {
                abort(403, 'Only administrators can access steps 2 and 3.');
            }
        }

        // Get all rooms for apartment selection
        $rooms = \App\Models\Room::with('property')->orderBy('name')->get();

        return view('booking.step', compact('booking', 'step', 'rooms'));
    }

    /**
     * Save booking step data
     */
    public function saveStep(Request $request, Booking $booking, int $step)
    {
        if ($step === 1) {
            // Build validation rules conditionally
            $validationRules = [
                'guest_first_name' => 'required|string|max:255',
                'guest_last_name' => 'required|string|max:255',
                'language' => 'required|in:Deutsch,Englisch',
                'communication_preference' => 'required|string',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:255',
                'room_id' => 'nullable|exists:rooms,id',
                'start_at' => 'nullable|date',
                'end_at' => 'nullable|date',
                'signature' => 'required|string',
                'payment_method_id' => $booking->is_short_term ? 'required|string' : 'nullable',
            ];
            
            // Address fields are only required for short-term rentals
            if (!$booking->is_short_term) {
                $validationRules['renter_address'] = 'nullable|string|max:255';
                $validationRules['renter_postal_code'] = 'nullable|string|max:255';
                $validationRules['renter_city'] = 'nullable|string|max:255';
                $validationRules['renter_phone'] = 'nullable|string|max:255';
            } else {
                $validationRules['renter_address'] = 'required|string|max:255';
                $validationRules['renter_postal_code'] = 'required|string|max:255';
                $validationRules['renter_city'] = 'required|string|max:255';
                $validationRules['renter_phone'] = 'required|string|max:255';
            }
            
            $request->validate($validationRules);

            $updateData = $request->only([
                'guest_first_name',
                'guest_last_name',
                'language',
                'communication_preference',
                'email',
                'phone',
            ]);
            
            // Only include address fields if provided (required for long-term, optional for short-term)
            if ($request->has('renter_address')) {
                $updateData['renter_address'] = $request->renter_address;
            }
            if ($request->has('renter_postal_code')) {
                $updateData['renter_postal_code'] = $request->renter_postal_code;
            }
            if ($request->has('renter_city')) {
                $updateData['renter_city'] = $request->renter_city;
            }
            
            $booking->update($updateData);

            // Update room if changed
            if ($request->room_id && $request->room_id != $booking->room_id) {
                $booking->update(['room_id' => $request->room_id]);
                // Recalculate total amount if room changed
                $newRoom = \App\Models\Room::find($request->room_id);
                if ($newRoom) {
                    $nights = \Carbon\Carbon::parse($booking->start_at)->diffInDays(\Carbon\Carbon::parse($booking->end_at));
                    $booking->update(['total_amount' => $nights * $newRoom->base_price]);
                }
            }

            // Update dates if changed
            if ($request->start_at) {
                $booking->update(['start_at' => $request->start_at]);
            }
            if ($request->end_at) {
                $booking->update(['end_at' => $request->end_at]);
            }

            // Process payment for short-term bookings
            if ($booking->is_short_term && $request->payment_method_id) {
                try {
                    if (!$booking->stripe_payment_intent_id) {
                        $paymentIntent = $this->paymentService->createPaymentIntent($booking);
                    } else {
                        $paymentIntent = \Stripe\PaymentIntent::retrieve($booking->stripe_payment_intent_id);
                    }

                    $paymentIntent = $this->paymentService->confirmPayment(
                        $paymentIntent->id,
                        $request->payment_method_id
                    );

                    if ($paymentIntent->status !== 'succeeded') {
                        return back()->withErrors(['payment' => 'Payment could not be processed. Please try again.'])->withInput();
                    }

                    $this->paymentService->handleSuccessfulPayment($booking, $paymentIntent);
                } catch (\Exception $e) {
                    return back()->withErrors(['payment' => $e->getMessage()])->withInput();
                }
            }

            // Save signature and create rental agreement document
            // Use booking language instead of app locale
            $locale = $booking->getLocaleFromLanguage();
            $document = $this->documentService->createDocument(
                $booking, 
                'rental_agreement', 
                $locale,
                ['signature' => $request->signature]
            );
            GenerateDocumentPdf::dispatch($document);
            
            // Send email after PDF is generated (will be handled in job)
            SendDocumentEmail::dispatch($document, [$booking->email], true)->afterResponse();

            return redirect()->route('booking.step', ['booking' => $booking->id, 'step' => 2]);
        }

        if ($step === 2 || $step === 3) {
            // Steps 2 and 3 are handled by signature saving
            return redirect()->route('booking.step', ['booking' => $booking->id, 'step' => $step + 1]);
        }

        return back();
    }

    /**
     * Save signature for a document
     */
    public function saveSignature(Request $request, Booking $booking)
    {
        $request->validate([
            'step' => 'required|integer|in:1,2,3',
            'signature' => 'required|string',
        ]);

        $step = $request->step;
        $docTypes = [
            1 => 'rental_agreement',
            2 => 'landlord_confirmation',
            3 => 'rent_arrears',
        ];

        $docType = $docTypes[$step];

        // Create or update document using DocumentService
        // Use booking language instead of app locale
        $locale = $booking->getLocaleFromLanguage();
        $document = $this->documentService->createDocument(
            $booking, 
            $docType, 
            $locale,
            ['signature' => $request->signature]
        );
        
        // Update with signature data
        $document->update([
            'signed_at' => now(),
            'signature_data' => ['signature' => $request->signature],
        ]);

        // Generate PDF and send emails
        GenerateDocumentPdf::dispatch($document);
        
        if ($step === 2) {
            // Wohnungsgeberbescheinigung: send to customer and owner
            SendDocumentEmail::dispatch($document, [$booking->email], true)->afterResponse();
        } elseif ($step === 3) {
            // Mietschuldsbefreiung: send to owner only
            SendDocumentEmail::dispatch($document, [], true)->afterResponse();
            $booking->update(['status' => 'confirmed']);
            Mail::to($booking->email)->send(new BookingConfirmation($booking));
        }

        if ($step < 3) {
            return redirect()->route('booking.step', ['booking' => $booking->id, 'step' => $step + 1]);
        }

        return redirect()->route('booking.complete', ['booking' => $booking->id])->with('success', __('booking.booking_completed_successfully'));
    }

    /**
     * Create payment intent for booking form (before booking creation)
     */
    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
        ]);
        
        try {
            $room = Room::findOrFail($request->room_id);
            $startAt = Carbon::parse($request->start_at)->setTimezone('Europe/Berlin')->startOfDay();
            $endAt = Carbon::parse($request->end_at)->setTimezone('Europe/Berlin')->startOfDay();
            
            // Check if it's short-term
            $isShortTerm = $room->short_term_allowed && $startAt->diffInDays($endAt) <= 30;
            
            if (!$isShortTerm) {
                return response()->json(['error' => 'Payment is only required for short-term bookings.'], 400);
            }
            
            // Calculate total
            $totalAmount = $this->bookingService->calculateTotal($room, $startAt, $endAt);
            
            // Create payment intent directly using Stripe
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => (int)($totalAmount * 100), // Convert to cents
                'currency' => 'eur',
                'metadata' => [
                    'room_id' => $room->id,
                    'start_at' => $startAt->format('Y-m-d'),
                    'end_at' => $endAt->format('Y-m-d'),
                ],
            ]);
            
            return response()->json([
                'client_secret' => $paymentIntent->client_secret,
                'amount' => $totalAmount,
            ]);
        } catch (\Exception $e) {
            \Log::error('Payment intent creation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create payment intent: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Process payment for short-term booking
     */
    public function processPayment(Request $request, Booking $booking)
    {
        // Handle setup request for Stripe Elements
        if ($request->has('action') && $request->action === 'setup') {
            try {
                if (!$booking->stripe_payment_intent_id) {
                    $paymentIntent = $this->paymentService->createPaymentIntent($booking);
                } else {
                    $paymentIntent = \Stripe\PaymentIntent::retrieve($booking->stripe_payment_intent_id);
                }
                
                return response()->json([
                    'client_secret' => $paymentIntent->client_secret
                ]);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        // Handle payment confirmation
        $request->validate([
            'payment_method_id' => 'required|string',
        ]);

        try {
            if (!$booking->stripe_payment_intent_id) {
                $paymentIntent = $this->paymentService->createPaymentIntent($booking);
            } else {
                $paymentIntent = \Stripe\PaymentIntent::retrieve($booking->stripe_payment_intent_id);
            }

            $paymentIntent = $this->paymentService->confirmPayment(
                $paymentIntent->id,
                $request->payment_method_id
            );

            if ($paymentIntent->status === 'succeeded') {
                $this->paymentService->handleSuccessfulPayment($booking, $paymentIntent);
                return redirect()->route('booking.step', ['booking' => $booking->id, 'step' => 2])
                    ->with('success', __('booking.payment_processed_successfully'));
            }

            return back()->withErrors(['payment' => 'Payment could not be processed.']);
        } catch (\Exception $e) {
            return back()->withErrors(['payment' => $e->getMessage()]);
        }
    }

    /**
     * Show booking completion page
     */
    public function complete(Booking $booking)
    {
        $booking->load('room.images', 'documents');
        return view('booking.complete', compact('booking'));
    }

    /**
     * Show booking lookup page
     */
    public function lookup()
    {
        return view('booking.lookup');
    }

    /**
     * Find bookings by email
     */
    public function findBookings(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;
        $bookings = Booking::where('email', $email)
            ->with('room', 'documents')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($bookings->isEmpty()) {
            return back()->withErrors(['email' => 'No bookings found for this email address.'])->withInput();
        }

        return view('booking.my-bookings', compact('bookings', 'email'));
    }

    /**
     * View a specific booking
     */
    public function view(Booking $booking)
    {
        $booking->load('room.house', 'documents', 'paymentLogs');
        
        // Get check-in PDF path if available
        $checkInPdfPath = null;
        if ($booking->room) {
            $checkInPdfPath = $this->documentService->getCheckInPdfPath($booking->room);
        }
        
        return view('booking.view', compact('booking', 'checkInPdfPath'));
    }

    /**
     * Export iCal feed for a room
     */
    public function icalExport(Room $room, string $token)
    {
        $feed = IcalFeed::where('room_id', $room->id)
            ->where('direction', 'export')
            ->where('token', $token)
            ->where('active', true)
            ->first();

        if (!$feed) {
            abort(404);
        }

        $ical = $this->icalService->generateExport($room);

        return response($ical, 200)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="room-' . $room->id . '.ics"');
    }

    /**
     * Download a document PDF
     */
    public function downloadDocument($documentId)
    {
        try {
            // Find document by ID (using explicit lookup for better error handling)
            $document = Document::with('booking')->findOrFail($documentId);
            
            // Check if document exists and has a storage path
            if (empty($document->storage_path)) {
                abort(404, 'Document PDF has not been generated yet. Please wait a moment and try again.');
            }
            
            if (!Storage::exists($document->storage_path)) {
                abort(404, 'Document file not found in storage. The PDF may still be generating. Please make sure the queue worker is running.');
            }

            // Generate a friendly filename
            $booking = $document->booking;
            $docTypeNames = [
                'rental_agreement' => 'Rental-Agreement',
                'landlord_confirmation' => 'Landlord-Confirmation',
                'rent_arrears' => 'Rent-Arrears-Certificate',
            ];
            
            $docTypeName = $docTypeNames[$document->doc_type] ?? $document->doc_type;
            $filename = $docTypeName . '-Booking-' . $booking->id . '-v' . $document->version . '.pdf';

            return Storage::download($document->storage_path, $filename);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Document not found.');
        } catch (\Exception $e) {
            \Log::error('Document download error', ['document_id' => $documentId, 'error' => $e->getMessage()]);
            abort(500, 'An error occurred while downloading the document.');
        }
    }

    /**
     * Show 3-step booking form (before booking creation)
     */
    public function showForm(Room $room, Request $request)
    {
        $step = $request->get('step', 1);
        
        if ($step < 1 || $step > 3) {
            $step = 1;
        }
        
        // Get session data if exists
        $formData = session('booking_form_data', []);
        
        // Pre-fill dates from query parameters if coming from search page
        if ($request->has('check_in') && empty($formData['step2'])) {
            $checkOut = $request->get('check_out');
            // Only set end_at if check_out is provided and not empty
            $endAtValue = (!empty($checkOut) && trim($checkOut) !== '') ? $checkOut : null;
            $formData['step2'] = [
                'start_at' => $request->get('check_in'),
                'end_at' => $endAtValue, // null for long-term rentals
                'renter_address' => '',
                'renter_postal_code' => '',
                'renter_city' => '',
            ];
            session(['booking_form_data' => $formData]);
        }
        
        // Also update formData if check_in/check_out are in request (to override any old session data)
        if ($request->has('check_in')) {
            $checkOut = $request->get('check_out');
            $endAtValue = (!empty($checkOut) && trim($checkOut) !== '') ? $checkOut : null;
            if (!isset($formData['step2'])) {
                $formData['step2'] = [];
            }
            $formData['step2']['start_at'] = $request->get('check_in');
            $formData['step2']['end_at'] = $endAtValue; // null for long-term rentals
            session(['booking_form_data' => $formData]);
        }
        
        $room->load('images', 'property', 'house');
        
        // Get all rooms for apartment selection dropdown
        $allRooms = Room::with('property')->orderBy('name')->get();
        
        // Prepare rooms data for JavaScript
        // Determine if this is long-term to set the correct price
        $checkIn = request()->get('check_in') ?? $formData['step2']['start_at'] ?? null;
        $checkOut = request()->get('check_out') ?? $formData['step2']['end_at'] ?? null;
        $isLongTermForPrice = empty($checkOut) || $checkOut === null || trim($checkOut) === '';
        
        $roomsData = $allRooms->map(function($r) use ($isLongTermForPrice) {
            return [
                'id' => $r->id,
                'name' => $r->name,
                'address' => ($r->property && $r->property->address) ? $r->property->address : 'N/A',
                'base_price' => $r->base_price,
                'monthly_price' => $r->monthly_price ?? 700,
                'price' => $isLongTermForPrice ? ($r->monthly_price ?? 700) : $r->base_price
            ];
        })->values()->all();
        
        // Get confirmed bookings for calendar (for step 2)
        $bookings = Booking::where('room_id', $room->id)
            ->where('status', 'confirmed')
            ->get(['start_at', 'end_at']);
        
        // Calculate if this will be a short-term booking for payment display
        // Determine rental type from request parameters or form data
        $checkIn = request()->get('check_in') ?? $formData['step2']['start_at'] ?? null;
        $checkOut = request()->get('check_out') ?? $formData['step2']['end_at'] ?? null;
        
        $isShortTerm = false;
        $totalAmount = 0;
        
        if ($checkIn) {
            $startAt = \Carbon\Carbon::parse($checkIn);
            $endAt = null;
            
            // Only parse end_at if it exists and is not empty
            if ($checkOut && trim($checkOut) !== '') {
                $endAt = \Carbon\Carbon::parse($checkOut);
            }
            
            if ($endAt && $room->short_term_allowed) {
                $nights = $startAt->diffInDays($endAt);
                $isShortTerm = $nights <= 30;
                $totalAmount = $this->bookingService->calculateTotal($room, $startAt, $endAt);
            } else if (!$endAt) {
                // Long-term rental (no end date)
                $isShortTerm = false;
                $totalAmount = $this->bookingService->calculateTotal($room, $startAt, null);
            }
        }
        
        return view('booking.form', compact('room', 'step', 'formData', 'bookings', 'allRooms', 'roomsData', 'isShortTerm', 'totalAmount'));
    }

    /**
     * Save form step data to session
     */
    public function saveFormStep(Request $request, Room $room, int $step)
    {
        $formData = session('booking_form_data', []);
        
        if ($step === 1) {
            // Update room if changed (shouldn't happen as it's disabled, but keep for safety)
            $selectedRoomId = $request->room_id ?: $room->id;
            $selectedRoom = \App\Models\Room::findOrFail($selectedRoomId);
            
            // Check availability
            $startAt = Carbon::parse($request->start_at)->setTimezone('Europe/Berlin')->startOfDay();
            
            // Determine if this is a long-term rental (needed for validation)
            $endAt = $request->end_at ? Carbon::parse($request->end_at)->setTimezone('Europe/Berlin')->startOfDay() : null;
            $isLongTermRental = empty($endAt) || $endAt === null || trim($endAt) === '';
            if ($endAt && $selectedRoom->short_term_allowed) {
                $nights = $startAt->diffInDays($endAt);
                $isLongTermRental = $nights > 30;
            }
            
            // Build validation rules conditionally
            $validationRules = [
                'guest_first_name' => 'required|string|max:255',
                'guest_last_name' => 'required|string|max:255',
                'language' => 'required|in:Deutsch,Englisch',
                'communication_preference' => 'required|string',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:255',
                'room_id' => 'nullable|exists:rooms,id',
                'start_at' => 'required|date|after:yesterday',
                'end_at' => 'nullable|date|after:start_at',
                'signature' => 'nullable|string', // Only required for long-term rentals
                'payment_method_id' => 'nullable|string', // Required for short-term bookings, handled below
            ];
            
            // Address fields are only required for short-term rentals
            if ($isLongTermRental) {
                $validationRules['renter_address'] = 'nullable|string|max:255';
                $validationRules['renter_postal_code'] = 'nullable|string|max:255';
                $validationRules['renter_city'] = 'nullable|string|max:255';
                $validationRules['renter_phone'] = 'nullable|string|max:255';
            } else {
                $validationRules['renter_address'] = 'required|string|max:255';
                $validationRules['renter_postal_code'] = 'required|string|max:255';
                $validationRules['renter_city'] = 'required|string|max:255';
                $validationRules['renter_phone'] = 'required|string|max:255';
            }
            
            $request->validate($validationRules);
            
            // Validate signature for long-term rentals
            if ($isLongTermRental && !$request->signature) {
                return back()->withErrors(['signature' => 'Signature is required for long-term rentals.'])->withInput();
            }
            
            if ($request->end_at) {
                // Short-term rental with check-out date
                $endAt = Carbon::parse($request->end_at)->setTimezone('Europe/Berlin')->startOfDay();
                if (!$this->bookingService->isAvailable($selectedRoom, $startAt, $endAt)) {
                    return back()->withErrors(['dates' => 'The selected dates are not available.'])->withInput();
                }
            } else {
                // Long-term rental - check if room is available on check-in date
                $unavailableRoomIds = Booking::where('status', 'confirmed')
                    ->where('room_id', $selectedRoom->id)
                    ->where('start_at', '<=', $startAt->utc())
                    ->where('end_at', '>', $startAt->utc())
                    ->exists();
                
                if ($unavailableRoomIds) {
                    return back()->withErrors(['dates' => 'The selected date is not available.'])->withInput();
                }
            }
            
            $formData['step1'] = $request->only([
                'guest_first_name',
                'guest_last_name',
                'language',
                'communication_preference',
                'email',
                'phone',
            ]);
            
            
            // Use guest name and email from step1
            $renterName = trim(($formData['step1']['guest_first_name'] ?? '') . ' ' . ($formData['step1']['guest_last_name'] ?? ''));
            $renterEmail = $formData['step1']['email'] ?? '';
            
            $formData['step2'] = [
                'room_id' => $selectedRoomId,
                'start_at' => $request->start_at,
                'end_at' => $request->end_at ?? null, // Optional for long-term rentals
                'renter_name' => $renterName,
                'renter_address' => $request->renter_address ?? '',
                'renter_postal_code' => $request->renter_postal_code ?? '',
                'renter_city' => $request->renter_city ?? '',
                'renter_phone' => $request->renter_phone ?? $request->phone ?? '',
                'renter_email' => $renterEmail,
            ];
            
            $formData['step3'] = [
                'signature' => $request->signature ?? null,
            ];
            
            // Step 1 includes everything, so create booking directly
            try {
                // Calculate total
                $endAt = $request->end_at ? Carbon::parse($request->end_at)->setTimezone('Europe/Berlin')->startOfDay() : null;
                $totalAmount = $endAt ? $this->bookingService->calculateTotal($selectedRoom, $startAt, $endAt) : 0;
                $isShortTerm = $endAt && $selectedRoom->short_term_allowed && $startAt->diffInDays($endAt) <= 30;
                
                // Validate payment for short-term bookings
                if ($isShortTerm && !$request->payment_method_id) {
                    return back()->withErrors(['payment' => 'Payment is required for short-term bookings.'])->withInput();
                }
                
                // Create booking
                $booking = Booking::create([
                    'room_id' => $selectedRoomId,
                    'start_at' => $startAt->utc(),
                    'end_at' => $endAt ? $endAt->utc() : null,
                    'source' => 'website',
                    'status' => 'pending',
                    'is_short_term' => $isShortTerm,
                    'total_amount' => $totalAmount,
                    'guest_first_name' => $formData['step1']['guest_first_name'],
                    'guest_last_name' => $formData['step1']['guest_last_name'],
                    'language' => $formData['step1']['language'],
                    'communication_preference' => $formData['step1']['communication_preference'],
                    'email' => $formData['step1']['email'],
                    'phone' => $formData['step1']['phone'],
                    'renter_address' => $formData['step2']['renter_address'],
                    'renter_postal_code' => $formData['step2']['renter_postal_code'],
                    'renter_city' => $formData['step2']['renter_city'],
                ]);
                
                // Get locale from booking language
                $locale = $booking->getLocaleFromLanguage();
                
                // Create rental agreement document only for long-term rentals
                if ($isLongTermRental && isset($formData['step3']['signature']) && $formData['step3']['signature']) {
                    $document1 = $this->documentService->createDocument(
                        $booking,
                        'rental_agreement',
                        $locale,
                        ['signature' => $formData['step3']['signature']]
                    );
                    $document1->update(['signed_at' => now()]);
                    GenerateDocumentPdf::dispatch($document1);
                    // NOTE: Admin will send this document manually, not automatically sent
                }
                
                // Create landlord confirmation document (Step 2) - in background, admin only
                // Document is created but not signed yet, admin will handle signature and sending
                $document2 = $this->documentService->createDocument(
                    $booking,
                    'landlord_confirmation',
                    $locale,
                    [] // No signature yet, admin will handle
                );
                GenerateDocumentPdf::dispatch($document2);
                // NOTE: Admin will sign and send this document manually
                
                // Create rent arrears document (Step 3) - in background, admin only
                // Document is created but not signed yet, admin will handle signature and sending
                $document3 = $this->documentService->createDocument(
                    $booking,
                    'rent_arrears',
                    $locale,
                    [] // No signature yet, admin will handle
                );
                GenerateDocumentPdf::dispatch($document3);
                // NOTE: Admin will sign and send this document manually
                
                // Process payment for short-term bookings
                if ($isShortTerm) {
                    if (!$request->payment_method_id) {
                        // Short-term booking requires payment
                        $booking->delete();
                        return back()->withErrors(['payment' => 'Payment is required for short-term bookings.'])->withInput();
                    }
                    
                    try {
                        // Retrieve the payment intent that was created on the frontend
                        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
                        
                        // The payment_method_id contains the payment intent ID
                        $paymentIntentId = $request->payment_method_id;
                        
                        // Retrieve the payment intent
                        $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);
                        
                        // Update booking with payment intent ID
                        $booking->update([
                            'stripe_payment_intent_id' => $paymentIntent->id,
                        ]);
                        
                        // Check if payment succeeded
                        if ($paymentIntent->status === 'succeeded') {
                            $this->paymentService->handleSuccessfulPayment($booking, $paymentIntent);
                        } else {
                            // Payment not completed - delete booking
                            $booking->delete();
                            return back()->withErrors(['payment' => 'Payment was not completed. Please try again.'])->withInput();
                        }
                    } catch (\Exception $e) {
                        // Delete booking if payment fails
                        if ($booking->exists) {
                            $booking->delete();
                        }
                        \Log::error('Payment processing failed: ' . $e->getMessage());
                        return back()->withErrors(['payment' => 'Payment processing failed: ' . $e->getMessage()])->withInput();
                    }
                }
                
                // Clear session
                session()->forget('booking_form_data');
                
                // Booking is complete after step 1 - redirect to completion page
                // Use 303 redirect to ensure proper redirect after POST
                return redirect()->route('booking.complete', ['booking' => $booking->id], 303)
                    ->with('success', __('booking.booking_submitted_successfully'));
                
            } catch (\Illuminate\Validation\ValidationException $e) {
                \Log::error('Booking validation failed: ' . $e->getMessage());
                return back()->withErrors($e->errors())->withInput();
            } catch (\Exception $e) {
                \Log::error('Booking creation failed: ' . $e->getMessage(), [
                    'exception' => $e,
                    'trace' => $e->getTraceAsString()
                ]);
                return back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()])->withInput();
            }
            
        } elseif ($step === 2) {
            $request->validate([
                'renter_address' => 'required|string|max:255',
                'renter_postal_code' => 'required|string|max:255',
                'renter_city' => 'required|string|max:255',
                'start_at' => 'required|date|after:yesterday',
                'end_at' => 'required|date|after:start_at',
            ]);
            
            // Check availability
            $startAt = Carbon::parse($request->start_at)->setTimezone('Europe/Berlin')->startOfDay();
            $endAt = Carbon::parse($request->end_at)->setTimezone('Europe/Berlin')->startOfDay();
            
            if (!$this->bookingService->isAvailable($room, $startAt, $endAt)) {
                return back()->withErrors(['dates' => 'The selected dates are not available.'])->withInput();
            }
            
            $formData['step2'] = $request->only([
                'renter_address',
                'renter_postal_code',
                'renter_city',
                'start_at',
                'end_at',
            ]);
            
        } elseif ($step === 3) {
            $request->validate([
                'signature' => 'required|string',
            ]);
            
            $formData['step3'] = $request->only(['signature']);
        }
        
        if ($step !== 1) {
            session(['booking_form_data' => $formData]);
        }
        
        if ($step < 3 && $step !== 1) {
            return redirect()->route('booking.form', ['room' => $room->id, 'step' => $step + 1]);
        }
        
        // All steps complete, redirect to completion
        if ($step === 3) {
            return redirect()->route('booking.form-complete', $room);
        }
    }

    /**
     * Complete form and create booking
     */
    public function completeForm(Request $request, Room $room)
    {
        $formData = session('booking_form_data', []);
        
        if (empty($formData['step1']) || empty($formData['step2']) || empty($formData['step3'])) {
            return redirect()->route('booking.form', ['room' => $room->id, 'step' => 1])
                ->withErrors(['error' => 'Please complete all steps.']);
        }
        
        try {
            // Get selected room first
            $selectedRoomId = $formData['step2']['room_id'] ?? $room->id;
            $selectedRoom = \App\Models\Room::findOrFail($selectedRoomId);
            
            $startAt = Carbon::parse($formData['step2']['start_at'])->setTimezone('Europe/Berlin')->startOfDay();
            $endAt = isset($formData['step2']['end_at']) && $formData['step2']['end_at'] 
                ? Carbon::parse($formData['step2']['end_at'])->setTimezone('Europe/Berlin')->startOfDay() 
                : null;
            
            // Re-check availability (only if end date is provided)
            if ($endAt && !$this->bookingService->isAvailable($selectedRoom, $startAt, $endAt)) {
                return redirect()->route('booking.form', ['room' => $selectedRoomId, 'step' => 1])
                    ->withErrors(['dates' => 'The selected dates are no longer available.']);
            } elseif (!$endAt) {
                // Long-term rental - check if room is available on check-in date
                $unavailableRoomIds = Booking::where('status', 'confirmed')
                    ->where('room_id', $selectedRoom->id)
                    ->where('start_at', '<=', $startAt->utc())
                    ->where('end_at', '>', $startAt->utc())
                    ->exists();
                
                if ($unavailableRoomIds) {
                    return redirect()->route('booking.form', ['room' => $selectedRoomId, 'step' => 1])
                        ->withErrors(['dates' => 'The selected date is not available.']);
                }
            }
            
            // Calculate total
            $totalAmount = $this->bookingService->calculateTotal($selectedRoom, $startAt, $endAt);
            $isShortTerm = $endAt && $selectedRoom->short_term_allowed && $startAt->diffInDays($endAt) <= 30;
            
            // Create booking
            $booking = Booking::create([
                'room_id' => $selectedRoomId,
                'start_at' => $startAt->utc(),
                'end_at' => $endAt ? $endAt->utc() : null,
                'source' => 'website',
                'status' => 'pending',
                'is_short_term' => $isShortTerm,
                'total_amount' => $totalAmount,
                'guest_first_name' => $formData['step1']['guest_first_name'],
                'guest_last_name' => $formData['step1']['guest_last_name'],
                'job' => $formData['step1']['job'],
                'language' => $formData['step1']['language'],
                'communication_preference' => $formData['step1']['communication_preference'],
                'email' => $formData['step1']['email'],
                'phone' => $formData['step1']['phone'],
                'renter_address' => $formData['step2']['renter_address'],
                'renter_postal_code' => $formData['step2']['renter_postal_code'],
                'renter_city' => $formData['step2']['renter_city'],
            ]);
            
            // Create rental agreement document only for long-term rentals
            if (!$isShortTerm && isset($formData['step3']['signature']) && $formData['step3']['signature']) {
                // Use booking language instead of app locale
                $locale = $booking->getLocaleFromLanguage();
                $document = $this->documentService->createDocument(
                    $booking,
                    'rental_agreement',
                    $locale,
                    ['signature' => $formData['step3']['signature']]
                );
                
                // Update with signature data
                $document->update([
                    'signed_at' => now(),
                    'signature_data' => ['signature' => $formData['step3']['signature']],
                ]);
                
                GenerateDocumentPdf::dispatch($document);
                SendDocumentEmail::dispatch($document, [$booking->email], true)->afterResponse();
            }
            
            // Send booking confirmation email
            try {
                Mail::to($booking->email)->send(new BookingConfirmation($booking));
                Log::info('Booking confirmation email sent', ['booking_id' => $booking->id, 'email' => $booking->email]);
            } catch (\Exception $e) {
                Log::error('Failed to send booking confirmation email: ' . $e->getMessage(), ['booking_id' => $booking->id]);
            }
            
            // Clear session
            session()->forget('booking_form_data');
            
            // Redirect to completion page
            return redirect()->route('booking.complete', ['booking' => $booking->id]);
            
        } catch (\Exception $e) {
            \Log::error('Booking creation failed: ' . $e->getMessage());
            return redirect()->route('booking.form', ['room' => $room->id, 'step' => 1])
                ->withErrors(['error' => 'An error occurred. Please try again.']);
        }
    }
}
