@extends('layouts.app')

@section('title', __('admin.edit_booking'))

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('admin.edit_booking') }} #{{ $booking->id }}</h1>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 shadow-sm flex items-center gap-3">
        <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <p class="text-green-800 font-medium">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error') || $errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 shadow-sm flex items-center gap-3">
        <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <div class="flex-1">
            @if(session('error'))
                <p class="text-red-800 font-medium">{{ session('error') }}</p>
            @endif
            @if($errors->any())
                <ul class="list-disc list-inside text-red-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
    @endif

    @if(session('conflicts'))
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <h3 class="text-lg font-semibold text-red-800 mb-2">⚠️ {{ __('admin.conflicting_bookings_detected') }}</h3>
        <ul class="list-disc list-inside text-sm text-red-700 mb-4">
            @foreach(session('conflicts') as $conflict)
            <li>{{ __('admin.booking') }} #{{ $conflict['id'] }}: {{ $conflict['guest_first_name'] }} {{ $conflict['guest_last_name'] }} 
                @if(!empty($conflict['start_at']) && !empty($conflict['end_at']))
                ({{ \Carbon\Carbon::parse($conflict['start_at'])->format('M d') }} - {{ \Carbon\Carbon::parse($conflict['end_at'])->format('M d, Y') }})
                @endif
            </li>
            @endforeach
        </ul>
        <p class="text-sm text-red-700">{{ __('admin.check_override_conflict') }}</p>
    </div>
    @endif

    @if($errors->has('conflict'))
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <p class="text-red-800">{{ $errors->first('conflict') }}</p>
    </div>
    @endif

    <form action="{{ route('admin.bookings.update', $booking) }}" method="POST" class="bg-white shadow-md rounded-lg p-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="room_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.room') }} *</label>
                <select name="room_id" id="room_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" {{ old('room_id', $booking->room_id) == $room->id ? 'selected' : '' }}>
                            {{ $room->property->name }} - {{ $room->name }}
                        </option>
                    @endforeach
                </select>
                @error('room_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="source" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.source') }} *</label>
                <select name="source" id="source" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="manual" {{ old('source', $booking->source) == 'manual' ? 'selected' : '' }}>{{ __('admin.manual') }}</option>
                    <option value="website" {{ old('source', $booking->source) == 'website' ? 'selected' : '' }}>{{ __('admin.website') }}</option>
                    <option value="airbnb" {{ old('source', $booking->source) == 'airbnb' ? 'selected' : '' }}>{{ __('admin.airbnb') }}</option>
                </select>
                @error('source')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="start_at" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.check_in_date') }} *</label>
                <input type="date" name="start_at" id="start_at" 
                       value="{{ old('start_at', $booking->start_at ? \Carbon\Carbon::parse($booking->start_at)->format('Y-m-d') : '') }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('start_at')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="end_at" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('admin.check_out_date') }}
                    <span class="text-gray-500 text-sm font-normal">({{ __('booking_flow.optional_long_term') }})</span>
                </label>
                <input type="date" name="end_at" id="end_at" 
                       value="{{ old('end_at', $booking->end_at ? \Carbon\Carbon::parse($booking->end_at)->format('Y-m-d') : '') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="mt-1 text-xs text-gray-500">{{ __('booking_flow.select_checkin_description') }}</p>
                @error('end_at')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.status') }} *</label>
                <select name="status" id="status" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="pending" {{ old('status', $booking->status) == 'pending' ? 'selected' : '' }}>{{ __('admin.pending') }}</option>
                    <option value="confirmed" {{ old('status', $booking->status) == 'confirmed' ? 'selected' : '' }}>{{ __('admin.confirmed') }}</option>
                    <option value="cancelled" {{ old('status', $booking->status) == 'cancelled' ? 'selected' : '' }}>{{ __('admin.cancelled') }}</option>
                </select>
                @error('status')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="border-t border-gray-200 pt-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('admin.guest_information') }}</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="guest_first_name" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.first_name') }} *</label>
                    <input type="text" name="guest_first_name" id="guest_first_name" value="{{ old('guest_first_name', $booking->guest_first_name) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('guest_first_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="guest_last_name" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.last_name') }} *</label>
                    <input type="text" name="guest_last_name" id="guest_last_name" value="{{ old('guest_last_name', $booking->guest_last_name) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('guest_last_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.email') }} *</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $booking->email) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.phone') }}</label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone', $booking->phone) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="language" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.language') }}</label>
                    <select name="language" id="language" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="Deutsch" {{ old('language', $booking->language ?? 'Deutsch') === 'Deutsch' ? 'selected' : '' }}>{{ __('booking_flow.deutsch') }}</option>
                        <option value="Englisch" {{ old('language', $booking->language) === 'Englisch' ? 'selected' : '' }}>{{ __('booking_flow.englisch') }}</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">{{ __('admin.language_for_emails') }}</p>
                    @error('language')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.notes') }}</label>
                <textarea name="notes" id="notes" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes', $booking->notes) }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        @if(session('conflicts'))
        <div class="border-t border-gray-200 pt-6 mb-6">
            <div class="flex items-center">
                <input type="checkbox" name="override_conflict" id="override_conflict" value="1"
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="override_conflict" class="ml-2 block text-sm text-gray-900">
                    {{ __('admin.override_conflict_proceed') }}
                </label>
            </div>
        </div>
        @endif

        <div class="flex justify-end gap-4">
            <a href="{{ route('admin.bookings.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-300 transition-colors">
                {{ __('admin.cancel') }}
            </a>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                {{ __('admin.update_booking') }}
            </button>
        </div>
    </form>

    <!-- Payment Section -->
    <div class="mt-8 bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('admin.payment_management') }}</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-500">{{ __('admin.total_amount') }}</p>
                <p class="text-2xl font-semibold text-gray-900">€{{ number_format($booking->total_amount ?? 0, 2) }}</p>
            </div>
            <div class="p-4 bg-blue-50 rounded-lg">
                <p class="text-sm text-blue-500">{{ __('admin.paid_amount') }}</p>
                <p class="text-2xl font-semibold text-blue-900">€{{ number_format($booking->paid_amount ?? 0, 2) }}</p>
            </div>
            <div class="p-4 bg-{{ $booking->payment_status === 'paid' ? 'green' : ($booking->payment_status === 'refunded' ? 'red' : 'yellow') }}-50 rounded-lg">
                <p class="text-sm text-{{ $booking->payment_status === 'paid' ? 'green' : ($booking->payment_status === 'refunded' ? 'red' : 'yellow') }}-500">{{ __('admin.payment_status') }}</p>
                <p class="text-2xl font-semibold text-{{ $booking->payment_status === 'paid' ? 'green' : ($booking->payment_status === 'refunded' ? 'red' : 'yellow') }}-900">
                    @php
                        $statusTranslations = [
                            'paid' => __('admin.paid'),
                            'refunded' => __('admin.refunded'),
                            'pending' => __('admin.pending'),
                        ];
                        echo $statusTranslations[$booking->payment_status] ?? ucfirst($booking->payment_status);
                    @endphp
                </p>
            </div>
        </div>

        @if($booking->total_amount && $booking->paid_amount < $booking->total_amount)
        <form action="{{ route('admin.bookings.mark-paid', $booking) }}" method="POST" class="mb-6 p-4 bg-gray-50 rounded-lg">
            @csrf
            <h3 class="font-semibold text-gray-900 mb-3">{{ __('admin.mark_payment_as_paid') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="payment_amount" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.payment_amount') }}</label>
                    <input type="number" name="amount" id="payment_amount" 
                           value="{{ number_format($booking->total_amount - $booking->paid_amount, 2, '.', '') }}"
                           step="0.01" min="0.01" max="{{ $booking->total_amount - $booking->paid_amount }}"
                           required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500">{{ __('admin.remaining') }}: €{{ number_format($booking->total_amount - $booking->paid_amount, 2) }}</p>
                </div>
                <div>
                    <label for="payment_notes" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.payment_notes') }}</label>
                    <input type="text" name="notes" id="payment_notes" 
                           placeholder="{{ __('admin.payment_notes') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                        {{ __('admin.mark_as_paid') }}
                    </button>
                </div>
            </div>
        </form>
        @endif

        <!-- Payment Logs -->
        <div>
            <h3 class="font-semibold text-gray-900 mb-3">{{ __('admin.payment_history') }}</h3>
            @if($booking->paymentLogs->count() > 0)
            <div class="space-y-2">
                @foreach($booking->paymentLogs as $log)
                <div class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">€{{ number_format($log->amount, 2) }}</p>
                        <p class="text-sm text-gray-500">
                            @php
                                $typeTranslations = [
                                    'payment' => __('admin.payment'),
                                    'refund' => __('admin.refund'),
                                ];
                                $statusTranslations = [
                                    'completed' => __('admin.completed'),
                                    'pending' => __('admin.pending'),
                                    'failed' => __('admin.failed'),
                                ];
                                echo ($typeTranslations[$log->type] ?? ucfirst($log->type)) . ' - ' . ($statusTranslations[$log->status] ?? ucfirst($log->status));
                            @endphp
                            @if($log->payment_method)
                            {{ __('admin.via') }} {{ $log->payment_method }}
                            @endif
                        </p>
                        @if($log->notes)
                        <p class="text-xs text-gray-400 mt-1">
                            @php
                                // Translate old English default notes if they match
                                $englishDefault = 'Manual payment recorded by admin';
                                if ($log->notes === $englishDefault) {
                                    // Get booking's locale for translation
                                    $bookingLocale = $booking->getLocaleFromLanguage();
                                    $originalLocale = app()->getLocale();
                                    app()->setLocale($bookingLocale);
                                    echo __('admin.manual_payment_recorded_by_admin');
                                    app()->setLocale($originalLocale);
                                } else {
                                    echo $log->notes;
                                }
                            @endphp
                        </p>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">{{ $log->created_at->format('M d, Y H:i') }}</p>
                        @if($log->user)
                        <p class="text-xs text-gray-400">{{ __('admin.by') }} {{ $log->user->name }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-500">{{ __('admin.no_payment_history') }}</p>
            @endif
        </div>
    </div>

    <!-- Check-in Details Section -->
    <div class="mt-8 bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('dashboard.checkin_pdf_documents') }}</h2>
        
        @php
            $documentService = new \App\Services\DocumentService();
            $checkInPdfPath = $documentService->getCheckInPdfPath($booking->room);
        @endphp
        
        @if($checkInPdfPath && \Storage::exists($checkInPdfPath))
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-medium text-gray-900 mb-1">{{ __('dashboard.checkin_document') }}</h3>
                    <p class="text-sm text-gray-600">{{ basename($checkInPdfPath) }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('dashboard.checkin-pdf.download', ['pdf' => base64_encode(basename($checkInPdfPath))]) }}" 
                       class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">
                        {{ __('admin.download_pdf') }}
                    </a>
                    <form action="{{ route('dashboard.checkin-pdf.send') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="recipient_email" value="{{ $booking->email }}">
                        <input type="hidden" name="pdf_name" value="{{ base64_encode(basename($checkInPdfPath)) }}">
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm">
                            {{ __('dashboard.send_checkin_pdf_email') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @else
        <p class="text-sm text-gray-500">{{ __('admin.no_checkin_pdf_available') }}</p>
        @endif
    </div>

    <!-- Send Documents Section -->
    @if($booking->status === 'confirmed')
    <div class="mt-8 bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('admin.send_documents') ?? 'Send Documents' }}</h2>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-sm text-gray-700 mb-4">{{ __('admin.send_documents_description') ?? 'Send rental agreement and check-in details to the guest via email.' }}</p>
            <form action="{{ route('admin.bookings.send-documents', $booking) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                    {{ __('admin.send_rental_agreement_and_checkin') ?? 'Send Rental Agreement & Check-in Details' }}
                </button>
            </form>
        </div>
    </div>
    @endif

    <!-- Documents Section -->
    <div class="mt-8 bg-white shadow-md rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900">{{ __('admin.documents') }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.bookings.debug-pdf-overlay', ['booking' => $booking, 'locale' => 'en']) }}" 
                   target="_blank"
                   class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 text-sm">
                    Debug PDF (EN)
                </a>
                <a href="{{ route('admin.bookings.debug-pdf-overlay', ['booking' => $booking, 'locale' => 'de']) }}" 
                   target="_blank"
                   class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 text-sm">
                    Debug PDF (DE)
                </a>
            </div>
        </div>
        <p class="text-sm text-gray-600 mb-4">
            Use the Debug PDF buttons above to see where fields are being placed. Red boxes show text positions, colored boxes show signature positions.
        </p>
        
        @if($booking->documents->count() > 0)
        <div class="space-y-4">
            @foreach($booking->documents as $document)
            @php
                $docTypeNames = [
                    'rental_agreement' => __('admin.rental_agreement'),
                    'landlord_confirmation' => __('admin.landlord_confirmation'),
                    'rent_arrears' => __('admin.rent_arrears'),
                ];
                $docTypeName = $docTypeNames[$document->doc_type] ?? $document->doc_type;
            @endphp
            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                <div class="flex-1">
                    <h3 class="font-medium text-gray-900">{{ $docTypeName }}</h3>
                    <div class="flex items-center gap-4 mt-1 text-sm text-gray-500">
                        <span>{{ __('admin.locale') }}: {{ strtoupper($document->locale) }}</span>
                        <span>{{ __('admin.version') }}: {{ $document->version }}</span>
                        @if($document->signed_at)
                        <span>{{ __('admin.signed') }}: {{ \Carbon\Carbon::parse($document->signed_at)->format('M d, Y H:i') }}</span>
                        @endif
                        @if($document->generated_at)
                        <span>{{ __('admin.generated') }}: {{ \Carbon\Carbon::parse($document->generated_at)->format('M d, Y H:i') }}</span>
                        @endif
                    </div>
                </div>
                <div class="flex gap-2">
                    @if($document->storage_path && \Storage::exists($document->storage_path))
                    <a href="{{ route('documents.download', $document) }}" 
                       class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">
                        {{ __('admin.download_pdf') }}
                    </a>
                    <form action="{{ route('admin.bookings.documents.send', ['booking' => $booking, 'document' => $document->id]) }}" 
                          method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm">
                            {{ __('admin.send_mail') }}
                        </button>
                    </form>
                    @else
                    <span class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded-md text-sm">
                        {{ __('admin.generating') }}
                    </span>
                    @endif
                    <form action="{{ route('admin.bookings.documents.regenerate', ['booking' => $booking, 'document' => $document->id]) }}" 
                          method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 text-sm"
                                onclick="return confirm('{{ __('admin.regenerate_document_confirm') }}')">
                            {{ __('admin.regenerate') }}
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-sm text-gray-500">{{ __('admin.no_documents_generated') }}</p>
        @endif
    </div>
</div>
@endsection

