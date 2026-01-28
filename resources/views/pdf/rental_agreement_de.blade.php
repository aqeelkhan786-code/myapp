<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Mietvertrag</title>
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
        <h1>MIETVERTRAG</h1>
        <div class="section" style="text-align: right;">
            <p><strong>Datum:</strong> {{ \Carbon\Carbon::now()->format('d.m.Y') }}</p>
        </div>
    </div>
    
    <div class="section">
        <div class="party-section">
            <div class="party-title">Vermieter:</div>
            <div class="party-details">
                <p><strong>{{ config('landlord.name', 'Martin Assies') }}</strong><br>
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
        </div>
        
        <div class="party-section">
            <div class="party-title">Mieter:</div>
            <div class="party-details">
                <p><strong>{{ $booking->guest_first_name }} {{ $booking->guest_last_name }}</strong><br>
                @if($booking->renter_address)
                    {{ $booking->renter_address }}<br>
                @endif
                @if($booking->renter_postal_code || $booking->renter_city)
                    {{ $booking->renter_postal_code }} {{ $booking->renter_city }}<br>
                @endif
                E-Mail: {{ $booking->email }}<br>
                @if($booking->phone)
                    Telefon: {{ $booking->phone }}<br>
                @endif
                @if($booking->job)
                    Beruf: {{ $booking->job }}
                @endif
                </p>
            </div>
        </div>
    </div>
    
    <div class="section">
        <h2>§ 1 Mietobjekt</h2>
        <div class="property-details">
            <p><strong>Wohnung/Raum:</strong> {{ $booking->room->name }}<br>
            <strong>Adresse:</strong> {{ $booking->room->property->address }}, {{ $booking->room->property->postal_code }} {{ $booking->room->property->city }}</p>
            @if($booking->room->description)
                <p><strong>Beschreibung:</strong> {{ $booking->room->description }}</p>
            @endif
        </div>
    </div>
    
    <div class="section">
        <h2>§ 2 Mietzeitraum</h2>
        <div class="clause">
            <p>Der Mietvertrag beginnt am <strong>{{ \Carbon\Carbon::parse($booking->start_at)->format('d.m.Y') }}</strong> (Einzugsdatum).</p>
            @if($booking->end_at)
                <p>Das Mietverhältnis endet am <strong>{{ \Carbon\Carbon::parse($booking->end_at)->format('d.m.Y') }}</strong> (Auszugsdatum).</p>
            @else
                <p>Das Mietverhältnis ist auf unbestimmte Zeit geschlossen und kann mit einer Frist von {{ $booking->is_short_term ? '2 Wochen' : '3 Monaten' }} gekündigt werden.</p>
            @endif
        </div>
    </div>
    
    <div class="section">
        <h2>§ 3 Mietzins</h2>
        <div class="clause">
            <p>Der monatliche Mietzins beträgt <strong>€{{ number_format($booking->total_amount, 2, ',', '.') }}</strong></p>
            @if($booking->is_short_term)
                <p>Der Gesamtmietzins für den Mietzeitraum beträgt <strong>€{{ number_format($booking->total_amount, 2, ',', '.') }}</strong></p>
            @endif
            <p>Die Miete ist monatlich im Voraus bis zum 3. Werktag des jeweiligen Monats fällig und auf das vom Vermieter angegebene Konto zu überweisen.</p>
        </div>
    </div>
    
    <div class="section">
        <h2>§ 4 Nebenkosten</h2>
        <div class="clause">
            <p>Nebenkosten sind im Mietzins enthalten bzw. werden gesondert abgerechnet gemäß der Betriebskostenverordnung.</p>
        </div>
    </div>
    
    <div class="section">
        <h2>§ 5 Kaution</h2>
        <div class="clause">
            <p>Der Mieter verpflichtet sich, eine Kaution in Höhe von einem Monatsmietzins zu hinterlegen. Die Kaution dient zur Sicherung aller Ansprüche des Vermieters aus dem Mietverhältnis.</p>
        </div>
    </div>
    
    <div class="section">
        <h2>§ 6 Pflichten des Mieters</h2>
        <div class="clause">
            <p>Der Mieter ist verpflichtet:</p>
            <ul class="terms-list">
                <li>die gemieteten Räume sorgfältig zu behandeln und vor Schäden zu bewahren,</li>
                <li>die Räume nur zu Wohnzwecken zu nutzen,</li>
                <li>den Vermieter unverzüglich über Mängel und Schäden zu informieren,</li>
                <li>die Räume am Ende des Mietverhältnisses im vertragsgemäßen Zustand zu übergeben,</li>
                <li>die Hausordnung zu beachten und die Ruhezeiten einzuhalten.</li>
            </ul>
        </div>
    </div>
    
    <div class="section">
        <h2>§ 7 Kündigung</h2>
        <div class="clause">
            <p>Die Kündigung bedarf der Schriftform. Die gesetzlichen Kündigungsfristen bleiben unberührt.</p>
        </div>
    </div>
    
    <div class="section">
        <h2>§ 8 Schlussbestimmungen</h2>
        <div class="clause">
            <p>Änderungen und Ergänzungen dieses Vertrages bedürfen der Schriftform. Sollten einzelne Bestimmungen dieses Vertrages unwirksam sein oder werden, so bleibt die Wirksamkeit des Vertrages im Übrigen unberührt.</p>
            <p>Es gilt deutsches Recht. Gerichtsstand ist der Wohnsitz des Vermieters.</p>
        </div>
    </div>
    
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">
                <p><strong>Vermieter</strong></p>
                <p>{{ config('landlord.name', 'Martin Assies') }}</p>
                @if(isset($landlordSignature))
                    <img src="{{ $landlordSignature }}" class="signature-image" alt="Vermieter Unterschrift">
                    <div class="date-info">
                        <p>Datum: _________________</p>
                    </div>
                @else
                    <div class="date-info">
                        <p>_________________________</p>
                        <p>Datum: _________________</p>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="signature-box">
            <div class="signature-line">
                <p><strong>Mieter</strong></p>
                <p>{{ $booking->guest_first_name }} {{ $booking->guest_last_name }}</p>
                @if($document && $document->signature_data && isset($document->signature_data['signature']))
                    <img src="{{ $document->signature_data['signature'] }}" class="signature-image" alt="Mieter Unterschrift">
                @endif
                <div class="date-info">
                    <p>_________________________</p>
                    <p>Datum: {{ $document && $document->signed_at ? \Carbon\Carbon::parse($document->signed_at)->format('d.m.Y') : '' }}</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
