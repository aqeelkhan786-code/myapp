@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Settings</h1>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <p class="text-green-800">{{ session('success') }}</p>
    </div>
    @endif

    <!-- General Settings -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">General Settings</h2>
        <form action="{{ route('admin.settings.general') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="timezone" class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
                <select name="timezone" id="timezone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="Europe/Berlin" {{ config('app.timezone') === 'Europe/Berlin' ? 'selected' : '' }}>Europe/Berlin (CET/CEST)</option>
                    <option value="UTC" {{ config('app.timezone') === 'UTC' ? 'selected' : '' }}>UTC</option>
                    <option value="America/New_York" {{ config('app.timezone') === 'America/New_York' ? 'selected' : '' }}>America/New_York (EST/EDT)</option>
                    <option value="Asia/Tokyo" {{ config('app.timezone') === 'Asia/Tokyo' ? 'selected' : '' }}>Asia/Tokyo (JST)</option>
                </select>
                <p class="mt-1 text-sm text-gray-500">Current: {{ config('app.timezone') }}</p>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                Save General Settings
            </button>
        </form>
    </div>

    <!-- Payment Settings -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Payment Settings</h2>
        <form action="{{ route('admin.settings.payment') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="stripe_key" class="block text-sm font-medium text-gray-700 mb-2">Stripe Publishable Key</label>
                    <input type="text" name="stripe_key" id="stripe_key" 
                           value="{{ config('services.stripe.key') ? '••••••••' : '' }}"
                           placeholder="pk_test_..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500">Current value is hidden for security</p>
                </div>
                <div>
                    <label for="stripe_secret" class="block text-sm font-medium text-gray-700 mb-2">Stripe Secret Key</label>
                    <input type="password" name="stripe_secret" id="stripe_secret" 
                           value="{{ config('services.stripe.secret') ? '••••••••' : '' }}"
                           placeholder="sk_test_..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500">Current value is hidden for security</p>
                </div>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                Save Payment Settings
            </button>
        </form>
    </div>

    <!-- Email Templates -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Email Templates</h2>
        <div class="space-y-6">
            <!-- Booking Confirmation Template -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-3">Booking Confirmation Email</h3>
                <form action="{{ route('admin.settings.email') }}" method="POST">
                    @csrf
                    <input type="hidden" name="template" value="booking_confirmation">
                    <div class="mb-3">
                        <label for="booking_subject" class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                        <input type="text" name="subject" id="booking_subject" 
                               value="Booking Confirmation - {{ config('app.name') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-3">
                        <label for="booking_body" class="block text-sm font-medium text-gray-700 mb-2">Body</label>
                        <textarea name="body" id="booking_body" rows="10"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm">Dear @{{ $booking->guest_first_name }},

Your booking has been confirmed!

Booking Details:
- Room: @{{ $booking->room->name }}
- Check-in: @{{ $booking->start_at->format('M d, Y') }}
- Check-out: @{{ $booking->end_at->format('M d, Y') }}
- Total Amount: €@{{ number_format($booking->total_amount, 2) }}

Thank you for your booking!

Best regards,
{{ config('app.name') }}</textarea>
                        <p class="mt-1 text-sm text-gray-500">Use Blade syntax: @{{ $variable }}</p>
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">
                        Save Template
                    </button>
                </form>
            </div>

            <!-- Document Sent Template -->
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Document Sent Email</h3>
                <form action="{{ route('admin.settings.email') }}" method="POST">
                    @csrf
                    <input type="hidden" name="template" value="document_sent">
                    <div class="mb-3">
                        <label for="document_subject" class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                        <input type="text" name="subject" id="document_subject" 
                               value="Document Available - {{ config('app.name') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-3">
                        <label for="document_body" class="block text-sm font-medium text-gray-700 mb-2">Body</label>
                        <textarea name="body" id="document_body" rows="10"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm">Dear @{{ $booking->guest_first_name }},

A document has been generated for your booking.

Document Type: @{{ $document->doc_type }}

You can download it from your booking confirmation page.

Best regards,
{{ config('app.name') }}</textarea>
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">
                        Save Template
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Locale Settings -->
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Locale Settings</h2>
        <div class="space-y-3">
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                    <p class="font-medium text-gray-900">English (EN)</p>
                    <p class="text-sm text-gray-500">Primary language</p>
                </div>
                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">Active</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                    <p class="font-medium text-gray-900">German (DE)</p>
                    <p class="text-sm text-gray-500">Secondary language</p>
                </div>
                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">Active</span>
            </div>
            <p class="text-sm text-gray-500 mt-4">Both locales are supported for PDF generation and document templates.</p>
        </div>
    </div>
</div>
@endsection

