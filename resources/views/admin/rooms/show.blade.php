@extends('layouts.app')

@section('title', __('admin.room_details'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ $room->name }}</h1>
        <div class="flex gap-4">
            <a href="{{ route('admin.rooms.edit', $room) }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                {{ __('admin.edit_room') }}
            </a>
            <a href="{{ route('admin.rooms.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-300 transition-colors">
                {{ __('admin.back_to_list') }}
            </a>
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8">
            <button onclick="showTab('details')" id="tab-details" class="tab-button border-b-2 border-blue-500 py-4 px-1 text-sm font-medium text-blue-600">
                {{ __('admin.details') }}
            </button>
            <button onclick="showTab('calendar')" id="tab-calendar" class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                {{ __('admin.calendar_sync') }}
            </button>
        </nav>
    </div>

    <!-- Details Tab -->
    <div id="tab-content-details" class="tab-content">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('admin.room_information') }}</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('admin.property') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $room->property->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('admin.capacity') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $room->capacity }} {{ __('admin.guests') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('admin.base_price') }} (Short-term per night)</dt>
                        <dd class="mt-1 text-sm text-gray-900">€{{ number_format($room->base_price, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Monthly Price (Long-term per month)</dt>
                        <dd class="mt-1 text-sm text-gray-900">€{{ number_format($room->monthly_price ?? 700, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('admin.short_term_allowed') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($room->short_term_allowed)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">{{ __('admin.yes') }}</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ __('admin.no') }}</span>
                            @endif
                        </dd>
                    </div>
                    @if($room->description)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('admin.description') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $room->description }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            <div class="bg-white shadow-md rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">{{ __('admin.images') }}</h2>
                    <form action="{{ route('admin.rooms.images.upload', $room) }}" method="POST" enctype="multipart/form-data" class="inline" id="image-upload-form">
                        @csrf
                        <input type="file" name="images[]" id="image-upload" accept="image/*" multiple class="hidden" onchange="document.getElementById('image-upload-form').submit()">
                        <label for="image-upload" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 cursor-pointer text-sm">
                            {{ __('admin.upload_images_bulk') }}
                        </label>
                    </form>
                </div>
                
                @if($room->images->count() > 0)
                <div id="image-gallery" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($room->images->sortBy('sort_order') as $image)
                    <div class="relative group" data-image-id="{{ $image->id }}">
                        <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $room->name }}" 
                             class="w-full h-32 object-cover rounded-lg cursor-move" draggable="true">
                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <form action="{{ route('admin.rooms.images.delete', ['room' => $room, 'image' => $image->id]) }}" 
                                  method="POST" class="inline" onsubmit="return confirm('{{ __('admin.delete_this_image') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-600 text-white p-1 rounded hover:bg-red-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                        <div class="absolute bottom-2 left-2 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded">
                            {{ __('admin.order') }} {{ $image->sort_order }}
                        </div>
                    </div>
                    @endforeach
                </div>
                <p class="mt-4 text-sm text-gray-500">{{ __('admin.drag_images_reorder') }}</p>
                @else
                <p class="text-sm text-gray-500">{{ __('admin.no_images_uploaded') }}</p>
                @endif
            </div>

            <!-- Blackout Dates Section -->
            <div class="bg-white shadow-md rounded-lg p-6 mt-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('admin.blackout_dates_maintenance') }}</h2>
                
                <form action="{{ route('admin.rooms.blackout-dates.store', $room) }}" method="POST" class="mb-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.start_date') }} *</label>
                            <input type="date" name="start_date" id="start_date" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.end_date') }} *</label>
                            <input type="date" name="end_date" id="end_date" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.reason') }}</label>
                            <select name="reason" id="reason"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="maintenance">{{ __('admin.maintenance') }}</option>
                                <option value="cleaning">{{ __('admin.cleaning') }}</option>
                                <option value="holiday">{{ __('admin.holiday') }}</option>
                                <option value="other">{{ __('admin.other') }}</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                                {{ __('admin.add_blackout') }}
                            </button>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.notes_optional') }}</label>
                        <textarea name="notes" id="notes" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </form>

                @if($room->blackoutDates->count() > 0)
                <div class="border-t border-gray-200 pt-4">
                    <h3 class="text-sm font-medium text-gray-900 mb-3">{{ __('admin.active_blackout_dates') }}</h3>
                    <div class="space-y-2">
                        @foreach($room->blackoutDates as $blackout)
                        <div class="flex items-center justify-between p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ \Carbon\Carbon::parse($blackout->start_date)->format('M d, Y') }} - 
                                    {{ \Carbon\Carbon::parse($blackout->end_date)->format('M d, Y') }}
                                </p>
                                <p class="text-xs text-gray-600">
                                    Reason: <span class="font-semibold">{{ ucfirst($blackout->reason) }}</span>
                                    @if($blackout->notes)
                                        | {{ $blackout->notes }}
                                    @endif
                                </p>
                            </div>
                            <form action="{{ route('admin.rooms.blackout-dates.delete', ['room' => $room, 'blackoutDate' => $blackout->id]) }}" 
                                  method="POST" class="inline" onsubmit="return confirm('{{ __('admin.delete_this_blackout_period') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                    {{ __('admin.delete') }}
                                </button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <p class="text-sm text-gray-500">{{ __('admin.no_blackout_dates_configured') }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Calendar & Sync Tab -->
    <div id="tab-content-calendar" class="tab-content hidden">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ __('admin.ical_calendar_sync') }}</h2>

            <!-- Import Section -->
            <div class="mb-8 pb-8 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('admin.import_airbnb') }}</h3>
                <form action="{{ route('admin.rooms.ical.import', $room) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="import_url" class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.airbnb_ical_import_url') }}</label>
                        <input type="url" name="import_url" id="import_url" 
                               value="{{ $room->icalImportFeed->url ?? '' }}"
                               placeholder="https://www.airbnb.com/calendar/ical/..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="mt-1 text-sm text-gray-500">{{ __('admin.paste_airbnb_calendar_export_url') }}</p>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="import_active" id="import_active" value="1" 
                               {{ ($room->icalImportFeed->active ?? false) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="import_active" class="ml-2 block text-sm text-gray-900">
                            {{ __('admin.active_include_automatic_sync') }}
                        </label>
                    </div>
                    <div class="flex items-center gap-4">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                            {{ __('admin.save_import_url') }}
                        </button>
                        @if($room->icalImportFeed && $room->icalImportFeed->active)
                        <form action="{{ route('admin.rooms.ical.sync', $room) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors">
                                {{ __('admin.sync_now') }}
                            </button>
                        </form>
                        @endif
                    </div>
                    @if($room->icalImportFeed && $room->icalImportFeed->last_synced_at)
                    <p class="text-sm text-gray-500">
                        {{ __('admin.last_synced') }} {{ $room->icalImportFeed->last_synced_at->format('M d, Y H:i') }}
                    </p>
                    @endif
                </form>
            </div>

            <!-- Export Section -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('admin.export_maroom') }}</h3>
                @if($room->icalExportFeed && $room->icalExportFeed->token)
                <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.export_url') }}</label>
                    <div class="flex items-center gap-2">
                        <input type="text" readonly 
                               value="{{ route('ical.export', ['room' => $room->id, 'token' => $room->icalExportFeed->token]) }}"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-md bg-white"
                               id="export-url">
                        <button onclick="copyExportUrl()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition-colors">
                            {{ __('admin.copy') }}
                        </button>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">{{ __('admin.paste_url_airbnb_import') }}</p>
                </div>
                @endif
                <form action="{{ route('admin.rooms.ical.export', $room) }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="{{ $room->icalExportFeed && $room->icalExportFeed->token ? 'revoke' : 'generate' }}">
                    <button type="submit" class="bg-{{ $room->icalExportFeed && $room->icalExportFeed->token ? 'red' : 'green' }}-600 text-white px-4 py-2 rounded-md hover:bg-{{ $room->icalExportFeed && $room->icalExportFeed->token ? 'red' : 'green' }}-700 transition-colors">
                        @if($room->icalExportFeed && $room->icalExportFeed->token)
                            {{ __('admin.revoke_token') }}
                        @else
                            {{ __('admin.generate_export_token') }}
                        @endif
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tab) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });

    // Show selected tab
    document.getElementById('tab-content-' + tab).classList.remove('hidden');
    const button = document.getElementById('tab-' + tab);
    button.classList.remove('border-transparent', 'text-gray-500');
    button.classList.add('border-blue-500', 'text-blue-600');
}

