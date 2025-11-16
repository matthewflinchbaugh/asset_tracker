<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Asset List
            </h2>

            <div class="flex items-center space-x-2">
                <a href="{{ route('assets.index') }}"
                   class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Hierarchy View
                </a>
                @if(Route::has('assets.kanban'))
                    <a href="{{ route('assets.kanban') }}"
                       class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Kanban View
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Flash messages --}}
            @if (session('success'))
                <div class="mb-4 rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- Filters / Search --}}
                    <form method="GET" action="{{ route('assets.list') }}" class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                        {{-- Search --}}
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                Search
                            </label>
                            <input
                                type="text"
                                name="search"
                                value="{{ $search }}"
                                placeholder="Search by name, asset ID, location, manufacturer, model, serial, department, tags..."
                                class="block w-full rounded-md border-gray-300 shadow-sm text-sm"
                            >
                        </div>

                        {{-- Sort controls --}}
                        <div class="flex space-x-2">
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    Sort by
                                </label>
                                <select name="sort_by" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="name" {{ $sort_by === 'name' ? 'selected' : '' }}>Name</option>
                                    <option value="asset_tag_id" {{ $sort_by === 'asset_tag_id' ? 'selected' : '' }}>Asset ID</option>
                                    <option value="location" {{ $sort_by === 'location' ? 'selected' : '' }}>Location</option>
                                    <option value="department" {{ $sort_by === 'department' ? 'selected' : '' }}>Department</option>
                                    <option value="category" {{ $sort_by === 'category' ? 'selected' : '' }}>Category</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    Order
                                </label>
                                <select name="order" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="asc" {{ $order === 'asc' ? 'selected' : '' }}>Asc</option>
                                    <option value="desc" {{ $order === 'desc' ? 'selected' : '' }}>Desc</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button
                                    type="submit"
                                    class="inline-flex items-center px-3 py-2 rounded-md border border-gray-300 bg-white text-xs font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Apply
                                </button>
                            </div>
                        </div>
                    </form>

                    @if ($assets->isEmpty())
                        <p class="text-sm text-gray-600">
                            No assets found matching your filters.
                        </p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-700">Asset ID</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-700">Name</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-700">Department</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-700">Category</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-700">Location</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-700">PM Status</th>
                                        <th class="px-3 py-2 text-right font-medium text-gray-700">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($assets as $asset)
                                        <tr class="@if(method_exists($asset, 'is_out_of_service') && $asset->is_out_of_service) bg-red-50 @endif">
                                            {{-- Asset ID --}}
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-800">
                                                {{ $asset->asset_tag_id ?? '—' }}
                                            </td>

                                            {{-- Name + flags --}}
						<td class="px-3 py-2 whitespace-nowrap text-gray-800">
						    <div class="flex items-center space-x-2">
						        <span>{{ $asset->name }}</span>
						        <x-asset-status-badge :asset="$asset" />
						    </div>
						</td>


                                            {{-- Department --}}
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-600">
                                                {{ optional($asset->department)->name ?? '—' }}
                                            </td>

                                            {{-- Category --}}
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-600">
                                                {{ optional($asset->category)->name ?? '—' }}
                                            </td>

                                            {{-- Location --}}
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-600">
                                                {{ $asset->location ?? '—' }}
                                            </td>

                                            {{-- PM Status --}}
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-600">
                                                @php
                                                    $dueDate = $asset->next_pm_due_date ? \Illuminate\Support\Carbon::parse($asset->next_pm_due_date) : null;
                                                @endphp

                                                @if(!$dueDate)
                                                    <span class="text-xs text-gray-400">No PM date set</span>
                                                @else
                                                    <div class="flex flex-col">
                                                        <span class="text-xs text-gray-800">
                                                            {{ $dueDate->format('Y-m-d') }}
                                                        </span>
                                                        <span class="text-[11px]">
                                                            @if($asset->is_pm_overdue ?? false)
                                                                <span class="inline-flex px-1.5 py-0.5 rounded-full bg-red-100 text-red-800">
                                                                    Overdue
                                                                </span>
                                                            @elseif($asset->is_pm_due_soon ?? false)
                                                                <span class="inline-flex px-1.5 py-0.5 rounded-full bg-yellow-100 text-yellow-800">
                                                                    Due soon
                                                                </span>
                                                            @else
                                                                <span class="inline-flex px-1.5 py-0.5 rounded-full bg-green-100 text-green-800">
                                                                    OK
                                                                </span>
                                                            @endif
                                                        </span>
                                                    </div>
                                                @endif
                                            </td>

                                            {{-- Actions --}}
                                            <td class="px-3 py-2 whitespace-nowrap text-right">
                                                <div class="inline-flex items-center space-x-2">
                                                    <a href="{{ route('assets.show', $asset->id) }}"
                                                       class="text-xs text-gray-700 hover:text-gray-900 underline">
                                                        View
                                                    </a>

                                                    @php
                                                        $user = auth()->user();
                                                    @endphp

                                                    {{-- Admins/managers can edit; technicians only if allowed by controller guards --}}
                                                    @if($user && $user->role !== 'contractor')
                                                        <a href="{{ route('assets.edit', $asset->id) }}"
                                                           class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-indigo-600 text-white hover:bg-indigo-500">
                                                            Edit
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="mt-4">
                            {{ $assets->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

