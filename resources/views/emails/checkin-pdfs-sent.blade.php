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
            <h1>{{ __('booking.checkin_email_header', [], $locale ?? app()->getLocale()) }}</h1>
        </div>
        <div class="content">
            {!! nl2br(e($emailMessage)) !!}
        </div>
        <div class="footer">
            <p>{{ __('booking.checkin_email_footer', [], $locale ?? app()->getLocale()) }}</p>
        </div>
    </div>
</body>
</html>

