@extends('layouts.app')

@section('title', __('admin.view_property'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ $property->name }}</h1>
        <div class="flex gap-3">
            <a href="{{ route('admin.properties.edit', $property) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors">
                {{ __('admin.edit') }}
            </a>
            <a href="{{ route('admin.properties.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition-colors">
                {{ __('admin.back') }}
            </a>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('admin.property_details') }}</h2>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <dt class="text-sm font-medium text-gray-500">{{ __('admin.property_name') }}</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $property->name }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">{{ __('admin.address') }}</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $property->address }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">{{ __('admin.city') }}</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $property->city }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">{{ __('admin.postal_code') }}</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $property->postal_code }}</dd>
            </div>
        </dl>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-900">{{ __('admin.rooms') }} ({{ $property->rooms->count() }})</h2>
            <a href="{{ route('admin.rooms.create') }}?property_id={{ $property->id }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors text-sm">
                {{ __('admin.add_room') }}
            </a>
        </div>

        @if($property->rooms->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.room') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.capacity') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.price') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($property->rooms as $room)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $room->name }}</div>
                            <div class="text-sm text-gray-500">{{ $room->slug }}</div>
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
        <p class="text-gray-500 text-center py-8">{{ __('admin.no_rooms_in_property') }}</p>
        @endif
    </div>
</div>
@endsection