function copyExportUrl() {
    const input = document.getElementById('export-url');
    input.select();
    document.execCommand('copy');
    alert('{{ __('admin.export_url_copied') }}');
}

// Image drag and drop sorting
let draggedElement = null;
const gallery = document.getElementById('image-gallery');
if (gallery) {
    gallery.addEventListener('dragstart', (e) => {
        draggedElement = e.target.closest('[data-image-id]');
        e.target.style.opacity = '0.5';
    });

    gallery.addEventListener('dragend', (e) => {
        e.target.style.opacity = '1';
    });

    gallery.addEventListener('dragover', (e) => {
        e.preventDefault();
        const afterElement = getDragAfterElement(gallery, e.clientX);
        if (afterElement == null) {
            gallery.appendChild(draggedElement);
        } else {
            gallery.insertBefore(draggedElement, afterElement);
        }
    });

    gallery.addEventListener('drop', async (e) => {
        e.preventDefault();
        const imageIds = Array.from(gallery.querySelectorAll('[data-image-id]')).map(el => el.dataset.imageId);
        
        try {
            const response = await fetch('{{ route("admin.rooms.images.order", $room) }}', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ images: imageIds })
            });
            
            if (response.ok) {
                // Update order numbers
                gallery.querySelectorAll('[data-image-id]').forEach((el, index) => {
                    const orderBadge = el.querySelector('.absolute.bottom-2');
                    if (orderBadge) {
                        orderBadge.textContent = `{{ __('admin.order') }} ${index + 1}`;
                    }
                });
            }
        } catch (error) {
            console.error('Error updating order:', error);
        }
    });
}

function getDragAfterElement(container, x) {
    const draggableElements = [...container.querySelectorAll('[data-image-id]:not(.dragging)')];
    
    return draggableElements.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = x - box.left - box.width / 2;
        
        if (offset < 0 && offset > closest.offset) {
            return { offset: offset, element: child };
        } else {
            return closest;
        }
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}
</script>
@endsection

