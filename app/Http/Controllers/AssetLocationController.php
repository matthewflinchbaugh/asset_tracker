<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\WebhookSender; // Include Webhook Trait

class AssetLocationController extends Controller
{
    use WebhookSender; // Use Webhook Trait

    /**
     * Show the form for editing the asset's location.
     */
    public function edit(Asset $asset)
    {
        // Ensure user has permission to view this asset
        $user = Auth::user();
        if ($user->role === 'technician') {
            if (!$user->relationLoaded('visibleCategories')) {
                $user->load('visibleCategories');
            }
            $allowedCategoryIds = $user->visibleCategories->pluck('id');
            // We also need to check tags
            $allowedTagIds = $user->visibleCategories->pluck('id'); 
            
            if (!$allowedCategoryIds->contains($asset->category_id) && !$asset->tags->pluck('id')->intersect($allowedTagIds)->isNotEmpty()) {
                return redirect()->route('assets.index')->with('error', 'You do not have permission to view this asset.');
            }
        }
        
        return view('technician.assets.edit_location', compact('asset'));
    }

    /**
     * Update the asset's location in storage.
     */
    public function update(Request $request, Asset $asset)
    {
        $user = Auth::user();
        if ($user->role === 'technician') {
            if (!$user->relationLoaded('visibleCategories')) {
                $user->load('visibleCategories');
            }
            $allowedCategoryIds = $user->visibleCategories->pluck('id');
            // We also need to check tags
            $allowedTagIds = $user->visibleCategories->pluck('id'); 
            
            if (!$allowedCategoryIds->contains($asset->category_id) && !$asset->tags->pluck('id')->intersect($allowedTagIds)->isNotEmpty()) {
                return redirect()->route('assets.index')->with('error', 'You do not have permission to edit this asset.');
            }
        }

        $validated = $request->validate([
            'location' => 'required|string|max:255',
            'pm_templates' => 'nullable|array',
            'pm_templates.*.template_id' => 'nullable|integer|exists:checklist_templates,id',
            'pm_templates.*.component_name' => 'nullable|string|max:255',]);

        $asset->update([
            'location' => $validated['location'],
        ]);
        // === PM template sync (idempotent) ===
        if ($request->has('pm_templates')) {
            $rows = array_values(array_filter($request->input('pm_templates', []), function ($row) {
                return isset($row['template_id']) && $row['template_id'] !== '' && $row['template_id'] !== null;
            }));

            // Replace all current rows for this asset
            DB::table('asset_checklist_template')->where('asset_id', $asset->id)->delete();

            $now = now();
            foreach ($rows as $row) {
                DB::table('asset_checklist_template')->insert([
                    'asset_id' => $asset->id,
                    'checklist_template_id' => (int) $row['template_id'],
                    'component_name' => $row['component_name'] ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }


        // Trigger webhook for asset update
        $this->sendWebhooks('ASSET_UPDATED', $asset);

        return redirect()->route('assets.show', $asset->id)
                         ->with('success', 'Asset location updated successfully.');
    }
}
