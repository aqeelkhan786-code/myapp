@extends('layouts.app')

@section('title', __('settings.title'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('settings.title') }}</h1>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <p class="text-green-800">{{ session('success') }}</p>
    </div>
    @endif

    <!-- General Settings -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('settings.general_settings') }}</h2>
        <form action="{{ route('admin.settings.general') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="locale" class="block text-sm font-medium text-gray-700 mb-2">{{ __('settings.language') }}</label>
                    <select name="locale" id="locale" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="en" {{ (config('app.locale') ?: 'de') === 'en' ? 'selected' : '' }}>{{ __('settings.english') }}</option>
                        <option value="de" {{ (config('app.locale') ?: 'de') === 'de' ? 'selected' : '' }}>{{ __('settings.german') }}</option>
                    </select>
                    <p class="mt-1 text-sm text-gray-500">{{ __('settings.current') }}: {{ config('app.locale') ?: 'de' }}</p>
                </div>
                <div>
                    <label for="timezone" class="block text-sm font-medium text-gray-700 mb-2">{{ __('settings.timezone') }}</label>
                    <select name="timezone" id="timezone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="Europe/Berlin" {{ config('app.timezone') === 'Europe/Berlin' ? 'selected' : '' }}>Europe/Berlin (CET/CEST)</option>
                        <option value="UTC" {{ config('app.timezone') === 'UTC' ? 'selected' : '' }}>UTC</option>
                        <option value="America/New_York" {{ config('app.timezone') === 'America/New_York' ? 'selected' : '' }}>America/New_York (EST/EDT)</option>
                        <option value="Asia/Tokyo" {{ config('app.timezone') === 'Asia/Tokyo' ? 'selected' : '' }}>Asia/Tokyo (JST)</option>
                    </select>
                    <p class="mt-1 text-sm text-gray-500">{{ __('settings.current') }}: {{ config('app.timezone') }}</p>
                </div>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                {{ __('settings.save_general_settings') }}
            </button>
        </form>
    </div>

    <!-- Landlord Information Settings -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('settings.landlord_settings') ?? 'Vermieterinformationen' }}</h2>
        <form action="{{ route('admin.settings.landlord') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="landlord_name" class="block text-sm font-medium text-gray-700 mb-2">{{ __('settings.landlord_name') ?? 'Name' }} *</label>
                    <input type="text" name="landlord_name" id="landlord_name" 
                           value="{{ config('landlord.name') }}"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="landlord_email" class="block text-sm font-medium text-gray-700 mb-2">{{ __('settings.landlord_email') ?? 'E-Mail' }}</label>
                    <input type="email" name="landlord_email" id="landlord_email" 
                           value="{{ config('landlord.email') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="landlord_address" class="block text-sm font-medium text-gray-700 mb-2">{{ __('settings.landlord_address') ?? 'Adresse' }}</label>
                    <input type="text" name="landlord_address" id="landlord_address" 
                           value="{{ config('landlord.address') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="landlord_phone" class="block text-sm font-medium text-gray-700 mb-2">{{ __('settings.landlord_phone') ?? 'Telefon' }}</label>
                    <input type="text" name="landlord_phone" id="landlord_phone" 
                           value="{{ config('landlord.phone') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="landlord_postal_code" class="block text-sm font-medium text-gray-700 mb-2">{{ __('settings.landlord_postal_code') ?? 'Postleitzahl' }}</label>
                    <input type="text" name="landlord_postal_code" id="landlord_postal_code" 
                           value="{{ config('landlord.postal_code') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="landlord_city" class="block text-sm font-medium text-gray-700 mb-2">{{ __('settings.landlord_city') ?? 'Stadt' }}</label>
                    <input type="text" name="landlord_city" id="landlord_city" 
                           value="{{ config('landlord.city') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                {{ __('settings.save_landlord_settings') ?? 'Vermieterinformationen speichern' }}
            </button>
        </form>
    </div>

    <!-- Payment Settings -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('settings.payment_settings') }}</h2>
        <form action="{{ route('admin.settings.payment') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="stripe_key" class="block text-sm font-medium text-gray-700 mb-2">{{ __('settings.stripe_publishable_key') }}</label>
                    <input type="text" name="stripe_key" id="stripe_key" 
                           value="{{ config('services.stripe.key') ? '••••••••' : '' }}"
                           placeholder="pk_test_..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500">{{ __('settings.current_value_hidden') }}</p>
                </div>
                <div>
                    <label for="stripe_secret" class="block text-sm font-medium text-gray-700 mb-2">{{ __('settings.stripe_secret_key') }}</label>
                    <input type="password" name="stripe_secret" id="stripe_secret" 
                           value="{{ config('services.stripe.secret') ? '••••••••' : '' }}"
                           placeholder="sk_test_..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500">{{ __('settings.current_value_hidden') }}</p>
                </div>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                {{ __('settings.save_payment_settings') }}
            </button>
        </form>
    </div>

    <!-- Email Templates -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('settings.email_templates') }}</h2>
        <div class="space-y-6">
            <!-- Booking Confirmation Template -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-3">{{ __('settings.booking_confirmation_email') }}</h3>
                <form action="{{ route('admin.settings.email') }}" method="POST">
                    @csrf
                    <input type="hidden" name="template" value="booking_confirmation">
                    <div class="mb-3">
                        <label for="booking_subject" class="block text-sm font-medium text-gray-700 mb-2">{{ __('settings.subject') }}</label>
                        <input type="text" name="subject" id="booking_subject" 
                               value="Booking Confirmation - {{ config('app.name') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-3">
                        <label for="booking_body" class="block text-sm font-medium text-gray-700 mb-2">{{ __('settings.body') }}</label>
                        <textarea name="body" id="booking_body" rows="10"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm">Dear @{{ $booking->guest_first_name }},

