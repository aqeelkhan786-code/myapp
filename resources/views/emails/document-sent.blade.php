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
    <div class="container">
        <div class="header">
            <h1>Document Sent</h1>
        </div>
        <div class="content">
            <p>Dear {{ $booking->guest_first_name }},</p>
            
            <p>Please find attached the {{ ucfirst(str_replace('_', ' ', $document->doc_type)) }} for your booking.</p>
            
            <p><strong>Booking Details:</strong></p>
            <ul>
                <li>Room: {{ $booking->room->name }}</li>
                <li>Check-in: {{ \Carbon\Carbon::parse($booking->start_at)->format('d.m.Y') }}</li>
                <li>Check-out: {{ \Carbon\Carbon::parse($booking->end_at)->format('d.m.Y') }}</li>
            </ul>
            
            <p>Best regards,<br>MaRoom Booking System</p>
        </div>
        <div class="footer">
            <p>This is an automated email. Please do not reply.</p>
        </div>
    </div>
</body>
</html>

