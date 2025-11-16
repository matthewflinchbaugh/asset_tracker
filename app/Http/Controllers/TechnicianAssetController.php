<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Department;
use App\Models\Tag;
use App\Models\ChecklistTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Traits\WebhookSender;

class TechnicianAssetController extends Controller
{
    use WebhookSender;

    /**
     * Show the form for a technician to submit a new asset.
     */
    public function create()
    {
        $departments        = Department::orderBy('name')->get();
        // "Tags" still point at the categories table for primary tag group
        $tags               = Tag::orderBy('name')->get();
        $checklistTemplates = ChecklistTemplate::orderBy('name')->get();

        return view('technician.assets.create', compact('departments', 'tags', 'checklistTemplates'));
    }

    /**
     * Store a newly submitted asset (status: pending_approval).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                     => 'required|string|max:255',
	    'is_critical_infrastructure' => 'nullable|boolean',

            'department_id'            => 'required|exists:departments,id',
            'category_id'              => 'required|exists:categories,id', // primary tag group
            'location'                 => 'nullable|string|max:255',
            'manufacturer'             => 'nullable|string|max:255',
            'model_number'             => 'nullable|string|max:255',
            'serial_number'            => 'nullable|string|max:255',
            'purchase_cost'            => 'nullable|numeric|min:0',
            'purchase_date'            => 'nullable|date',
            'warranty_expiration_date' => 'nullable|date|after_or_equal:purchase_date',
            'pm_interval_value'        => 'nullable|integer|min:1',
            'pm_interval_unit'         => 'nullable|in:days,weeks,months,years',
            'pm_procedure_notes'       => 'nullable|string',
            'commissioning_notes'      => 'nullable|string',
            'next_pm_due_date'         => 'nullable|date',

            // PM checklist assignments
            'checklist_assignments'                  => 'nullable|array',
            'checklist_assignments.*.template_id'    => 'exists:checklist_templates,id',
            'checklist_assignments.*.component_name' => 'nullable|string|max:255',
        ]);

        // --- Generate Temporary Asset Tag ID (TEMP-1, TEMP-2, ...) ---
        $latestTempAsset = Asset::where('temp_asset_tag_id', 'LIKE', 'TEMP-%')
            ->orderBy('id', 'desc')
            ->first();

        $nextIdNumber = 1;
        if ($latestTempAsset) {
            $lastNumber   = (int) str_replace('TEMP-', '', $latestTempAsset->temp_asset_tag_id);
            $nextIdNumber = $lastNumber + 1;
        }

        $tempAssetTagId                 = 'TEMP-' . $nextIdNumber;
        $validated['temp_asset_tag_id'] = $tempAssetTagId;

        $validated['created_by_user_id'] = Auth::id();
        $validated['status']             = 'pending_approval';
	$validated['is_critical_infrastructure'] = $request->boolean('is_critical_infrastructure');


        // Create the pending asset
        $asset = Asset::create($validated);

        // Attach PM checklists (optional)
        $assignments = $request->input('checklist_assignments', []);
        $sync        = [];

        if (is_array($assignments)) {
            foreach ($assignments as $row) {
                $templateId    = $row['template_id'] ?? null;
                $componentName = $row['component_name'] ?? null;

                if (!$templateId) {
                    continue;
                }

                $sync[$templateId] = [
                    'component_name' => $componentName,
                ];
            }
        }

        if (method_exists($asset, 'checklistTemplates')) {
            $asset->checklistTemplates()->sync($sync);
        }

        // Send Webhook: PENDING_SUBMITTED
        $this->sendWebhooks('PENDING_SUBMITTED', $asset);

        return redirect()
            ->route('dashboard')
            ->with('success', "Equipment submitted for approval with Temp ID: {$tempAssetTagId}.");
    }
}

