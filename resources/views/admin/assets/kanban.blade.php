<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Asset Kanban View (Grouped by Department)') }}
            </h2>
            <div class="flex space-x-2">
                 <a href="{{ route('assets.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                    Hierarchy View
                </a>
                 <a href="{{ route('assets.list') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                    List View
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <!-- Scrollable Container -->
            <div class="flex space-x-4 overflow-x-auto pb-4">
                
                @forelse ($assetsByGroup as $groupName => $assets)
                    <!-- Kanban Column -->
                    <div class="flex-shrink-0 w-80 bg-gray-100 rounded-lg shadow-sm">
                        <!-- Column Header -->
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="text-md font-semibold text-gray-800">{{ $groupName }}</h3>
                            <span class="text-sm text-gray-500">{{ $assets->count() }} {{ Str::plural('Asset', $assets->count()) }}</span>
                        </div>
                        
                        <!-- Column Cards -->
                        <div class="p-4 space-y-3 overflow-y-auto" style="max-height: 70vh;">
                            @foreach ($assets as $asset)
                                <a href="{{ route('assets.show', $asset->id) }}" class="block p-4 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                                    <div class="font-semibold text-gray-900">{{ $asset->name }}</div>
                                    <div class="text-sm text-gray-600">{{ $asset->asset_tag_id }}</div>
                                    <div class="mt-2 text-xs text-gray-500">
                                        {{ $asset->location ?? 'N/A' }} | {{ $asset->category->name ?? 'N/A' }}
                                    </div>
                                    <!-- Tags -->
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        @foreach ($asset->tags as $tag)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-800">
                                                {{ $tag->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500">No active assets found.</p>
                @endforelse

            </div>
        </div>
    </div>
</x-app-layout>
