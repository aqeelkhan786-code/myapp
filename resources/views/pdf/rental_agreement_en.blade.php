<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rental Agreement</title>
    <style>
        @page {
            margin: 2cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #000;
        }
        h1 {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 30px;
            text-transform: uppercase;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        h2 {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        .header-section {
            margin-bottom: 25px;
        }
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .party-section {
            margin-bottom: 20px;
        }
        .party-title {
            font-weight: bold;
            font-size: 12pt;
            margin-bottom: 10px;
        }
        .party-details {
            margin-left: 20px;
            line-height: 1.8;
        }
        .clause {
            margin-bottom: 15px;
            text-align: justify;
        }
        .clause-number {
            font-weight: bold;
        }
        .signature-section {
            margin-top: 50px;
            page-break-inside: avoid;
        }
        .signature-box {
            width: 45%;
            display: inline-block;
            vertical-align: top;
            margin-right: 5%;
        }
        .signature-box:last-child {
            margin-right: 0;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 60px;
            padding-top: 5px;
            text-align: center;
        }
        .signature-image {
            max-width: 200px;
            max-height: 80px;
            margin-bottom: 10px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .date-info {
            margin-top: 5px;
            font-size: 10pt;
        }
        .property-details {
            background-color: #f5f5f5;
            padding: 15px;
            border: 1px solid #ddd;
            margin: 15px 0;
        }
        .terms-section {
            margin-top: 30px;
        }
        .terms-list {
            margin-left: 20px;
            margin-bottom: 15px;
        }
        .terms-list li {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="header-section">
        <h1>RENTAL AGREEMENT</h1>
        <div class="section" style="text-align: right;">
            <p><strong>Date:</strong> {{ \Carbon\Carbon::now()->format('d.m.Y') }}</p>
        </div>
    </div>
    
    <div class="section">
        <div class="party-section">
            <div class="party-title">Landlord:</div>
            <div class="party-details">
                <p><strong>{{ config('landlord.name', 'Martin Assies') }}</strong><br>
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
        </div>
        
        <div class="party-section">
            <div class="party-title">Tenant:</div>
            <div class="party-details">
                <p><strong>{{ $booking->guest_first_name }} {{ $booking->guest_last_name }}</strong><br>
                @if($booking->renter_address)
                    {{ $booking->renter_address }}<br>
                @endif
                @if($booking->renter_postal_code || $booking->renter_city)
                    {{ $booking->renter_postal_code }} {{ $booking->renter_city }}<br>
                @endif
                Email: {{ $booking->email }}<br>
                @if($booking->phone)
                    Phone: {{ $booking->phone }}<br>
                @endif
                @if($booking->job)
                    Occupation: {{ $booking->job }}
                @endif
                </p>
            </div>
        </div>
    </div>
    
    <div class="section">
        <h2>§ 1 Rental Property</h2>
        <div class="property-details">
            <p><strong>Room/Apartment:</strong> {{ $booking->room->name }}<br>
            <strong>Address:</strong> {{ $booking->room->property->address }}, {{ $booking->room->property->postal_code }} {{ $booking->room->property->city }}</p>
            @if($booking->room->description)
                <p><strong>Description:</strong> {{ $booking->room->description }}</p>
            @endif
        </div>
    </div>
    
    <div class="section">
        <h2>§ 2 Rental Period</h2>
        <div class="clause">
            <p>The rental agreement begins on <strong>{{ \Carbon\Carbon::parse($booking->start_at)->format('d.m.Y') }}</strong> (move-in date).</p>
            @if($booking->end_at)
                <p>The tenancy ends on <strong>{{ \Carbon\Carbon::parse($booking->end_at)->format('d.m.Y') }}</strong> (move-out date).</p>
            @else
                <p>The tenancy is for an indefinite period and can be terminated with a notice period of {{ $booking->is_short_term ? '2 weeks' : '3 months' }}.</p>
            @endif
        </div>
    </div>
    
    <div class="section">
        <h2>§ 3 Rent</h2>
        <div class="clause">
            <p>The monthly rent amounts to <strong>€{{ number_format($booking->total_amount, 2, ',', '.') }}</strong></p>
            @if($booking->is_short_term)
                <p>The total rent for the rental period amounts to <strong>€{{ number_format($booking->total_amount, 2, ',', '.') }}</strong></p>
            @endif
            <p>Rent is due monthly in advance by the 3rd working day of each month and shall be transferred to the account specified by the landlord.</p>
        </div>
    </div>
    
    <div class="section">
        <h2>§ 4 Additional Costs</h2>
        <div class="clause">
            <p>Additional costs are included in the rent or will be separately invoiced according to the operating costs regulation.</p>
        </div>
    </div>
    
    <div class="section">
        <h2>§ 5 Security Deposit</h2>
        <div class="clause">
            <p>The tenant agrees to provide a security deposit in the amount of one month's rent. The security deposit serves to secure all claims of the landlord arising from the tenancy.</p>
        </div>
    </div>
    
    <div class="section">
        <h2>§ 6 Tenant Obligations</h2>
        <div class="clause">
            <p>The tenant is obligated to:</p>
            <ul class="terms-list">
                <li>treat the rented premises carefully and protect them from damage,</li>
                <li>use the premises only for residential purposes,</li>
                <li>inform the landlord immediately of any defects and damage,</li>
                <li>return the premises in the condition specified in the contract at the end of the tenancy,</li>
                <li>observe house rules and respect quiet hours.</li>
            </ul>
        </div>
    </div>
    
    <div class="section">
        <h2>§ 7 Termination</h2>
        <div class="clause">
            <p>Termination must be in writing. The statutory notice periods remain unaffected.</p>
        </div>
    </div>
    
    <div class="section">
        <h2>§ 8 Final Provisions</h2>
        <div class="clause">
            <p>Changes and additions to this contract must be in writing. If individual provisions of this contract are or become invalid, the validity of the contract shall otherwise remain unaffected.</p>
            <p>German law applies. The place of jurisdiction is the landlord's place of residence.</p>
        </div>
    </div>
    
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">
                <p><strong>Landlord</strong></p>
                <p>{{ config('landlord.name', 'Martin Assies') }}</p>
                @if(isset($landlordSignature))
                    <img src="{{ $landlordSignature }}" class="signature-image" alt="Landlord Signature">
                    <div class="date-info">
                        <p>Date: _________________</p>
                    </div>
                @else
                    <div class="date-info">
                        <p>_________________________</p>
                        <p>Date: _________________</p>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="signature-box">
            <div class="signature-line">
                <p><strong>Tenant</strong></p>
                <p>{{ $booking->guest_first_name }} {{ $booking->guest_last_name }}</p>
                @if($document && $document->signature_data && isset($document->signature_data['signature']))
                    <img src="{{ $document->signature_data['signature'] }}" class="signature-image" alt="Tenant Signature">
                @endif
                <div class="date-info">
                    <p>_________________________</p>
                    <p>Date: {{ $document && $document->signed_at ? \Carbon\Carbon::parse($document->signed_at)->format('d.m.Y') : '' }}</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
