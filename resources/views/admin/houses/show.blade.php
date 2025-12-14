@extends('layouts.app')

@section('title', __('admin.view_house'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ $house->name }}</h1>
        <div class="flex gap-3">
            <a href="{{ route('admin.houses.edit', $house) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors">
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
            <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('admin.image') }}</h2>
            <img src="{{ asset('storage/' . $house->image) }}" alt="{{ $house->name }}" class="w-full h-auto rounded-lg object-cover">
        </div>
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
@endsection
