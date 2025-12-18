<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Mietschuldsbefreiung</title>
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
    <h1>MIETSCHULDSBEFREIUNG</h1>
    
    <div class="section">
        <p><strong>Datum:</strong> {{ \Carbon\Carbon::now()->format('d.m.Y') }}</p>
    </div>
    
    <div class="section">
        <p>Hiermit bestätige ich, dass:</p>
        <p><strong>{{ $booking->guest_first_name }} {{ $booking->guest_last_name }}</strong><br>
        wohnhaft: {{ $booking->room->property->address }}, {{ $booking->room->property->city }}<br>
        Zimmer/Wohnung: {{ $booking->room->name }}</p>
    </div>
    
    <div class="section">
        <p>keine ausstehenden Mietrückstände zum Stand vom {{ \Carbon\Carbon::now()->format('d.m.Y') }} hat.</p>
    </div>
    
    <div class="section">
        <p>Alle Mietzahlungen wurden vollständig und fristgerecht für den Zeitraum vom {{ \Carbon\Carbon::parse($booking->start_at)->format('d.m.Y') }} bis {{ \Carbon\Carbon::parse($booking->end_at)->format('d.m.Y') }} geleistet.</p>
    </div>
    
    <div class="signature-section">
        <div class="signature-box">
            <p><strong>Vermieter</strong></p>
            <p>{{ config('landlord.name', 'Martin Assies') }}</p>
            @if(isset($landlordSignature))
                <img src="{{ $landlordSignature }}" class="signature-image" alt="Vermieter Unterschrift">
            @endif
            <p>_________________________</p>
            <p>Datum: _________________</p>
        </div>
    </div>
</body>
</html>

