<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
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
                            <h1 class="text-4xl font-bold mb-2">Welcome to Your Dashboard</h1>
                            <p class="text-xl">Manage your bookings and properties</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                                <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=100&h=100&fit=crop" 
                                     alt="Properties" 
                                     class="w-12 h-12 rounded-lg object-cover">
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Properties</p>
                                <p class="text-2xl font-semibold text-gray-900">0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                                <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=100&h=100&fit=crop" 
                                     alt="Bookings" 
                                     class="w-12 h-12 rounded-lg object-cover">
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Bookings</p>
                                <p class="text-2xl font-semibold text-gray-900">0</p>
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
                                <p class="text-sm font-medium text-gray-500">Total Revenue</p>
                                <p class="text-2xl font-semibold text-gray-900">€0.00</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">Quick Actions</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <a href="{{ route('booking.index') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <img src="https://images.unsplash.com/photo-1493809842364-78817add7ffb?w=150&h=150&fit=crop" 
                                 alt="View Bookings" 
                                 class="w-16 h-16 rounded-lg object-cover mr-4">
                            <div>
                                <p class="font-medium text-gray-900">View Bookings</p>
                                <p class="text-sm text-gray-500">Manage all your bookings</p>
                            </div>
                        </a>
                        <div class="flex items-center p-4 border border-gray-200 rounded-lg">
                            <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=150&h=150&fit=crop" 
                                 alt="Properties" 
                                 class="w-16 h-16 rounded-lg object-cover mr-4">
                            <div>
                                <p class="font-medium text-gray-900">Manage Properties</p>
                                <p class="text-sm text-gray-500">Add and edit properties</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
