<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Wohnungsgeberbescheinigung</title>
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
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
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
    <h1>WOHNUNGSGEBERBESCHEINIGUNG</h1>
    
    <div class="section">
        <p><strong>Datum:</strong> {{ \Carbon\Carbon::now()->format('d.m.Y') }}</p>
    </div>
    
    <div class="section">
        <p>Hiermit bestätige ich, dass:</p>
        <p><strong>{{ $booking->guest_first_name }} {{ $booking->guest_last_name }}</strong><br>
        (Geburtsdatum: _______________)<br>
        unter folgender Adresse gemeldet ist:</p>
    </div>
    
    <div class="section">
        <p><strong>Adresse:</strong> {{ $booking->room->property->address }}, {{ $booking->room->property->city }}<br>
        <strong>Zimmer/Wohnung:</strong> {{ $booking->room->name }}</p>
    </div>
    
    <div class="section">
        <p><strong>Meldezeitraum:</strong><br>
        Von: {{ \Carbon\Carbon::parse($booking->start_at)->format('d.m.Y') }}<br>
        Bis: {{ \Carbon\Carbon::parse($booking->end_at)->format('d.m.Y') }}</p>
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
        <div class="signature-box">
            <p><strong>Mieter</strong></p>
            @if($document && $document->signature_data && isset($document->signature_data['signature']))
                <img src="{{ $document->signature_data['signature'] }}" class="signature-image" alt="Mieter Unterschrift">
            @endif
            <p>_________________________</p>
            <p>Datum: {{ $document && $document->signed_at ? \Carbon\Carbon::parse($document->signed_at)->format('d.m.Y') : '' }}</p>
        </div>
    </div>
</body>
</html>

