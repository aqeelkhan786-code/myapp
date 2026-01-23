@extends('layouts.app')

@section('title', __('admin.create_booking'))

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('admin.create_new_booking') }}</h1>

    <form action="{{ route('admin.bookings.store') }}" method="POST" class="bg-white shadow-md rounded-lg p-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="room_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.room') }} *</label>
                <select name="room_id" id="room_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('admin.select_room') }}</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
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
                    <option value="manual" {{ old('source') == 'manual' ? 'selected' : '' }}>{{ __('admin.manual') }}</option>
                    <option value="website" {{ old('source') == 'website' ? 'selected' : '' }}>{{ __('admin.website') }}</option>
                    <option value="airbnb" {{ old('source') == 'airbnb' ? 'selected' : '' }}>{{ __('admin.airbnb') }}</option>
                </select>
                @error('source')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="start_at" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.check_in_date') }} *</label>
                <input type="date" name="start_at" id="start_at" value="{{ old('start_at') }}" required 
                       min="{{ date('Y-m-d') }}"
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
                <input type="date" name="end_at" id="end_at" value="{{ old('end_at') }}"
                       min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="mt-1 text-xs text-gray-500">{{ __('booking_flow.select_checkin_description') }}</p>
                @error('end_at')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.status') }} *</label>
                <select name="status" id="status" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>{{ __('admin.pending') }}</option>
                    <option value="confirmed" {{ old('status') == 'confirmed' ? 'selected' : '' }}>{{ __('admin.confirmed') }}</option>
                    <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>{{ __('admin.cancelled') }}</option>
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
                    <input type="text" name="guest_first_name" id="guest_first_name" value="{{ old('guest_first_name') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('guest_first_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="guest_last_name" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.last_name') }} *</label>
                    <input type="text" name="guest_last_name" id="guest_last_name" value="{{ old('guest_last_name') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('guest_last_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.email') }} *</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.phone') }}</label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="language" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.language') }}</label>
                    <select name="language" id="language" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="Deutsch" {{ old('language', 'Deutsch') === 'Deutsch' ? 'selected' : '' }}>{{ __('booking_flow.deutsch') }}</option>
                        <option value="Englisch" {{ old('language') === 'Englisch' ? 'selected' : '' }}>{{ __('booking_flow.englisch') }}</option>
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
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <a href="{{ route('admin.bookings.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-300 transition-colors">
                {{ __('admin.cancel') }}
            </a>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                {{ __('admin.create_booking') }}
            </button>
        </div>
    </form>
</div>
@endsection


