@extends('layouts.app')

@section('title', __('admin.view_location'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ $location->name }}</h1>
        <div class="flex gap-3">
            <a href="{{ route('admin.locations.edit', $location) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors">
                {{ __('admin.edit') }}
            </a>
            <a href="{{ route('admin.locations.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition-colors">
                {{ __('admin.back') }}
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="md:col-span-2 bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('admin.location_details') }}</h2>
            <dl class="grid grid-cols-1 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('admin.location_name') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $location->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('admin.slug') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $location->slug }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('admin.sort_order') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $location->sort_order }}</dd>
                </div>
                @if($location->description)
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('admin.description') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $location->description }}</dd>
                </div>
                @endif
            </dl>
        </div>

        @if($location->image)
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('admin.image') }}</h2>
            <img src="{{ asset('storage/' . $location->image) }}" alt="{{ $location->name }}" class="w-full h-auto rounded-lg object-cover">
        </div>
        @endif
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-900">{{ __('admin.houses') }} ({{ $location->houses->count() }})</h2>
            <a href="{{ route('admin.houses.create') }}?location_id={{ $location->id }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors text-sm">
                {{ __('admin.add_house') }}
            </a>
        </div>

        @if($location->houses->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.house') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.description') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.rooms_count') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($location->houses as $house)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                @if($house->image)
                                <img src="{{ asset('storage/' . $house->image) }}" alt="{{ $house->name }}" class="h-10 w-10 rounded-lg object-cover mr-3">
                                @endif
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $house->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $house->slug }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div class="max-w-xs truncate">{{ $house->description ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $house->rooms->count() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.houses.show', $house) }}" class="text-blue-600 hover:text-blue-900 mr-3">{{ __('admin.view') }}</a>
                            <a href="{{ route('admin.houses.edit', $house) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('admin.edit') }}</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-gray-500 text-center py-8">{{ __('admin.no_houses_in_location') }}</p>
        @endif
    </div>
</div>
@endsection





