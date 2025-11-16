@props(['asset'])

@php
    $status = $asset->status ?? 'active';

    $baseClasses = 'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium';

    $map = [
        'pending_approval' => ['bg-yellow-100 text-yellow-800', 'Pending approval'],
        'inactive'         => ['bg-gray-200 text-gray-800', 'Inactive'],
        'retired'          => ['bg-red-100 text-red-800', 'Retired'],
        'decommissioned'   => ['bg-red-100 text-red-800', 'Decommissioned'],
        'active'           => ['bg-green-100 text-green-800', 'Active'],
    ];

    [$statusClasses, $label] = $map[$status] ?? [
        'bg-gray-100 text-gray-800',
        ucfirst(str_replace('_', ' ', $status)),
    ];

    // Out-of-service flag (safe even if accessor not present)
    $isOos = false;
    if (isset($asset->is_out_of_service)) {
        $isOos = (bool) $asset->is_out_of_service;
    } elseif (method_exists($asset, 'getIsOutOfServiceAttribute')) {
        try {
            $isOos = (bool) $asset->is_out_of_service;
        } catch (\Throwable $e) {
            $isOos = false;
        }
    }

    $isPmOverdue = $asset->is_pm_overdue ?? false;
    $isPmDueSoon = $asset->is_pm_due_soon ?? false;
@endphp

<span class="{{ $baseClasses }} {{ $statusClasses }}">
    {{ $label }}
</span>

@if($isOos)
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-red-200 text-red-800 ml-1">
        OOS
    </span>
@endif

@if($isPmOverdue)
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-red-100 text-red-800 ml-1">
        PM overdue
    </span>
@elseif($isPmDueSoon)
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-yellow-100 text-yellow-800 ml-1">
        PM due soon
    </span>
@endif

