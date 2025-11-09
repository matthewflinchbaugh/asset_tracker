<?php

namespace App\Http\Controllers; // <-- FIX: Was App\Http/Controllers

use App\Models\Asset;
use App\Models\Department;
use App\Models\Tag;
use App\Models\MaintenanceLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Traits\WebhookSender; 

class PendingAssetController extends Controller
{
    use WebhookSender; 

    /**
     * Display a listing of pending assets for admins.
     */
    public function index()
    {
        $pendingAssets = Asset::with('creator', 'department', 'category')
                                ->where('status', 'pending_approval')
                                ->orderBy('created_at', 'desc')
                                ->get();
                                
        return view('admin.pending.index', compact('pendingAssets'));
    }

    /**
     * Show the form for an admin to review and approve a pending asset.
     */
    public function edit(Asset $asset)
    {
        $departments = Department::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();
        
        $assignedTagIds = $asset->tags->pluck('id')->toArray();
        
        return view('admin.pending.edit', compact('asset', 'departments', 'tags', 'assignedTagIds'));
    }

    /**
     * Update (approve) the pending asset.
     */
    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'location' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'model_number' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'purchase_cost' => 'required|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'warranty_expiration_date' => 'nullable|date|after_or_equal:purchase_date',
            'pm_interval_value' => 'nullable|integer|min:1',
            'pm_interval_unit' => 'nullable|in:days,weeks,months,years',
            'pm_procedure_notes' => 'nullable|string',
            'commissioning_notes' => 'nullable|string',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:categories,id',
        ]);

        // --- Generate the Final Asset ID ---
        $department = $asset->department;
        $prefix = $department->abbreviation;
        $latestAsset = Asset::where('asset_tag_id', 'LIKE', $prefix . '-%')
                            ->where('asset_tag_id', '!=', null) 
                            ->orderBy('asset_tag_id', 'desc')
                            ->first();
        $nextIdNumber = 1;
        if ($latestAsset) {
            $lastNumber = (int)str_replace($prefix . '-', '', $latestAsset->asset_tag_id);
            $nextIdNumber = $lastNumber + 1;
        }
        $finalAssetTagId = $prefix . '-' . Str::padLeft($nextIdNumber, 5, '0');
        // --- End ID Generation ---

        $validated['asset_tag_id'] = $finalAssetTagId;
        $validated['temp_asset_tag_id'] = null;
        $validated['status'] = 'active';

        // 1. Update the asset with all validated data
        $asset->update($validated);
        
        // 2. Sync Tags
        $asset->tags()->sync($request->input('tag_ids', []));


        // --- Create the Commissioning Log ---
        if (!empty($validated['commissioning_notes'])) {
            MaintenanceLog::create([
                'asset_id' => $asset->id,
                'user_id' => $asset->created_by_user_id,
                'event_type' => 'commissioning',
                'service_date' => $asset->created_at,
                'description_of_work' => $validated['commissioning_notes'],
            ]);
        }
        
        // 3. Send Webhook: PENDING_APPROVED
        $this->sendWebhooks('PENDING_APPROVED', $asset); // <-- FIXED: Pass model

        return redirect()->route('pending.index')
                         ->with('success', 'Asset approved with new ID: ' . $finalAssetTagId);
    }

    /**
     * Remove (deny) the pending asset submission.
     */
    public function destroy(Asset $asset)
    {
        if ($asset->status == 'pending_approval') {
            $asset->delete();
            return redirect()->route('pending.index')
                             ->with('success', 'Pending submission has been denied and deleted.');
        }
        return redirect()->route('pending.index')->with('error', 'This asset is not pending.');
    }
}