Your booking has been confirmed!

Booking Details:
- {{ __('admin.room') ?? __('booking.room') }}: @{{ $booking->room->name }}
- {{ __('admin.check_in_date') }}: @{{ $booking->start_at->format('M d, Y') }}
- {{ __('admin.check_out_date') }}: @{{ $booking->end_at->format('M d, Y') }}
- {{ __('booking.total_amount') }}: €@{{ number_format($booking->total_amount, 2) }}

Thank you for your booking!

Best regards,
{{ config('app.name') }}</textarea>
                        <p class="mt-1 text-sm text-gray-500">{{ __('settings.use_blade_syntax') }}</p>
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">
                        {{ __('settings.save_template') }}
                    </button>
                </form>
            </div>

            <!-- Document Sent Template -->
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-3">{{ __('settings.document_sent_email') }}</h3>
                <form action="{{ route('admin.settings.email') }}" method="POST">
                    @csrf
                    <input type="hidden" name="template" value="document_sent">
                    <div class="mb-3">
                        <label for="document_subject" class="block text-sm font-medium text-gray-700 mb-2">{{ __('settings.subject') }}</label>
                        <input type="text" name="subject" id="document_subject" 
                               value="Document Available - {{ config('app.name') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-3">
                        <label for="document_body" class="block text-sm font-medium text-gray-700 mb-2">{{ __('settings.body') }}</label>
                        <textarea name="body" id="document_body" rows="10"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm">Dear @{{ $booking->guest_first_name }},

A document has been generated for your booking.

Document Type: @{{ $document->doc_type }}

You can download it from your booking confirmation page.

Best regards,
{{ config('app.name') }}</textarea>
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">
                        {{ __('settings.save_template') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Locale Information -->
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('settings.supported_languages') }}</h2>
        <div class="space-y-3">
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                    <p class="font-medium text-gray-900">{{ __('settings.english') }}</p>
                    <p class="text-sm text-gray-500">{{ __('settings.default_language') }}</p>
                </div>
                <span class="px-3 py-1 text-sm font-semibold rounded-full {{ (config('app.locale') ?: 'de') === 'en' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ (config('app.locale') ?: 'de') === 'en' ? __('settings.active') : __('settings.available') }}
                </span>
            </div>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                    <p class="font-medium text-gray-900">{{ __('settings.german') }}</p>
                    <p class="text-sm text-gray-500">{{ __('settings.secondary_language') }}</p>
                </div>
                <span class="px-3 py-1 text-sm font-semibold rounded-full {{ config('app.locale') === 'de' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ config('app.locale') === 'de' ? __('settings.active') : __('settings.available') }}
                </span>
            </div>
            <p class="text-sm text-gray-500 mt-4">{{ __('settings.change_language_info') }}</p>
        </div>
    </div>
</div>
@endsection

