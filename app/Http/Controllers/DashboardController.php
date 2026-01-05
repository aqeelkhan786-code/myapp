<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\CheckInPdfsSent;

class DashboardController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->middleware('auth');
        $this->bookingService = $bookingService;
    }

    public function index()
    {
        $today = Carbon::today('Europe/Berlin');
        $nextWeek = $today->copy()->addWeek();
        $nextMonth = $today->copy()->addMonth();

        // Upcoming arrivals (next 7 days)
        $arrivals = Booking::with(['room', 'room.property'])
            ->where('status', 'confirmed')
            ->whereBetween('start_at', [
                $today->startOfDay()->utc(),
                $today->copy()->addDays(7)->endOfDay()->utc()
            ])
            ->orderBy('start_at', 'asc')
            ->get();

        // Check for conflicts (optimized to avoid N+1 queries)
        $conflicts = [];
        $allBookings = Booking::where('status', 'confirmed')
            ->where('start_at', '>=', $today->startOfDay()->utc())
            ->with(['room'])
            ->get();

        if ($allBookings->isNotEmpty()) {
            // Get all booking IDs and room IDs
            $bookingIds = $allBookings->pluck('id')->toArray();
            $roomIds = $allBookings->pluck('room_id')->unique()->toArray();

            // Fetch all potential conflicting bookings in a single query
            $potentialConflicts = Booking::where('status', 'confirmed')
                ->where('start_at', '>=', $today->startOfDay()->utc())
                ->whereIn('room_id', $roomIds)
                ->with(['room'])
                ->get()
                ->groupBy('room_id');

            // Check for conflicts efficiently
            foreach ($allBookings as $booking) {
                $roomBookings = $potentialConflicts->get($booking->room_id, collect());
                
                $conflicting = $roomBookings->first(function ($otherBooking) use ($booking) {
                    return $otherBooking->id !== $booking->id
                        && $otherBooking->start_at < $booking->end_at
                        && $otherBooking->end_at > $booking->start_at;
                });

                if ($conflicting) {
                    $conflicts[] = [
                        'booking1' => $booking,
                        'booking2' => $conflicting,
                    ];
                }
            }
        }

        // Stats
        $totalBookings = Booking::count();
        $confirmedBookings = Booking::where('status', 'confirmed')->count();
        $pendingBookings = Booking::where('status', 'pending')->count();
        $totalRevenue = Booking::where('payment_status', 'paid')->sum('paid_amount');

        // Get available check-in PDFs
        $checkInPdfs = [
            'Check in Haus Hoppe.pdf',
            'Check In Haus Rosa OG.pdf',
            'Check In Haus Rosa Room 7.pdf',
            'Check In L 1-3 (1).pdf',
            'Check In L 4-6.pdf',
            'Check In L 7-8.pdf',
            'Check In L 9-11.pdf',
        ];
        
        $availablePdfs = [];
        foreach ($checkInPdfs as $pdf) {
            $path = 'public/check-in-pdfs/' . $pdf;
            if (Storage::exists($path)) {
                $availablePdfs[] = [
                    'name' => $pdf,
                    'path' => $path,
                ];
            }
        }

        return view('dashboard', compact(
            'arrivals',
            'conflicts',
            'totalBookings',
            'confirmedBookings',
            'pendingBookings',
            'totalRevenue',
            'availablePdfs'
        ));
    }

    /**
     * Download a check-in PDF
     */
    public function downloadCheckInPdf(Request $request, $pdf)
    {
        $pdfName = base64_decode($pdf);
        $path = 'public/check-in-pdfs/' . $pdfName;
        
        if (!Storage::exists($path)) {
            abort(404, 'PDF not found');
        }
        
        return Storage::download($path, $pdfName);
    }

    /**
     * Send check-in PDF(s) via email
     */
    public function sendCheckInPdfs(Request $request)
    {
        $request->validate([
            'recipient_email' => 'required|email',
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string',
            'pdf_name' => 'nullable|string', // Base64 encoded PDF name for single PDF
        ]);

        $pdfPaths = [];
        
        // If pdf_name is provided, send only that PDF
        if ($request->has('pdf_name') && !empty($request->pdf_name)) {
            $pdfName = base64_decode($request->pdf_name);
            $path = 'public/check-in-pdfs/' . $pdfName;
            
            if (!Storage::exists($path)) {
                return back()->withErrors(['error' => 'PDF not found: ' . $pdfName])->withInput();
            }
            
            $pdfPaths[] = $path;
        } else {
            // Otherwise, send all available check-in PDFs
            $checkInPdfs = [
                'Check in Haus Hoppe.pdf',
                'Check In Haus Rosa OG.pdf',
                'Check In Haus Rosa Room 7.pdf',
                'Check In L 1-3 (1).pdf',
                'Check In L 4-6.pdf',
                'Check In L 7-8.pdf',
                'Check In L 9-11.pdf',
            ];
            
            foreach ($checkInPdfs as $pdf) {
                $path = 'public/check-in-pdfs/' . $pdf;
                if (Storage::exists($path)) {
                    $pdfPaths[] = $path;
                }
            }
        }

        if (empty($pdfPaths)) {
            return back()->withErrors(['error' => 'No check-in PDFs found to send.'])->withInput();
        }

        try {
            // Get default message based on locale
            $defaultMessage = app()->getLocale() === 'de' 
                ? 'Sehr geehrter Kunde,

Bitte finden Sie die Check-in Informationen im Anhang' . (count($pdfPaths) > 1 ? '' : '') . '.

Mit freundlichen Grüßen,
MaRoom Team'
                : 'Dear customer,

Please find attached the check-in information document' . (count($pdfPaths) > 1 ? 's' : '') . '.

Best regards,
MaRoom Team';

            $message = $request->input('message', $defaultMessage);
            
            // Get default subject based on locale
            $defaultSubject = app()->getLocale() === 'de' 
                ? 'Check-in Informationen - MaRoom'
                : 'Check-in Information - MaRoom';
            
            $subject = $request->input('subject', $defaultSubject);

            Mail::to($request->recipient_email)
                ->send(new CheckInPdfsSent($message, $pdfPaths, $subject));

            $pdfCount = count($pdfPaths);
            $successMessage = $pdfCount === 1 
                ? 'Check-in PDF has been sent successfully to ' . $request->recipient_email
                : 'Check-in PDFs have been sent successfully to ' . $request->recipient_email;

            return back()->with('success', $successMessage);
        } catch (\Exception $e) {
            \Log::error('Failed to send check-in PDFs', [
                'email' => $request->recipient_email,
                'error' => $e->getMessage(),
            ]);
            
            return back()->withErrors(['error' => 'Failed to send email: ' . $e->getMessage()])->withInput();
        }
    }
}
