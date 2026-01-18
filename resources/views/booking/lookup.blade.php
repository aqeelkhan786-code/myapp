@extends('layouts.app')

@section('title', 'Find My Booking')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-8 text-center">
            <svg class="h-16 w-16 text-white mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <h1 class="text-3xl font-bold text-white mb-2">Find My Booking</h1>
            <p class="text-blue-100">Enter your email address to view your bookings</p>
        </div>
        
        <div class="p-8">
            <form method="POST" action="{{ route('booking.find') }}" class="space-y-6">
                @csrf
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address *
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                           placeholder="Enter the email address used for booking">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <button type="submit" 
                        class="w-full bg-blue-600 text-white py-3 px-6 rounded-md hover:bg-blue-700 transition-colors font-semibold text-lg shadow-lg hover:shadow-xl">
                    <i class="fa-solid fa-search mr-2"></i> Find My Bookings
                </button>
            </form>
            
            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-sm text-gray-600 text-center mb-4">
                    <i class="fa-solid fa-info-circle mr-2"></i>
                    {{ __('Need help? Contact us if you can\'t find your booking.') }}
                </p>
                <p class="text-sm text-gray-700 text-center">
                    {{ __('Do you have an account?') }}
                    <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700 font-medium">{{ __('Log in') }}</a>
                    {{ __('or') }}
                    <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-700 font-medium">{{ __('Register') }}</a>
                    {{ __('to see your bookings and payment info in one place.') }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection











