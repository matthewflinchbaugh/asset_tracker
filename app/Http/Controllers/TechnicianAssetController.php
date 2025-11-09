<?php

namespace App\Http\Controllers; // <-- FIX: Was App\Http/Controllers

use App\Models\Asset;
use App\Models\Department;
use App\Models\Tag;
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
        $departments = Department::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();
        return view('technician.assets.create', compact('departments', 'tags'));
    }

    /**
     * Store a newly submitted asset (status: pending_approval).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'category_id' => 'required|exists:categories,id', // Primary Category/Tag Group
            'location' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'model_number' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'warranty_expiration_date' => 'nullable|date|after_or_equal:purchase_date',
            'commissioning_notes' => 'nullable|string',
        ]);

        // --- Generate Temporary Asset Tag ID ---
        $latestTempAsset = Asset::where('temp_asset_tag_id', 'LIKE', 'TEMP-%')
                            ->orderBy('id', 'desc') 
                            ->first();

        $nextIdNumber = 1;
        if ($latestTempAsset) {
            $lastNumber = (int)str_replace('TEMP-', '', $latestTempAsset->temp_asset_tag_id);
            $nextIdNumber = $lastNumber + 1;
        }

        $tempAssetTagId = 'TEMP-' . $nextIdNumber;
        $validated['temp_asset_tag_id'] = $tempAssetTagId;
        // --- End Temp ID Generation ---

        $validated['created_by_user_id'] = Auth::id();
        $validated['status'] = 'pending_approval';

        $asset = Asset::create($validated);

        // Send Webhook: PENDING_SUBMITTED
        $this->sendWebhooks('PENDING_SUBMITTED', $asset); // <-- FIXED: Pass model

        return redirect()->route('dashboard')->with('success', "New equipment submitted for approval with Temp ID: {$tempAssetTagId}.");
    }
}
