<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate of Rent Arrears</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            margin: 40px;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
        }
        .section {
            margin-bottom: 20px;
        }
        .signature-section {
            margin-top: 60px;
            text-align: center;
        }
        .signature-box {
            width: 45%;
            margin: 0 auto;
            border-top: 1px solid #000;
            padding-top: 10px;
            text-align: center;
        }
        .signature-image {
            max-width: 200px;
            max-height: 80px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h1>CERTIFICATE OF RENT ARREARS<br>(Mietschuldsbefreiung)</h1>
    
    <div class="section">
        <p><strong>Date:</strong> {{ \Carbon\Carbon::now()->format('d.m.Y') }}</p>
    </div>
    
    <div class="section">
        <p>I hereby confirm that:</p>
        <p><strong>{{ $booking->guest_first_name }} {{ $booking->guest_last_name }}</strong><br>
        residing at: {{ $booking->room->property->address }}, {{ $booking->room->property->city }}<br>
        Room/Apartment: {{ $booking->room->name }}</p>
    </div>
    
    <div class="section">
        <p>has no outstanding rent arrears as of {{ \Carbon\Carbon::now()->format('d.m.Y') }}.</p>
    </div>
    
    <div class="section">
        <p>All rental payments have been made in full and on time for the period from {{ \Carbon\Carbon::parse($booking->start_at)->format('d.m.Y') }} to {{ \Carbon\Carbon::parse($booking->end_at)->format('d.m.Y') }}.</p>
    </div>
    
    <div class="signature-section">
        <div class="signature-box">
            <p><strong>Landlord</strong></p>
            @if(isset($landlordSignature))
                <img src="{{ $landlordSignature }}" class="signature-image" alt="Landlord Signature">
            @endif
            <p>_________________________</p>
            <p>Date: _________________</p>
        </div>
    </div>
</body>
</html>

