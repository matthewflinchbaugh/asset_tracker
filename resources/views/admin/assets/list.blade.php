<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Assets - List View') }}
            </h2>
            <div class="flex space-x-2">
                 <a href="{{ route('assets.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                    Hierarchy View
                </a>
                 <a href="{{ route('assets.kanban') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                    Kanban View
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <!-- Search Bar -->
                    <div class="mb-4">
                        <form method="GET" action="{{ route('assets.list') }}">
                            <div class="flex">
                                <input type="text" name="search" placeholder="Search by name, ID, or location..." 
                                       class="block w-full rounded-l-md shadow-sm border-gray-300"
                                       value="{{ $search ?? '' }}">
                                <!-- Hidden fields to preserve sort order while searching -->
                                <input type="hidden" name="sort_by" value="{{ $sort_by ?? 'name' }}">
                                <input type="hidden" name="order" value="{{ $order ?? 'asc' }}">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-r-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                    Search
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    @php
                                        // Helper function to generate sort links
                                        $sortLink = fn($field) => route('assets.list', [
                                            'search' => $search,
                                            'sort_by' => $field,
                                            'order' => ($sort_by == $field && $order == 'asc') ? 'desc' : 'asc'
                                        ]);
                                        $sortArrow = fn($field) => ($sort_by == $field) ? ($order == 'asc' ? ' &uarr;' : ' &darr;') : '';
                                    @endphp
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        <a href="{{ $sortLink('asset_tag_id') }}">ID{!! $sortArrow('asset_tag_id') !!}</a>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        <a href="{{ $sortLink('name') }}">Name{!! $sortArrow('name') !!}</a>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        <a href="{{ $sortLink('category') }}">Primary Tag{!! $sortArrow('category') !!}</a>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        <a href="{{ $sortLink('department') }}">Department{!! $sortArrow('department') !!}</a>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        <a href="{{ $sortLink('location') }}">Location{!! $sortArrow('location') !!}</a>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($assets as $asset)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $asset->asset_tag_id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $asset->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $asset->category->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $asset->department->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $asset->location }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center space-x-4">
                                                <a href="{{ route('assets.show', $asset->id) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                                @if (auth()->user()->role == 'admin')
                                                    <a href="{{ route('assets.edit', $asset->id) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            No assets found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
