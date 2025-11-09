<x-app-layout>
    <!-- FIX: Add x-cloak CSS definition to the header -->
    <x-slot name="header">
        <style>
            [x-cloak] { display: none !important; }
        </style>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Assets') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="flex flex-col md:flex-row justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-2 md:mb-0">
                            All Assets (Grouped by System)
                        </h3>

                        <!-- Action Buttons -->
                        <div class="flex space-x-2">
                            <!-- Kanban View Button -->
                            <a href="{{ route('assets.kanban') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                                Kanban View
                            </a>
                            
                            <!-- Role-based create button -->
                            @if (auth()->user()->role == 'admin')
                                <a href="{{ route('assets.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                    Add New Asset
                                </a>
                            @elseif (auth()->user()->role == 'technician')
                                 <a href="{{ route('technician.assets.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500">
                                    Submit New Equipment
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Search Bar -->
                    <div class="mb-4">
                        <form method="GET" action="{{ route('assets.index') }}">
                            <div class="flex">
                                <input type="text" name="search" placeholder="Search by name, ID, or location..." 
                                       class="block w-full rounded-l-md shadow-sm border-gray-300"
                                       value="{{ $search ?? '' }}">
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
                                    <th class="w-12 px-6 py-3"></th> <!-- Column for Expander -->
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Primary Tag</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <!-- FIX: Use TBODY for x-data scope -->
                            @forelse ($assets as $asset)
                                <tbody x-data="{ open: false }" class="bg-white divide-y divide-gray-200">
                                    <tr class="hover:bg-gray-50">
                                        <!-- Expander Column -->
                                        <td class="px-6 py-4">
                                            @if ($asset->children->count() > 0)
                                                <button @click="open = !open" class="text-gray-400 hover:text-gray-700">
                                                    <svg x-show="!open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    <svg x-show="open" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                </button>
                                            @endif
                                        </td>
                                        
                                        <!-- Asset Details -->
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

                                    <!-- Collapsible Children Row -->
                                    @if ($asset->children->count() > 0)
                                        <!-- FIX: Add x-cloak to hide by default -->
                                        <tr x-show="open" x-cloak class="bg-gray-50">
                                            <td colspan="7" class="px-4 py-2">
                                                <div class="pl-12 pr-4">
                                                    <h5 class="text-xs font-semibold text-gray-500 uppercase mb-2">Components:</h5>
                                                    <ul class="divide-y divide-gray-200">
                                                        @foreach ($asset->children as $child)
                                                            <li class="py-2 flex justify-between items-center">
                                                                <div>
                                                                    <a href="{{ route('assets.show', $child->id) }}" class="text-sm font-medium text-blue-600 hover:text-blue-900">{{ $child->name }}</a>
                                                                    <span class="text-xs text-gray-500 ml-2">({{ $child->asset_tag_id }})</span>
                                                                </div>
                                                                <span class="text-sm text-gray-600">{{ $child->location }}</span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            @empty
                                <tbody>
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            No assets found.
                                        </td>
                                    </tr>
                                </tbody>
                            @endforelse
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
