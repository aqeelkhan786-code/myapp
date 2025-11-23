<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Booking;
use App\Models\Document;
use App\Services\BookingService;
use App\Services\DocumentService;
use App\Services\IcalService;
use App\Services\PaymentService;
use App\Jobs\GenerateDocumentPdf;
use App\Jobs\SendDocumentEmail;
use App\Mail\BookingConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
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
    public function index()
    {
        $rooms = Room::with('images')->get();
        
        return view('booking.index', compact('rooms'));
    }

    /**
     * Show room details with calendar
     */
    public function show(Room $room)
    {
        // Ensure room exists
        if (!$room->exists) {
            abort(404, 'Room not found');
        }
        
        $room->load('images', 'property');
        
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
     */
    public function step(Booking $booking, int $step)
    {
        $booking->load('room', 'documents');
        
        if ($step < 1 || $step > 3) {
            abort(404);
        }

        return view('booking.step', compact('booking', 'step'));
    }

    /**
     * Save booking step data
     */
    public function saveStep(Request $request, Booking $booking, int $step)
    {
        if ($step === 1) {
            $request->validate([
                'guest_first_name' => 'required|string|max:255',
                'guest_last_name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'postal_code' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
            ]);

            $booking->update($request->only([
                'guest_first_name',
                'guest_last_name',
                'email',
                'phone',
                'notes',
            ]));

            // Store address in notes if provided
            if ($request->address || $request->city || $request->postal_code) {
                $address = implode(', ', array_filter([
                    $request->address,
                    $request->city,
                    $request->postal_code,
                ]));
                $booking->update(['notes' => ($booking->notes ? $booking->notes . "\n\n" : '') . "Address: {$address}"]);
            }

            // Create rental agreement document and generate PDF
            $document = $this->documentService->createDocument($booking, 'rental_agreement', app()->getLocale());
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
        $document = $this->documentService->createDocument(
            $booking, 
            $docType, 
            app()->getLocale(),
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

        return redirect()->route('booking.complete', ['booking' => $booking->id])->with('success', 'Booking completed successfully!');
    }

    /**
     * Process payment for short-term booking
     */
    public function processPayment(Request $request, Booking $booking)
    {
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
                    ->with('success', 'Payment processed successfully!');
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
        $booking->load('room', 'documents');
        return view('booking.complete', compact('booking'));
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
}
