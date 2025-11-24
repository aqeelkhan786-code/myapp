@extends('layouts.app')

@section('title', __('admin.edit_booking'))

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('admin.edit_booking') }} #{{ $booking->id }}</h1>

    @if(session('conflicts'))
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <h3 class="text-lg font-semibold text-red-800 mb-2">⚠️ {{ __('admin.conflicting_bookings_detected') }}</h3>
        <ul class="list-disc list-inside text-sm text-red-700 mb-4">
            @foreach(session('conflicts') as $conflict)
            <li>Booking #{{ $conflict['id'] }}: {{ $conflict['guest_first_name'] }} {{ $conflict['guest_last_name'] }} 
                ({{ \Carbon\Carbon::parse($conflict['start_at'])->format('M d') }} - {{ \Carbon\Carbon::parse($conflict['end_at'])->format('M d, Y') }})
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
                <label for="room_id" class="block text-sm font-medium text-gray-700 mb-2">Room *</label>
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
                <label for="source" class="block text-sm font-medium text-gray-700 mb-2">Source *</label>
                <select name="source" id="source" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="manual" {{ old('source', $booking->source) == 'manual' ? 'selected' : '' }}>Manual</option>
                    <option value="website" {{ old('source', $booking->source) == 'website' ? 'selected' : '' }}>Website</option>
                    <option value="airbnb" {{ old('source', $booking->source) == 'airbnb' ? 'selected' : '' }}>Airbnb</option>
                </select>
                @error('source')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="start_at" class="block text-sm font-medium text-gray-700 mb-2">Check-in Date *</label>
                <input type="date" name="start_at" id="start_at" 
                       value="{{ old('start_at', \Carbon\Carbon::parse($booking->start_at)->format('Y-m-d')) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('start_at')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="end_at" class="block text-sm font-medium text-gray-700 mb-2">Check-out Date *</label>
                <input type="date" name="end_at" id="end_at" 
                       value="{{ old('end_at', \Carbon\Carbon::parse($booking->end_at)->format('Y-m-d')) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('end_at')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                <select name="status" id="status" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="pending" {{ old('status', $booking->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ old('status', $booking->status) == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="cancelled" {{ old('status', $booking->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                @error('status')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="border-t border-gray-200 pt-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Guest Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="guest_first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                    <input type="text" name="guest_first_name" id="guest_first_name" value="{{ old('guest_first_name', $booking->guest_first_name) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('guest_first_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="guest_last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                    <input type="text" name="guest_last_name" id="guest_last_name" value="{{ old('guest_last_name', $booking->guest_last_name) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('guest_last_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $booking->email) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone', $booking->phone) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
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
                    Override Conflict (proceed despite conflicting bookings)
                </label>
            </div>
        </div>
        @endif

        <div class="flex justify-end gap-4">
            <a href="{{ route('admin.bookings.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-300 transition-colors">
                Cancel
            </a>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                Update Booking
            </button>
        </div>
    </form>

    <!-- Payment Section -->
    <div class="mt-8 bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Payment Management</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-500">Total Amount</p>
                <p class="text-2xl font-semibold text-gray-900">€{{ number_format($booking->total_amount ?? 0, 2) }}</p>
            </div>
            <div class="p-4 bg-blue-50 rounded-lg">
                <p class="text-sm text-blue-500">Paid Amount</p>
                <p class="text-2xl font-semibold text-blue-900">€{{ number_format($booking->paid_amount ?? 0, 2) }}</p>
            </div>
            <div class="p-4 bg-{{ $booking->payment_status === 'paid' ? 'green' : ($booking->payment_status === 'refunded' ? 'red' : 'yellow') }}-50 rounded-lg">
                <p class="text-sm text-{{ $booking->payment_status === 'paid' ? 'green' : ($booking->payment_status === 'refunded' ? 'red' : 'yellow') }}-500">Status</p>
                <p class="text-2xl font-semibold text-{{ $booking->payment_status === 'paid' ? 'green' : ($booking->payment_status === 'refunded' ? 'red' : 'yellow') }}-900">
                    {{ ucfirst($booking->payment_status) }}
                </p>
            </div>
        </div>

        @if($booking->total_amount && $booking->paid_amount < $booking->total_amount)
        <form action="{{ route('admin.bookings.mark-paid', $booking) }}" method="POST" class="mb-6 p-4 bg-gray-50 rounded-lg">
            @csrf
            <h3 class="font-semibold text-gray-900 mb-3">Mark Payment as Paid</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="payment_amount" class="block text-sm font-medium text-gray-700 mb-2">Amount (€)</label>
                    <input type="number" name="amount" id="payment_amount" 
                           value="{{ number_format($booking->total_amount - $booking->paid_amount, 2, '.', '') }}"
                           step="0.01" min="0.01" max="{{ $booking->total_amount - $booking->paid_amount }}"
                           required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Remaining: €{{ number_format($booking->total_amount - $booking->paid_amount, 2) }}</p>
                </div>
                <div>
                    <label for="payment_notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <input type="text" name="notes" id="payment_notes" 
                           placeholder="Payment method, reference, etc."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                        Mark as Paid
                    </button>
                </div>
            </div>
        </form>
        @endif

        <!-- Payment Logs -->
        <div>
            <h3 class="font-semibold text-gray-900 mb-3">Payment History</h3>
            @if($booking->paymentLogs->count() > 0)
            <div class="space-y-2">
                @foreach($booking->paymentLogs as $log)
                <div class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">€{{ number_format($log->amount, 2) }}</p>
                        <p class="text-sm text-gray-500">
                            {{ ucfirst($log->type) }} - {{ ucfirst($log->status) }}
                            @if($log->payment_method)
                            via {{ $log->payment_method }}
                            @endif
                        </p>
                        @if($log->notes)
                        <p class="text-xs text-gray-400 mt-1">{{ $log->notes }}</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">{{ $log->created_at->format('M d, Y H:i') }}</p>
                        @if($log->user)
                        <p class="text-xs text-gray-400">by {{ $log->user->name }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-500">No payment history</p>
            @endif
        </div>
    </div>

    <!-- Documents Section -->
    <div class="mt-8 bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Documents</h2>
        
        @if($booking->documents->count() > 0)
        <div class="space-y-4">
            @foreach($booking->documents as $document)
            @php
                $docTypeNames = [
                    'rental_agreement' => 'Rental Agreement',
                    'landlord_confirmation' => 'Landlord Confirmation',
                    'rent_arrears' => 'Rent Arrears Certificate',
                ];
                $docTypeName = $docTypeNames[$document->doc_type] ?? $document->doc_type;
            @endphp
            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                <div class="flex-1">
                    <h3 class="font-medium text-gray-900">{{ $docTypeName }}</h3>
                    <div class="flex items-center gap-4 mt-1 text-sm text-gray-500">
                        <span>Locale: {{ strtoupper($document->locale) }}</span>
                        <span>Version: {{ $document->version }}</span>
                        @if($document->signed_at)
                        <span>Signed: {{ \Carbon\Carbon::parse($document->signed_at)->format('M d, Y H:i') }}</span>
                        @endif
                        @if($document->generated_at)
                        <span>Generated: {{ \Carbon\Carbon::parse($document->generated_at)->format('M d, Y H:i') }}</span>
                        @endif
                    </div>
                </div>
                <div class="flex gap-2">
                    @if($document->storage_path && \Storage::exists($document->storage_path))
                    <a href="{{ route('documents.download', $document) }}" 
                       class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">
                        Download PDF
                    </a>
                    @else
                    <span class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded-md text-sm">
                        Generating...
                    </span>
                    @endif
                    <form action="{{ route('admin.bookings.documents.regenerate', ['booking' => $booking, 'document' => $document->id]) }}" 
                          method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 text-sm"
                                onclick="return confirm('Regenerate this document?')">
                            Regenerate
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-sm text-gray-500">No documents generated yet for this booking.</p>
        @endif
    </div>
</div>
@endsection

