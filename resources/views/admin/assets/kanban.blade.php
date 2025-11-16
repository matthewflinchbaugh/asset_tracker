<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Asset Kanban View (Grouped by Department)') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('assets.index') }}"
                   class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                    Hierarchy View
                </a>
                <a href="{{ route('assets.list') }}"
                   class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                    List View
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4">

                    <div class="flex space-x-4 overflow-x-auto pb-4">
                        @forelse ($assetsByGroup as $groupName => $assets)
                            <!-- Kanban Column -->
                            <div class="flex-shrink-0 w-80 bg-gray-100 rounded-lg shadow-sm">
                                <!-- Column Header -->
                                <div class="p-4 border-b border-gray-200">
                                    <h3 class="text-md font-semibold text-gray-800">
                                        {{ $groupName }}
                                    </h3>
                                    <span class="text-sm text-gray-600">
                                        {{ $assets->count() }} {{ Str::plural('Asset', $assets->count()) }}
                                    </span>
                                </div>

                                <!-- Column Cards -->
                                <div class="p-4 space-y-3 overflow-y-auto" style="max-height: 70vh;">
					@foreach ($assets as $asset)
    @php
        $isOut     = $asset->is_out_of_service;
        $isDueSoon = $asset->is_pm_due_soon;
        $isOverdue = $asset->is_pm_overdue;

        $cardClasses = 'block p-3 rounded-lg shadow-sm hover:shadow-md transition-shadow border relative overflow-hidden ';
        $style = '';

        if ($isOut && ($isDueSoon || $isOverdue)) {
            // Striped: out of service + PM due
            $cardClasses .= 'border-red-500';
            $style = 'background-image: repeating-linear-gradient(45deg, #fee2e2, #fee2e2 10px, #fef3c7 10px, #fef3c7 20px);';
        } elseif ($isOut) {
            // Out of service only
            $cardClasses .= 'bg-red-50 border-red-500';
        } elseif ($isOverdue) {
            // PM overdue
            $cardClasses .= 'bg-yellow-100 border-yellow-500';
        } elseif ($isDueSoon) {
            // PM due soon
            $cardClasses .= 'bg-orange-50 border-orange-400';
        } else {
            $cardClasses .= 'bg-white border-gray-200';
        }
    @endphp

    <a href="{{ route('assets.show', $asset->id) }}"
       class="{{ $cardClasses }}"
       style="{{ $style }}">
        <div class="flex items-start justify-between">
            <div>
		<div class="flex items-center justify-between">
		    <div class="font-semibold text-gray-900">
        		{{ $asset->name ?? 'Untitled Asset' }}
		    </div>
		    <x-asset-status-badge :asset="$asset" />
		</div>
		<div class="text-xs text-gray-600 mt-1">
		    {{ $asset->asset_tag_id ?? 'No ID' }}
		</div>

            </div>

            <div class="flex flex-col items-end space-y-1">
                @if ($isOut)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[0.65rem] font-semibold bg-red-100 text-red-700">
                        Out of Service
                    </span>
                @endif

                @if ($isOverdue)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[0.65rem] font-semibold bg-yellow-100 text-yellow-800">
                        PM Overdue
                    </span>
                @elseif ($isDueSoon)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[0.65rem] font-semibold bg-orange-100 text-orange-800">
                        PM Due Soon
                    </span>
                @endif
            </div>
        </div>

        {{-- keep the rest of your card (tags, location, next PM date, etc.) --}}
    </a>
@endforeach

                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500">No active assets found.</p>
                        @endforelse
                    </div>
			<div class="mt-4 text-xs text-gray-500">
    <div class="flex flex-wrap items-center gap-4">
        <div class="flex items-center gap-1">
            <span class="inline-block w-3 h-3 rounded bg-red-200 border border-red-500"></span>
            <span>Out of service</span>
        </div>

        <div class="flex items-center gap-1">
            <span class="inline-block w-3 h-3 rounded bg-yellow-200 border border-yellow-500"></span>
            <span>PM overdue</span>
        </div>

        <div class="flex items-center gap-1">
            <span class="inline-block w-3 h-3 rounded bg-orange-200 border border-orange-400"></span>
            <span>PM due soon (≤ 7 days)</span>
        </div>

        <div class="flex items-center gap-1">
            <span class="inline-block w-3 h-3 rounded border border-red-500"
                  style="background-image: repeating-linear-gradient(45deg, #fee2e2, #fee2e2 4px, #fef3c7 4px, #fef3c7 8px);">
            </span>
            <span>Out of service + PM due</span>
        </div>
    </div>
</div>



                </div>
            </div>
        </div>
    </div>
</x-app-layout>

