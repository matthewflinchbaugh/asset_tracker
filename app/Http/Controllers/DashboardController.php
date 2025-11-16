<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Asset;
use App\Models\MaintenanceLog;

class DashboardController extends Controller
{
    /**
     * Main dashboard screen (first page after login, route '/').
     */
    public function __invoke(Request $request)
    {
        $user = Auth::user();

        // Base visibility-scoped query for this user
        $baseQuery = Asset::query();

        // Technician visibility: reuse the same rules used elsewhere
        if ($user->role === 'technician') {
            if (!$user->relationLoaded('visibleCategories')) {
                $user->load('visibleCategories');
            }

            $allowedCategoryIds = $user->visibleCategories->pluck('id');

            if ($allowedCategoryIds->isNotEmpty()) {
                $baseQuery->where(function ($q) use ($allowedCategoryIds) {
                    $q->whereIn('category_id', $allowedCategoryIds)
                        ->orWhereHas('tags', function ($subQuery) use ($allowedCategoryIds) {
                            $subQuery->whereIn('categories.id', $allowedCategoryIds);
                        });
                });
            } else {
                // Tech with no visible categories => no results anywhere
                $baseQuery->whereRaw('1 = 0');
            }
        }

        // ---- Out-of-service assets grouped by department (Kanban) ----
        $oosAssetsByDepartment = (clone $baseQuery)
            ->with(['department', 'category'])
            ->where('temporarily_out_of_service', true)
            ->whereIn('status', ['active', 'pending_approval'])
            ->orderBy('name')
            ->get()
            ->groupBy(function ($asset) {
                return $asset->department->name ?? 'Unassigned';
            });

        // ---- Summary stats (respecting same visibility rules) ----
        $totalAssets = (clone $baseQuery)->count();

        $activeAssets = (clone $baseQuery)
            ->where('status', 'active')
            ->count();

        $oosAssetsCount = (clone $baseQuery)
            ->where('temporarily_out_of_service', true)
            ->count();

        $pendingApprovalCount = (clone $baseQuery)
            ->where('status', 'pending_approval')
            ->count();

        $criticalInfraCount = (clone $baseQuery)
            ->where('is_critical_infrastructure', true)
            ->count();

        // ---- Small cards data ----

        // Pending approval assets (top 5)
        $pendingApprovalAssets = (clone $baseQuery)
            ->with(['department'])
            ->where('status', 'pending_approval')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Visible asset IDs for maintenance-log scoping
        $visibleAssetIds = (clone $baseQuery)->pluck('id');

        // Recent maintenance activity (top 5 non-draft logs on visible assets)
        $recentMaintenanceLogs = MaintenanceLog::with(['asset.department'])
            ->where('is_draft', false)
            ->when($visibleAssetIds->isNotEmpty(), function ($q) use ($visibleAssetIds) {
                $q->whereIn('asset_id', $visibleAssetIds);
            }, function ($q) {
                // If no visible assets, force empty result
                $q->whereRaw('1 = 0');
            })
            ->orderByDesc('service_date')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'oosAssetsByDepartment'  => $oosAssetsByDepartment,
            'totalAssets'           => $totalAssets,
            'activeAssets'          => $activeAssets,
            'oosAssetsCount'        => $oosAssetsCount,
            'pendingApprovalCount'  => $pendingApprovalCount,
            'criticalInfraCount'    => $criticalInfraCount,
            'pendingApprovalAssets' => $pendingApprovalAssets,
            'recentMaintenanceLogs' => $recentMaintenanceLogs,
        ]);
    }
}

