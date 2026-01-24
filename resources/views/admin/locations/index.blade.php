@extends('layouts.app')

@section('title', __('admin.manage_locations'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-12">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 sm:mb-8 gap-4">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ __('admin.manage_locations') }}</h1>
        <a href="{{ route('admin.locations.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors text-sm sm:text-base font-medium text-center">
            {{ __('admin.create_new_location') }}
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <p class="text-green-800">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error') || $errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <p class="text-red-800">{{ session('error') ?? $errors->first() }}</p>
    </div>
    @endif

    <!-- Mobile Card View -->
    <div class="block md:hidden space-y-4">
        @forelse($locations as $location)
        <div class="bg-white shadow-md rounded-lg p-4 border border-gray-200">
            <div class="flex items-start gap-3 mb-3">
                @if($location->image)
                <img src="{{ asset('storage/' . $location->image) }}" alt="{{ $location->name }}" class="h-16 w-16 rounded-lg object-cover flex-shrink-0">
                @else
                <div class="h-16 w-16 rounded-lg bg-gray-200 flex items-center justify-center flex-shrink-0">
                    <span class="text-xs text-gray-400 text-center px-1">{{ __('admin.no_image') }}</span>
                </div>
                @endif
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-semibold text-gray-900 truncate">{{ $location->name }}</h3>
                    <p class="text-xs text-gray-500 truncate">{{ $location->slug }}</p>
                </div>
            </div>
            <div class="space-y-2 mb-4">
                <div class="text-sm">
                    <span class="font-medium text-gray-700">{{ __('admin.description') }}:</span>
                    <p class="text-gray-900 mt-1">{{ $location->description ?? '-' }}</p>
                </div>
                <div class="flex items-center text-sm">
                    <span class="font-medium text-gray-700 w-24">{{ __('admin.sort_order') }}:</span>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                        {{ $location->sort_order }}
                    </span>
                </div>
                <div class="flex items-center text-sm">
                    <span class="font-medium text-gray-700 w-24">{{ __('admin.houses_count') }}:</span>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        {{ $location->houses_count }}
                    </span>
                </div>
            </div>
            <div class="flex gap-2 pt-3 border-t border-gray-200">
                <a href="{{ route('admin.locations.show', $location) }}" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors text-center text-sm font-medium">
                    {{ __('admin.view') }}
                </a>
                <a href="{{ route('admin.locations.edit', $location) }}" class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors text-center text-sm font-medium">
                    {{ __('admin.edit') }}
                </a>
                <form action="{{ route('admin.locations.destroy', $location) }}" method="POST" class="flex-1" onsubmit="return confirm('{{ __('admin.are_you_sure_delete_location') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full text-red-600 hover:text-red-900 font-medium px-4 py-2 border border-red-300 rounded-md hover:bg-red-50 transition-colors text-sm">
                        {{ __('admin.delete') }}
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="bg-white shadow-md rounded-lg p-6 text-center text-gray-500">
            {{ __('admin.no_locations_found') }}
        </div>
        @endforelse
    </div>

    <!-- Desktop Table View -->
    <div class="hidden md:block bg-white shadow-md rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.location') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.description') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.sort_order') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.houses_count') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($locations as $location)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            @if($location->image)
                            <img src="{{ asset('storage/' . $location->image) }}" alt="{{ $location->name }}" class="h-10 w-10 rounded-lg object-cover mr-3">
                            @endif
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $location->name }}</div>
                                <div class="text-sm text-gray-500">{{ $location->slug }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        <div class="max-w-xs truncate">{{ $location->description ?? '-' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                            {{ $location->sort_order }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ $location->houses_count }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('admin.locations.show', $location) }}" class="text-blue-600 hover:text-blue-900 mr-3">{{ __('admin.view') }}</a>
                        <a href="{{ route('admin.locations.edit', $location) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">{{ __('admin.edit') }}</a>
                        <form action="{{ route('admin.locations.destroy', $location) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('admin.are_you_sure_delete_location') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">{{ __('admin.delete') }}</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">{{ __('admin.no_locations_found') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection






