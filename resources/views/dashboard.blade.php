<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('common.Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Hero Section with Image -->
            <div class="mb-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="relative h-64 md:h-96">
                    <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1200&h=600&fit=crop" 
                         alt="Luxury Apartment" 
                         class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-900/70 to-transparent flex items-center">
                    <div class="px-8 text-white">
                        <h1 class="text-4xl font-bold mb-2">{{ __('dashboard.welcome') }}</h1>
                        <p class="text-xl">{{ __('dashboard.manage_bookings_properties') }}</p>
                    </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                                <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=100&h=100&fit=crop" 
                                     alt="Bookings" 
                                     class="w-12 h-12 rounded-lg object-cover">
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">{{ __('dashboard.total_bookings') }}</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $totalBookings ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                                <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=100&h=100&fit=crop" 
                                     alt="Confirmed" 
                                     class="w-12 h-12 rounded-lg object-cover">
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">{{ __('dashboard.confirmed') }}</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $confirmedBookings ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                                <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?w=100&h=100&fit=crop" 
                                     alt="Pending" 
                                     class="w-12 h-12 rounded-lg object-cover">
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">{{ __('dashboard.pending') }}</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $pendingBookings ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                                <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?w=100&h=100&fit=crop" 
                                     alt="Revenue" 
                                     class="w-12 h-12 rounded-lg object-cover">
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">{{ __('dashboard.total_revenue') }}</p>
                                <p class="text-2xl font-semibold text-gray-900">€{{ number_format($totalRevenue ?? 0, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Arrivals & Conflicts Panels -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Upcoming Arrivals -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('dashboard.upcoming_arrivals') }}</h3>
                        @if(isset($arrivals) && $arrivals->count() > 0)
                        <div class="space-y-3">
                            @foreach($arrivals as $booking)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $booking->guest_full_name }}</p>
                                    <p class="text-sm text-gray-500">{{ $booking->room->name }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ \Carbon\Carbon::parse($booking->start_at)->format('M d') }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($booking->start_at)->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-sm text-gray-500">{{ __('dashboard.no_upcoming_arrivals') }}</p>
                        @endif
                    </div>
                </div>

                <!-- Conflicts Panel -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">⚠️ {{ __('dashboard.booking_conflicts') }}</h3>
                        @if(isset($conflicts) && count($conflicts) > 0)
                        <div class="space-y-3">
                            @foreach($conflicts as $conflict)
                            <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                                <p class="text-sm font-medium text-red-900 mb-1">{{ __('dashboard.room') }}: {{ $conflict['booking1']->room->name }}</p>
                                <p class="text-xs text-red-700">
                                    Booking #{{ $conflict['booking1']->id }} ({{ $conflict['booking1']->guest_full_name }}) 
                                    {{ __('dashboard.conflicts_with') }} 
                                    Booking #{{ $conflict['booking2']->id }} ({{ $conflict['booking2']->guest_full_name }})
                                </p>
                                <div class="mt-2 flex gap-2">
                                    <a href="{{ route('admin.bookings.edit', $conflict['booking1']) }}" class="text-xs text-blue-600 hover:text-blue-800">{{ __('admin.edit') }} #{{ $conflict['booking1']->id }}</a>
                                    <a href="{{ route('admin.bookings.edit', $conflict['booking2']) }}" class="text-xs text-blue-600 hover:text-blue-800">{{ __('admin.edit') }} #{{ $conflict['booking2']->id }}</a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-sm text-gray-500">{{ __('dashboard.no_conflicts') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">{{ __('dashboard.quick_actions') }}</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <a href="{{ route('booking.index') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <img src="https://images.unsplash.com/photo-1493809842364-78817add7ffb?w=150&h=150&fit=crop" 
                                 alt="View Bookings" 
                                 class="w-16 h-16 rounded-lg object-cover mr-4">
                            <div>
                                <p class="font-medium text-gray-900">{{ __('dashboard.view_bookings') }}</p>
                                <p class="text-sm text-gray-500">{{ __('dashboard.browse_available_rooms') }}</p>
                            </div>
                        </a>
                        <a href="{{ route('admin.bookings.index') }}" class="flex items-center p-4 border-2 border-blue-300 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=150&h=150&fit=crop" 
                                 alt="Admin Bookings" 
                                 class="w-16 h-16 rounded-lg object-cover mr-4">
                            <div>
                                <p class="font-medium text-gray-900">{{ __('dashboard.admin_manage_bookings') }}</p>
                                <p class="text-sm text-gray-500">{{ __('dashboard.create_edit_bookings_manually') }}</p>
                            </div>
                        </a>
                        <a href="{{ route('admin.bookings.create') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?w=150&h=150&fit=crop" 
                                 alt="Create Booking" 
                                 class="w-16 h-16 rounded-lg object-cover mr-4">
                            <div>
                                <p class="font-medium text-gray-900">{{ __('dashboard.create_new_booking') }}</p>
                                <p class="text-sm text-gray-500">{{ __('dashboard.add_manual_booking') }}</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
