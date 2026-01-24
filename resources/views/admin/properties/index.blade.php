@extends('layouts.app')

@section('title', __('admin.manage_properties'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-12">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 sm:mb-8 gap-4">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ __('admin.manage_properties') }}</h1>
        <a href="{{ route('admin.properties.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors text-sm sm:text-base font-medium text-center">
            {{ __('admin.create_new_property') }}
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
        @forelse($properties as $property)
        <div class="bg-white shadow-md rounded-lg p-4 border border-gray-200">
            <div class="mb-3">
                <h3 class="text-base font-semibold text-gray-900">{{ $property->name }}</h3>
            </div>
            <div class="space-y-2 mb-4">
                <div class="flex items-start text-sm">
                    <span class="font-medium text-gray-700 w-24">{{ __('admin.address') }}:</span>
                    <span class="text-gray-900 flex-1">{{ $property->address }}</span>
                </div>
                <div class="flex items-center text-sm">
                    <span class="font-medium text-gray-700 w-24">{{ __('admin.city') }}:</span>
                    <span class="text-gray-900">{{ $property->city }}</span>
                </div>
                <div class="flex items-center text-sm">
                    <span class="font-medium text-gray-700 w-24">{{ __('admin.postal_code') }}:</span>
                    <span class="text-gray-900">{{ $property->postal_code }}</span>
                </div>
                <div class="flex items-center text-sm">
                    <span class="font-medium text-gray-700 w-24">{{ __('admin.rooms_count') }}:</span>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        {{ $property->rooms_count }}
                    </span>
                </div>
            </div>
            <div class="flex gap-2 pt-3 border-t border-gray-200">
                <a href="{{ route('admin.properties.show', $property) }}" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors text-center text-sm font-medium">
                    {{ __('admin.view') }}
                </a>
                <a href="{{ route('admin.properties.edit', $property) }}" class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors text-center text-sm font-medium">
                    {{ __('admin.edit') }}
                </a>
                <form action="{{ route('admin.properties.destroy', $property) }}" method="POST" class="flex-1" onsubmit="return confirm('{{ __('admin.are_you_sure_delete_property') }}');">
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
            {{ __('admin.no_properties_found') }}
        </div>
        @endforelse
    </div>

    <!-- Desktop Table View -->
    <div class="hidden md:block bg-white shadow-md rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.property') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.address') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.city') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.postal_code') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.rooms_count') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($properties as $property)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $property->name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $property->address }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $property->city }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $property->postal_code }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ $property->rooms_count }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('admin.properties.show', $property) }}" class="text-blue-600 hover:text-blue-900 mr-3">{{ __('admin.view') }}</a>
                        <a href="{{ route('admin.properties.edit', $property) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">{{ __('admin.edit') }}</a>
                        <form action="{{ route('admin.properties.destroy', $property) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('admin.are_you_sure_delete_property') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">{{ __('admin.delete') }}</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">{{ __('admin.no_properties_found') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection










