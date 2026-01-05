<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('common.Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <p class="text-green-800">{{ session('success') }}</p>
            </div>
            @endif

            @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <ul class="list-disc list-inside text-red-800">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Hero Section with Image -->
            <div class="mb-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="relative h-64 md:h-96">
                    <img src="{{ asset('images/main bg.jpeg') }}" 
                         alt="Ma Room Background" 
                         class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-900/70 to-transparent flex items-center">
                    <div class="px-8 text-white">
                        <h1 class="text-4xl font-bold mb-2">{{ __('dashboard.welcome') }}</h1>
                        <p class="text-xl mb-4">{{ __('dashboard.manage_bookings_properties') }}</p>
                        <a href="{{ route('booking-flow.home') }}" 
                           class="inline-flex items-center px-6 py-3 bg-white text-blue-600 rounded-lg font-semibold hover:bg-blue-50 transition-colors shadow-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            {{ __('dashboard.visit_us') }}
                        </a>
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

            <!-- Check-in PDFs Section -->
            @if(isset($availablePdfs) && count($availablePdfs) > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            {{ __('dashboard.checkin_pdf_documents') }}
                        </h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach($availablePdfs as $pdf)
                        <div class="group relative bg-gradient-to-br from-gray-50 to-white border border-gray-200 rounded-lg p-4 hover:shadow-md hover:border-blue-300 transition-all duration-200">
                            <div class="flex flex-col h-full">
                                <!-- PDF Icon and Name -->
                                <div class="flex items-start mb-3">
                                    <div class="flex-shrink-0 mr-3">
                                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                                            <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-gray-900 text-sm leading-tight mb-1 line-clamp-2" title="{{ $pdf['name'] }}">
                                            {{ $pdf['name'] }}
                                        </p>
                                        <p class="text-xs text-gray-500">{{ __('dashboard.checkin_document') }}</p>
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="flex items-center justify-end gap-2 mt-auto pt-3 border-t border-gray-200">
                                    <a href="{{ route('dashboard.checkin-pdf.download', ['pdf' => base64_encode($pdf['name'])]) }}" 
                                       class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 transition-colors" 
                                       download
                                       title="{{ __('dashboard.download_pdf') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                        {{ __('dashboard.download') }}
                                    </a>
                                    <button onclick="openSendEmailModal('{{ base64_encode($pdf['name']) }}', '{{ $pdf['name'] }}')" 
                                            class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-green-600 bg-green-50 rounded-md hover:bg-green-100 transition-colors"
                                            title="{{ __('dashboard.send_via_email') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        {{ __('dashboard.send') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

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

    <!-- Send Email Modal -->
    <div id="sendEmailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('dashboard.send_checkin_pdf_email') }}</h3>
                    <button onclick="closeSendEmailModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form id="sendEmailForm" action="{{ route('dashboard.checkin-pdf.send') }}" method="POST">
                    @csrf
                    <input type="hidden" name="pdf_name" id="pdf_name_input">
                    <div class="mb-4">
                        <label for="recipient_email" class="block text-sm font-medium text-gray-700 mb-2">{{ __('dashboard.recipient_email') }} *</label>
                        <input type="email" name="recipient_email" id="recipient_email" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="customer@example.com">
                    </div>
                    <div class="mb-4">
                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">{{ __('dashboard.subject') }}</label>
                        <input type="text" name="subject" id="subject"
                               value="{{ app()->getLocale() === 'de' ? 'Check-in Informationen - MaRoom' : 'Check-in Information - MaRoom' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-2">{{ __('dashboard.message') }}</label>
                        <textarea name="message" id="message" rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="{{ app()->getLocale() === 'de' ? 'Sehr geehrter Kunde,&#10;&#10;Bitte finden Sie die Check-in Informationen im Anhang.&#10;&#10;Mit freundlichen Grüßen,&#10;MaRoom Team' : 'Dear customer,&#10;&#10;Please find attached the check-in information document.&#10;&#10;Best regards,&#10;MaRoom Team' }}">{{ app()->getLocale() === 'de' ? 'Sehr geehrter Kunde,

Bitte finden Sie die Check-in Informationen im Anhang.

Mit freundlichen Grüßen,
MaRoom Team' : 'Dear customer,

Please find attached the check-in information document.

Best regards,
MaRoom Team' }}</textarea>
                    </div>
                    <div class="mb-4">
                        <p class="text-sm text-gray-600">{{ __('dashboard.pdf_attached') }}</p>
                        <p class="text-sm font-medium text-gray-900 mt-1" id="pdf_name_display"></p>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeSendEmailModal()" 
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                            {{ __('dashboard.cancel') }}
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                            {{ __('dashboard.send_email') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openSendEmailModal(pdfEncoded, pdfName) {
            document.getElementById('pdf_name_input').value = pdfEncoded;
            document.getElementById('pdf_name_display').textContent = pdfName;
            document.getElementById('sendEmailModal').classList.remove('hidden');
        }

        function closeSendEmailModal() {
            document.getElementById('sendEmailModal').classList.add('hidden');
            document.getElementById('pdf_name_input').value = '';
            document.getElementById('pdf_name_display').textContent = '';
            document.getElementById('recipient_email').value = '';
        }

        // Close modal when clicking outside
        document.getElementById('sendEmailModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeSendEmailModal();
            }
        });
    </script>
        </div>
    </div>
</x-app-layout>
