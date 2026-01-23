<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #2563eb;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            background-color: #f9fafb;
            padding: 20px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-size: 12px;
        }
    </style>
</head>
<body>
    @php $loc = $locale ?? app()->getLocale(); @endphp
    <div class="container">
        <div class="header">
            <h1>{{ __('booking.document_sent_title', [], $loc) }}</h1>
        </div>
        <div class="content">
            <p>{{ __('booking.email_dear', [], $loc) }} {{ $booking->guest_first_name }},</p>
            
            @php
                $docTypeKeys = [
                    'rental_agreement' => __('booking.rental_agreement', [], $loc),
                    'landlord_confirmation' => __('booking.landlord_confirmation', [], $loc),
                    'rent_arrears' => __('booking.rent_arrears_certificate', [], $loc),
                ];
                $doctype = $docTypeKeys[$document->doc_type] ?? ucfirst(str_replace('_', ' ', $document->doc_type));
            @endphp
            <p>{{ __('booking.document_sent_attached', ['doctype' => $doctype], $loc) }}</p>
            
            <p><strong>{{ __('booking.booking_details', [], $loc) }}:</strong></p>
            <ul>
                <li>{{ __('booking.room', [], $loc) }}: {{ $booking->room->name }}</li>
                <li>{{ __('booking.check_in', [], $loc) }}: {{ \Carbon\Carbon::parse($booking->start_at)->format('d.m.Y') }}</li>
                <li>{{ __('booking.check_out', [], $loc) }}: {{ \Carbon\Carbon::parse($booking->end_at)->format('d.m.Y') }}</li>
            </ul>
            
            <p>{{ __('booking.document_sent_best_regards', [], $loc) }}<br>{{ __('booking.document_sent_signoff', [], $loc) }}</p>
        </div>
        <div class="footer">
            <p>{{ __('booking.document_sent_automated', [], $loc) }}</p>
        </div>
    </div>
</body>
</html>

