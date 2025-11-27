<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('booking_flow.ma_room_title') }} - {{ config('app.name', 'Laravel') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&family=playfair:400,500,600,700&display=swap" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Figtree', sans-serif;
        }
        
        /* Header Styles */
        .header-top-strip {
            background-color: #f5f5dc;
            height: 3px;
            width: 100%;
        }
        
        .header {
            background-color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .logo-ma {
            font-size: 1.2rem;
            font-weight: 400;
            color: #7cb342;
            line-height: 1;
        }
        
        .logo-room {
            font-size: 2rem;
            font-weight: 600;
            color: #2e7d32;
            font-family: 'Playfair Display', serif;
            line-height: 1;
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            color: #2e7d32;
        }
        
        .nav-home {
            color: #7cb342;
            text-decoration: none;
            font-weight: 500;
            font-size: 1rem;
            transition: color 0.3s;
        }
        
        .nav-home:hover {
            color: #558b2f;
        }
        
        /* Main Content with Map Background */
        .main-content {
            position: relative;
            min-height: calc(100vh - 80px);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            background-image: url('https://images.unsplash.com/photo-1524661135-423995f22d0b?w=1920&h=1080&fit=crop');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        
        /* Dark overlay for better text readability */
        .main-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 0;
        }
        
        .content-overlay {
            position: relative;
            z-index: 1;
            text-align: center;
            color: white;
            max-width: 800px;
        }
        
        .ma-room-title {
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 2rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            letter-spacing: 0.1em;
        }
        
        .description-text {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 1rem;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }
        
        .language-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .lang-button {
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .lang-button-deutsch {
            background-color: #4caf50;
            color: white;
        }
        
        .lang-button-deutsch:hover {
            background-color: #45a049;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .lang-button-englisch {
            background-color: #000000;
            color: white;
        }
        
        .lang-button-englisch:hover {
            background-color: #333333;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .lang-button.active {
            opacity: 0.9;
        }
        
        @media (max-width: 768px) {
            .ma-room-title {
                font-size: 2.5rem;
            }
            
            .description-text {
                font-size: 0.95rem;
            }
            
            .header {
                padding: 1rem;
            }
            
            .logo-room {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-top-strip"></div>
    <header class="header">
        <div class="logo-container">
            <span class="logo-ma">Ma</span>
            <span class="logo-room">Room</span>
            <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <!-- House outline -->
                <path d="M3 12l9-9 9 9v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <!-- Door -->
                <path d="M9 22V12h6v10"/>
                <!-- Window left -->
                <rect x="4" y="8" width="3" height="3" rx="0.5"/>
                <!-- Window right -->
                <rect x="17" y="8" width="3" height="3" rx="0.5"/>
                <!-- Lamp (circle on top) -->
                <circle cx="12" cy="4" r="1.5" fill="currentColor"/>
            </svg>
        </div>
        <a href="{{ route('dashboard') }}" class="nav-home">{{ __('booking_flow.home') }}</a>
    </header>
    
    <!-- Main Content with Map Background -->
    <div class="main-content">
        <div class="content-overlay">
            <h1 class="ma-room-title">{{ __('booking_flow.ma_room_title') }}</h1>
            
            <p class="description-text">
                {{ __('booking_flow.description_line1') }}
            </p>
            <p class="description-text">
                {{ __('booking_flow.description_line2') }}
            </p>
            
            <div class="language-buttons">
                <form method="POST" action="{{ route('set-locale') }}" style="display: inline;">
                    @csrf
                    <input type="hidden" name="locale" value="de">
                    <input type="hidden" name="redirect_to" value="{{ route('booking-flow.locations') }}">
                    <button type="submit" class="lang-button lang-button-deutsch {{ app()->getLocale() === 'de' ? 'active' : '' }}">
                        {{ __('booking_flow.deutsch') }}
                    </button>
                </form>
                
                <form method="POST" action="{{ route('set-locale') }}" style="display: inline;">
                    @csrf
                    <input type="hidden" name="locale" value="en">
                    <input type="hidden" name="redirect_to" value="{{ route('booking-flow.locations') }}">
                    <button type="submit" class="lang-button lang-button-englisch {{ app()->getLocale() === 'en' ? 'active' : '' }}">
                        {{ __('booking_flow.englisch') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
