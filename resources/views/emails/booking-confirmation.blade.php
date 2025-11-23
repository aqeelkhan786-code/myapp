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
            background-color: #10b981;
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
            <h1>Booking Confirmed!</h1>
        </div>
        <div class="content">
            <p>Dear {{ $booking->guest_first_name }},</p>
            
            <p>Your booking has been confirmed. We look forward to hosting you!</p>
            
            <p><strong>Booking Details:</strong></p>
            <ul>
                <li>Room: {{ $booking->room->name }}</li>
                <li>Check-in: {{ \Carbon\Carbon::parse($booking->start_at)->format('d.m.Y') }}</li>
                <li>Check-out: {{ \Carbon\Carbon::parse($booking->end_at)->format('d.m.Y') }}</li>
                <li>Total Amount: €{{ number_format($booking->total_amount, 2) }}</li>
            </ul>
            
            <p>Best regards,<br>MaRoom Booking System</p>
        </div>
        <div class="footer">
            <p>This is an automated email. Please do not reply.</p>
        </div>
    </div>
</body>
</html>

