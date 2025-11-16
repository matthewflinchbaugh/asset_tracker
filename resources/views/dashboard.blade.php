<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Summary cards --}}
            <div class="mb-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-4">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        Total assets
                    </div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">
                        {{ number_format($totalAssets ?? 0) }}
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-4">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        Active
                    </div>
                    <div class="mt-2 text-2xl font-bold text-emerald-700">
                        {{ number_format($activeAssets ?? 0) }}
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-4">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        Out of service
                    </div>
                    <div class="mt-2 text-2xl font-bold text-red-700">
                        {{ number_format($oosAssetsCount ?? 0) }}
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-4">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        Pending approval
                    </div>
                    <div class="mt-2 text-2xl font-bold text-amber-700">
                        {{ number_format($pendingApprovalCount ?? 0) }}
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-4">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        Critical infrastructure
                    </div>
                    <div class="mt-2 text-2xl font-bold text-indigo-700">
                        {{ number_format($criticalInfraCount ?? 0) }}
                    </div>
                </div>
            </div>

            {{-- Out of Service Assets Kanban --}}
            @if(isset($oosAssetsByDepartment) && $oosAssetsByDepartment->flatten()->isNotEmpty())
                <div class="mb-8 bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Out of Service Assets
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">
                            Assets currently marked as
                            <span class="font-semibold">temporarily out of service</span>,
                            grouped by department.
                        </p>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            @foreach ($oosAssetsByDepartment as $deptName => $assets)
                                <div class="bg-gray-50 rounded-lg border border-gray-200 flex flex-col">
                                    <div class="px-3 py-2 bg-gray-100 border-b border-gray-200">
                                        <div class="font-semibold text-sm text-gray-800">
                                            {{ $deptName }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $assets->count() }} out of service
                                        </div>
                                    </div>

                                    <div class="p-3 space-y-3">
                                        @forelse ($assets as $asset)
                                            <a href="{{ route('assets.show', $asset->id) }}"
                                               class="block bg-white rounded-md border border-red-200 p-3 shadow-sm hover:shadow-md hover:border-red-400 transition">
                                                <div class="flex items-center justify-between">
                                                    <div class="font-semibold text-sm text-gray-900">
                                                        {{ $asset->name ?? 'Untitled Asset' }}
                                                    </div>
                                                    @if($asset->asset_tag_id)
                                                        <span class="text-[11px] text-gray-500">
                                                            {{ $asset->asset_tag_id }}
                                                        </span>
                                                    @endif
                                                </div>

                                                <div class="mt-1 text-xs text-gray-600">
                                                    {{ $asset->location ?? 'No location set' }}
                                                </div>

                                                <div class="mt-1 text-[11px] text-gray-500">
                                                    Category: {{ optional($asset->category)->name ?? 'N/A' }}
                                                </div>
                                            </a>
                                        @empty
                                            <p class="text-xs text-gray-500">
                                                No out of service assets in this department.
                                            </p>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="mb-8 bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-sm text-gray-600">
                        There are currently no assets marked as out of service.
                    </div>
                </div>
            @endif

            {{-- Small cards row --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                {{-- Pending Approval Assets --}}
                <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                    <div class="p-4 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">
                            Pending Approval Assets
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">
                            Recently submitted assets waiting for admin approval.
                        </p>
                    </div>

                    <div class="p-4">
                        @if(isset($pendingApprovalAssets) && $pendingApprovalAssets->isNotEmpty())
                            <ul class="divide-y divide-gray-100">
                                @foreach ($pendingApprovalAssets as $asset)
                                    <li class="py-2 flex items-start justify-between">
                                        <div>
                                            <a href="{{ route('assets.show', $asset->id) }}"
                                               class="text-sm font-medium text-indigo-700 hover:text-indigo-900">
                                                {{ $asset->name ?? 'Untitled Asset' }}
                                            </a>
                                            <div class="text-xs text-gray-500">
                                                {{ optional($asset->department)->name ?? 'No department' }}
                                                @if($asset->created_at)
                                                    &nbsp;·&nbsp;
                                                    Submitted {{ $asset->created_at->format('M d, Y') }}
                                                @endif
                                            </div>
                                        </div>
                                        @if($asset->asset_tag_id)
                                            <div class="text-[11px] text-gray-500 ml-3">
                                                {{ $asset->asset_tag_id }}
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-xs text-gray-500">
                                No assets are currently pending approval.
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Recent Maintenance Activity --}}
                <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                    <div class="p-4 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">
                            Recent Maintenance Activity
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">
                            Most recent non-draft maintenance logs on assets you can see.
                        </p>
                    </div>

                    <div class="p-4">
                        @if(isset($recentMaintenanceLogs) && $recentMaintenanceLogs->isNotEmpty())
                            <ul class="divide-y divide-gray-100">
                                @foreach ($recentMaintenanceLogs as $log)
                                    @php
                                        $asset = $log->asset;

                                        // Best-effort detection of "out of service" events.
                                        $isOosLog = false;
                                        if (!empty($log->event_type)) {
                                            $normalizedEvent = strtolower($log->event_type);
                                            $isOosLog =
                                                str_contains($normalizedEvent, 'unexpected') ||
                                                str_contains($normalizedEvent, 'unscheduled') ||
                                                str_contains($normalizedEvent, 'out_of_service') ||
                                                str_contains($normalizedEvent, 'out of service') ||
                                                str_contains($normalizedEvent, 'oos');
                                        }

                                        // If your MaintenanceLog has a dedicated flag like marks_out_of_service, this supports it:
                                        if (property_exists($log, 'marks_out_of_service') && $log->marks_out_of_service) {
                                            $isOosLog = true;
                                        }
                                    @endphp

                                    @if($asset)
                                        <li class="py-2 flex items-start justify-between">
                                            <div>
                                                <a href="{{ route('assets.show', $asset->id) }}"
                                                   class="text-sm font-medium text-gray-900 hover:text-gray-700">
                                                    {{ $asset->name ?? 'Untitled Asset' }}
                                                </a>
                                                <div class="text-xs text-gray-500">
                                                    {{ optional($asset->department)->name ?? 'No department' }}
                                                    @if($log->service_date)
                                                        &nbsp;·&nbsp;
                                                        {{ \Carbon\Carbon::parse($log->service_date)->format('M d, Y') }}
                                                    @elseif($log->created_at)
                                                        &nbsp;·&nbsp;
                                                        {{ $log->created_at->format('M d, Y H:i') }}
                                                    @endif
                                                </div>
                                                <div class="mt-0.5 flex flex-wrap items-center gap-2">
                                                    <span class="text-xs text-gray-600">
                                                        Event: {{ $log->event_type_display ?? $log->event_type ?? 'Log' }}
                                                    </span>

                                                    @if($isOosLog)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-red-100 text-red-700 border border-red-200">
                                                            Out of Service Event
                                                        </span>
                                                    @elseif($asset->temporarily_out_of_service)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-red-50 text-red-700 border border-red-200">
                                                            Asset OOS
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($asset->asset_tag_id)
                                                <div class="text-[11px] text-gray-500 ml-3 text-right">
                                                    {{ $asset->asset_tag_id }}
                                                </div>
                                            @endif
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        @else
                            <p class="text-xs text-gray-500">
                                No recent maintenance activity found.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

