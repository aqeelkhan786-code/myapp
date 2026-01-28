@extends('layouts.app')

@section('title', __('admin.manage_houses'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-12">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 sm:mb-8 gap-4">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ __('admin.manage_houses') }}</h1>
        <a href="{{ route('admin.houses.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors text-sm sm:text-base font-medium text-center">
            {{ __('admin.create_new_house') }}
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

    <!-- Mobile Card View (phones/tablets) -->
    <div class="block lg:hidden space-y-4">
        @forelse($houses as $house)
        <div class="bg-white shadow-md rounded-lg p-4 border border-gray-200">
            <div class="flex items-start gap-4 mb-4">
                @if($house->image)
                <img src="{{ asset('storage/' . $house->image) }}" alt="{{ $house->name }}" class="h-16 w-16 rounded-lg object-cover flex-shrink-0">
                @else
                <div class="h-16 w-16 rounded-lg bg-gray-200 flex items-center justify-center flex-shrink-0">
                    <span class="text-xs text-gray-400 text-center px-1">{{ __('admin.no_image') }}</span>
                </div>
                @endif
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-semibold text-gray-900 truncate">{{ $house->name }}</h3>
                    <p class="text-xs text-gray-500 truncate mt-1">{{ $house->slug }}</p>
                </div>
            </div>
            <div class="space-y-2.5 mb-4">
                <div class="flex items-center text-sm">
                    <span class="font-medium text-gray-700 w-24">{{ __('admin.location') }}:</span>
                    <span class="text-gray-900">{{ $house->location->name }}</span>
                </div>
                <div class="text-sm">
                    <span class="font-medium text-gray-700">{{ __('admin.description') }}:</span>
                    <p class="text-gray-900 mt-1">{{ $house->description ?? '-' }}</p>
                </div>
                <div class="flex items-center text-sm">
                    <span class="font-medium text-gray-700 w-24">{{ __('admin.rooms_count') }}:</span>
                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        {{ $house->rooms_count }}
                    </span>
                </div>
            </div>
            <div class="flex gap-2 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.houses.show', $house) }}" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors text-center text-sm font-medium">
                    {{ __('admin.view') }}
                </a>
                <a href="{{ route('admin.houses.edit', $house) }}" class="flex-1 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors text-center text-sm font-medium">
                    {{ __('admin.edit') }}
                </a>
                <form action="{{ route('admin.houses.destroy', $house) }}" method="POST" class="flex-1" onsubmit="return confirm('{{ __('admin.are_you_sure_delete_house') }}');">
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
            {{ __('admin.no_houses_found') }}
        </div>
        @endforelse
    </div>

    <!-- Desktop Table View (lg and up) -->
    <div class="hidden lg:block bg-white shadow-md rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.house') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.location') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.description') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.rooms_count') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($houses as $house)
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            @if($house->image)
                            <img src="{{ asset('storage/' . $house->image) }}" alt="{{ $house->name }}" class="h-10 w-10 rounded-lg object-cover mr-3">
                            @endif
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $house->name }}</div>
                                <div class="text-xs text-gray-500 mt-1">{{ $house->slug }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $house->location->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        <div class="max-w-xs truncate">{{ $house->description ?? '-' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ $house->rooms_count }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.houses.show', $house) }}" class="text-blue-600 hover:text-blue-800 transition-colors">{{ __('admin.view') }}</a>
                            <span class="text-gray-300">|</span>
                            <a href="{{ route('admin.houses.edit', $house) }}" class="text-green-600 hover:text-green-800 transition-colors">{{ __('admin.edit') }}</a>
                            <span class="text-gray-300">|</span>
                            <form action="{{ route('admin.houses.destroy', $house) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('admin.are_you_sure_delete_house') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 transition-colors">{{ __('admin.delete') }}</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">{{ __('admin.no_houses_found') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection










