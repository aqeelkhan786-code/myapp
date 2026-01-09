@extends('layouts.app')

@section('title', __('admin.view_house'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ $house->name }}</h1>
        <div class="flex gap-3">
            <a href="{{ route('admin.houses.edit', $house) }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors shadow">
                {{ __('admin.edit') }}
            </a>
            <a href="{{ route('admin.houses.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition-colors">
                {{ __('admin.back') }}
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="md:col-span-2 bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('admin.house_details') }}</h2>
            <dl class="grid grid-cols-1 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('admin.house_name') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $house->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('admin.slug') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $house->slug }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('admin.location') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $house->location->name }}</dd>
                </div>
                @if($house->description)
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('admin.description') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $house->description }}</dd>
                </div>
                @endif
            </dl>
        </div>

        @if($house->image)
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('admin.main_image') }}</h2>
            <img src="{{ asset('storage/' . $house->image) }}" alt="{{ $house->name }}" class="w-full h-auto rounded-lg object-cover">
        </div>
        @endif
    </div>

    <div class="bg-white shadow-md rounded-lg p-6 mt-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-900">{{ __('admin.images') }}</h2>
            <form action="{{ route('admin.houses.images.upload', $house) }}" method="POST" enctype="multipart/form-data" class="inline" id="image-upload-form">
                @csrf
                <input type="file" name="images[]" id="image-upload" accept="image/*" multiple class="hidden" onchange="document.getElementById('image-upload-form').submit()">
                <label for="image-upload" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 cursor-pointer text-sm">
                    {{ __('admin.upload_images_bulk') }}
                </label>
            </form>
        </div>
        
        @if($house->images->count() > 0)
        <div id="image-gallery" class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($house->images->sortBy('sort_order') as $image)
            <div class="relative group" data-image-id="{{ $image->id }}">
                <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $house->name }}" 
                     class="w-full h-32 object-cover rounded-lg cursor-move" draggable="true">
                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <form action="{{ route('admin.houses.images.delete', ['house' => $house, 'image' => $image->id]) }}" 
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

    <div class="bg-white shadow-md rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-900">{{ __('admin.rooms') }} ({{ $house->rooms->count() }})</h2>
            <a href="{{ route('admin.rooms.create') }}?house_id={{ $house->id }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors text-sm">
                {{ __('admin.add_room') }}
            </a>
        </div>

        @if($house->rooms->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.room') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.property') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.capacity') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.price') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($house->rooms as $room)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $room->name }}</div>
                            <div class="text-sm text-gray-500">{{ $room->slug }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $room->property->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $room->capacity }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">€{{ number_format($room->base_price, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.rooms.show', $room) }}" class="text-blue-600 hover:text-blue-900 mr-3">{{ __('admin.view') }}</a>
                            <a href="{{ route('admin.rooms.edit', $room) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('admin.edit') }}</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-gray-500 text-center py-8">{{ __('admin.no_rooms_in_house') }}</p>
        @endif
    </div>
</div>

@if($house->images->count() > 0)
<script>
// Image drag and drop sorting
let draggedElement = null;
const gallery = document.getElementById('image-gallery');

if (gallery) {
    gallery.addEventListener('dragstart', function(e) {
        draggedElement = e.target.closest('[data-image-id]');
        e.target.style.opacity = '0.5';
    });

    gallery.addEventListener('dragend', function(e) {
        e.target.style.opacity = '1';
    });

    gallery.addEventListener('dragover', function(e) {
        e.preventDefault();
        const afterElement = getDragAfterElement(gallery, e.clientX);
        const dragging = draggedElement;
        if (afterElement == null) {
            gallery.appendChild(dragging);
        } else {
            gallery.insertBefore(dragging, afterElement);
        }
    });

    gallery.addEventListener('drop', async function(e) {
        e.preventDefault();
        const imageIds = Array.from(gallery.querySelectorAll('[data-image-id]')).map(el => el.dataset.imageId);
        
        try {
            const response = await fetch('{{ route('admin.houses.images.order', $house) }}', {
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
                        orderBadge.textContent = '{{ __('admin.order') }} ' + (index + 1);
                    }
                });
            }
        } catch (error) {
            console.error('Error updating order:', error);
        }
    });

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
}
</script>
@endif
@endsection











