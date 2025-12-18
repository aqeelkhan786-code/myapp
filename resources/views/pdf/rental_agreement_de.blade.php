<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Mietvertrag</title>
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
    <h1>MIETVERTRAG</h1>
    
    <div class="section">
        <p><strong>Datum:</strong> {{ \Carbon\Carbon::now()->format('d.m.Y') }}</p>
    </div>
    
    <div class="section">
        <p><strong>Vermieter:</strong> {{ config('landlord.name', 'Martin Assies') }}<br>
        @if(config('landlord.address'))
            {{ config('landlord.address') }}<br>
        @endif
        @if(config('landlord.postal_code') || config('landlord.city'))
            {{ config('landlord.postal_code') }} {{ config('landlord.city') }}<br>
        @endif
        @if(config('landlord.phone'))
            Telefon: {{ config('landlord.phone') }}<br>
        @endif
        @if(config('landlord.email'))
            E-Mail: {{ config('landlord.email') }}
        @endif
        </p>
    </div>
    
    <div class="section">
        <p><strong>Mieter:</strong> {{ $booking->guest_first_name }} {{ $booking->guest_last_name }}<br>
        E-Mail: {{ $booking->email }}<br>
        Telefon: {{ $booking->phone ?? 'N/A' }}</p>
    </div>
    
    <div class="section">
        <p><strong>Wohnung:</strong> {{ $booking->room->name }}<br>
        Adresse: {{ $booking->room->property->address }}, {{ $booking->room->property->city }}</p>
    </div>
    
    <div class="section">
        <p><strong>Mietzeitraum:</strong><br>
        Einzug: {{ \Carbon\Carbon::parse($booking->start_at)->format('d.m.Y') }}<br>
        Auszug: {{ \Carbon\Carbon::parse($booking->end_at)->format('d.m.Y') }}</p>
    </div>
    
    <div class="section">
        <p><strong>Mietbetrag:</strong> €{{ number_format($booking->total_amount, 2) }}</p>
    </div>
    
    <div class="section">
        <p>Durch die Unterschrift dieses Vertrags erklären sich beide Parteien mit den oben genannten Bedingungen einverstanden.</p>
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

