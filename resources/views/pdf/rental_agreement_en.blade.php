<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rental Agreement</title>
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
    <h1>RENTAL AGREEMENT</h1>
    
    <div class="section">
        <p><strong>Date:</strong> {{ \Carbon\Carbon::now()->format('d.m.Y') }}</p>
    </div>
    
    <div class="section">
        <p><strong>Landlord:</strong> {{ config('landlord.name', 'Martin Assies') }}<br>
        @if(config('landlord.address'))
            {{ config('landlord.address') }}<br>
        @endif
        @if(config('landlord.postal_code') || config('landlord.city'))
            {{ config('landlord.postal_code') }} {{ config('landlord.city') }}<br>
        @endif
        @if(config('landlord.phone'))
            Phone: {{ config('landlord.phone') }}<br>
        @endif
        @if(config('landlord.email'))
            Email: {{ config('landlord.email') }}
        @endif
        </p>
    </div>
    
    <div class="section">
        <p><strong>Tenant (Mieter):</strong> {{ $booking->guest_first_name }} {{ $booking->guest_last_name }}<br>
        Email: {{ $booking->email }}<br>
        Phone: {{ $booking->phone ?? 'N/A' }}</p>
    </div>
    
    <div class="section">
        <p><strong>Property:</strong> {{ $booking->room->name }}<br>
        Address: {{ $booking->room->property->address }}, {{ $booking->room->property->city }}</p>
    </div>
    
    <div class="section">
        <p><strong>Rental Period:</strong><br>
        Check-in: {{ \Carbon\Carbon::parse($booking->start_at)->format('d.m.Y') }}<br>
        Check-out: {{ \Carbon\Carbon::parse($booking->end_at)->format('d.m.Y') }}</p>
    </div>
    
    <div class="section">
        <p><strong>Rental Amount:</strong> €{{ number_format($booking->total_amount, 2) }}</p>
    </div>
    
    <div class="section">
        <p>By signing this agreement, both parties agree to the terms and conditions outlined above.</p>
    </div>
    
    <div class="signature-section">
        <div class="signature-box">
            <p><strong>Landlord</strong></p>
            <p>{{ config('landlord.name', 'Martin Assies') }}</p>
            @if(isset($landlordSignature))
                <img src="{{ $landlordSignature }}" class="signature-image" alt="Landlord Signature">
            @endif
            <p>_________________________</p>
            <p>Date: _________________</p>
        </div>
        <div class="signature-box">
            <p><strong>Tenant</strong></p>
            @if($document && $document->signature_data && isset($document->signature_data['signature']))
                <img src="{{ $document->signature_data['signature'] }}" class="signature-image" alt="Tenant Signature">
            @endif
            <p>_________________________</p>
            <p>Date: {{ $document && $document->signed_at ? \Carbon\Carbon::parse($document->signed_at)->format('d.m.Y') : '' }}</p>
        </div>
    </div>
</body>
</html>

