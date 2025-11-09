#!/bin/bash

# --- Fix script for MaintenanceLogController.php ---
#
# This script first backs up your existing controller to
# app/Http/Controllers/MaintenanceLogController.php.bak
# and then replaces it with the corrected version that
# fixes the "Undefined variable $template" error.

# Define the file to be patched
TARGET_FILE="app/Http/Controllers/MaintenanceLogController.php"
BACKUP_FILE="app/Http/Controllers/MaintenanceLogController.php.bak"

# Check if the file exists
if [ ! -f "$TARGET_FILE" ]; then
    echo "Error: File not found at $TARGET_FILE"
    echo "Please run this script from your Laravel project's root directory."
    exit 1
fi

# Create a backup
cp "$TARGET_FILE" "$BACKUP_FILE"
if [ $? -eq 0 ]; then
    echo "Backup created at $BACKUP_FILE"
else
    echo "Error: Could not create backup. Aborting."
    exit 1
fi

# Overwrite the target file with the corrected content.
# Using 'EOF' disables all shell expansion, writing the content literally.
cat <<'EOF' > "$TARGET_FILE"
<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\MaintenanceLog;
use App\Models\LogAttachment;
use App\Models\ChecklistLogData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Traits\WebhookSender;

class MaintenanceLogController extends Controller
{
    use WebhookSender;

    /**
     * Show the form for creating a new maintenance log (for logged-in users).
     * This method now checks if a draft already exists.
     */
    public function create(Asset $asset)
    {
        // Check if a draft already exists for this user and asset
        $existingDraft = MaintenanceLog::where('asset_id', $asset->id)
                                       ->where('user_id', Auth::id())
                                       ->where('is_draft', true)
                                       ->first();
        
        if ($existingDraft) {
            // A draft exists, redirect to the editDraft page
            return redirect()->route('logs.draft.edit', $existingDraft->id)
                             ->with('info', 'You have an existing draft for this asset. Please complete or delete it.');
        }

        // --- CHECKLIST LOGIC ---
        $asset->load(['checklistTemplates', 'checklistTemplates.fields']);
        
        $template = $asset->checklistTemplates->first(); // <-- 1. Get the first template
        
        if ($template) { // <-- 2. Check if the template exists
            // Show the dynamic checklist form
            return view('technician.logs.checklist_form', [
                'asset' => $asset,
                'log' => new MaintenanceLog(),
                'template' => $template // <-- 3. Pass the singular 'template'
            ]);
        }
        // --- END CHECKLIST LOGIC ---

        // No draft, show the blank standard form
        return view('admin.logs.create', ['asset' => $asset, 'log' => new MaintenanceLog()]);
    }

    /**
     * Store a new maintenance log (draft or final).
     */
    public function store(Request $request, Asset $asset)
    {
        // --- CHECKLIST LOGIC ---
        $isChecklistForm = $request->has('is_checklist_form');
        
        $validated = $request->validate([
            'event_type' => 'required|in:commissioning,scheduled_maintenance,unscheduled_repair,inspection,decommissioning',
            'service_date' => 'required|date',
            'description_of_work' => $isChecklistForm ? 'nullable|string' : 'required|string',
            'parts_cost' => 'nullable|numeric|min:0',
            'labor_hours' => 'nullable|numeric|min:0',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx,mp4,mov',
            'checklist_data' => 'nullable|array',
        ]);

        $validated['asset_id'] = $asset->id;
        $validated['user_id'] = Auth::id();
        
        if ($request->has('save_draft')) {
            $validated['is_draft'] = true;
        } else {
            $validated['is_draft'] = false;
        }

        if ($isChecklistForm && empty($validated['description_of_work']) && $asset->checklistTemplates->first()) {
            $validated['description_of_work'] = $asset->checklistTemplates->first()->name . ' completed.';
        }
        
        $log = MaintenanceLog::create($validated);

        // --- SAVE CHECKLIST DATA ---
        if ($isChecklistForm && $request->has('checklist_data')) {
            foreach ($request->input('checklist_data') as $fieldId => $value) {
                $fieldValue = is_null($value) ? 'false' : ($value === 'on' ? 'true' : $value);
                
                ChecklistLogData::create([
                    'maintenance_log_id' => $log->id,
                    'checklist_template_field_id' => $fieldId,
                    'value' => $fieldValue,
                ]);
            }
        }
        // --- END CHECKLIST DATA ---

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = Storage::putFile('logs/'."{$log->id}", $file);
                LogAttachment::create([
                    'maintenance_log_id' => $log->id, 'file_path' => $path,
                    'original_file_name' => $file->getClientOriginalName(), 'file_type' => $file->getClientMimeType(),
                ]);
            }
        }

        if (!$log->is_draft) {
            $this->sendWebhooks('LOG_ADDED', $log);

            if ($log->event_type == 'scheduled_maintenance' && $asset->pm_interval_value) {
                try {
                    $nextDueDate = Carbon::parse($log->service_date)->add($asset->pm_interval_value, $asset->pm_interval_unit);
                    $asset->next_pm_due_date = $nextDueDate;
                    $asset->save();
                } catch (\Exception $e) { /* ignore */ }
            }
            if ($log->event_type == 'decommissioning') {
                $asset->status = 'decommissioned';
                $asset->save();
            }
            
            return redirect()->route('assets.show', $asset->id)->with('success', 'Maintenance log added successfully.');
        }

        return redirect()->route('logs.drafts.index')->with('success', 'Log saved as draft.');
    }

    /**
     * Display a list of the user's saved drafts.
     */
    public function listDrafts()
    {
        $drafts = MaintenanceLog::with('asset')
                                ->where('user_id', Auth::id())
                                ->where('is_draft', true)
                                ->orderBy('updated_at', 'desc')
                                ->get();
                                
        return view('technician.drafts.index', compact('drafts'));
    }

    /**
     * Show the form for editing a specific draft.
     */
    public function editDraft(MaintenanceLog $log)
    {
        // Security check: Make sure this user owns this draft
        if ($log->user_id !== Auth::id() || !$log->is_draft) {
            abort(403, 'You do not have permission to edit this draft.');
        }
        
        $asset = $log->asset;
        $log->load('attachments', 'checklistData'); // Load existing data
        
        // --- CHECKLIST LOGIC ---
        $asset->load(['checklistTemplates', 'checklistTemplates.fields']);
        
        $template = $asset->checklistTemplates->first(); // <-- 4. Get the first template
        
        if ($template) { // <-- 5. Check if the template exists
            // Get existing values
            $existingData = $log->checklistData->pluck('value', 'checklist_template_field_id');
            
            // Show the dynamic checklist form
            return view('technician.logs.checklist_form', [
                'asset' => $asset,
                'log' => $log,
                'template' => $template, // <-- 6. Pass the singular 'template'
                'existingData' => $existingData,
            ]);
        }
        // --- END CHECKLIST LOGIC ---
        
        return view('admin.logs.create', compact('asset', 'log'));
    }

    /**
     * Update an existing maintenance log draft.
     */
    public function updateDraft(Request $request, MaintenanceLog $log)
    {
        // Security check
        if ($log->user_id !== Auth::id() || !$log->is_draft) {
            abort(403, 'You do not have permission to edit this draft.');
        }
        
        $asset = $log->asset;
        $isChecklistForm = $request->has('is_checklist_form');
        
        $validated = $request->validate([
            'event_type' => 'required|in:commissioning,scheduled_maintenance,unscheduled_repair,inspection,decommissioning',
            'service_date' => 'required|date',
            'description_of_work' => $isChecklistForm ? 'nullable|string' : 'required|string',
            'parts_cost' => 'nullable|numeric|min:0',
            'labor_hours' => 'nullable|numeric|min:0',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx,mp4,mov',
            'checklist_data' => 'nullable|array',
        ]);
        
        // Check which button was pressed
        if ($request->has('save_draft')) {
            $validated['is_draft'] = true;
        } else {
            $validated['is_draft'] = false; // Final submission
        }
        
        if ($isChecklistForm && empty($validated['description_of_work']) && $asset->checklistTemplates->first()) {
            $validated['description_of_work'] = $asset->checklistTemplates->first()->name . ' completed.';
        }

        $log->update($validated);
        
        // --- UPDATE CHECKLIST DATA ---
        if ($isChecklistForm && $request->has('checklist_data')) {
            // Clear old data first
            $log->checklistData()->delete();
            
            foreach ($request->input('checklist_data') as $fieldId => $value) {
                $fieldValue = is_null($value) ? 'false' : ($value === 'on' ? 'true' : $value);
                
                ChecklistLogData::create([
                    'maintenance_log_id' => $log->id,
                    'checklist_template_field_id' => $fieldId,
                    'value' => $fieldValue,
                ]);
            }
        }
        // --- END CHECKLIST DATA ---

        // Handle file uploads (same as store)
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = Storage::putFile('logs/'."{$log->id}", $file);
                LogAttachment::create([
                    'maintenance_log_id' => $log->id, 'file_path' => $path,
                    'original_file_name' => $file->getClientOriginalName(), 'file_type' => $file->getClientMimeType(),
                ]);
            }
        }

        // Only fire webhooks and update PM dates if it's a FINAL submission
        if (!$log->is_draft) {
            $this->sendWebhooks('LOG_ADDED', $log);

            if ($log->event_type == 'scheduled_maintenance' && $asset->pm_interval_value) {
                try {
                    $nextDueDate = Carbon::parse($log->service_date)->add($asset->pm_interval_value, $asset->pm_interval_unit);
                    $asset->next_pm_due_date = $nextDueDate;
                    $asset->save();
                } catch (\Exception $e) { /* ignore */ }
            }
            if ($log->event_type == 'decommissioning') {
                $asset->status = 'decommissioned';
                $asset->save();
            }
            
            return redirect()->route('assets.show', $asset->id)->with('success', 'Maintenance log submitted successfully.');
        }

        return redirect()->route('logs.drafts.index')->with('success', 'Draft updated successfully.');
    }

    /**
     * Delete a saved draft.
     */
    public function destroyDraft(MaintenanceLog $log)
    {
        // Security check
        if ($log->user_id !== Auth::id() || !$log->is_draft) {
            abort(403, 'You do not have permission to delete this draft.');
        }

        // Delete associated files first
        Storage::deleteDirectory('logs/' . $log->id);
        
        $log->delete();

        return redirect()->route('logs.drafts.index')->with('success', 'Draft deleted successfully.');
    }
    
    // ... (rest of the public/contractor methods) ...
    
    public function generateSecureLink(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'contractor_company' => 'required|string|max:255',
            'contractor_rep' => 'required|string|max:255',
        ]);
        
        $token = Str::random(40);
        $expiresAt = Carbon::now()->addDays(7);

        $log = MaintenanceLog::create([
            'asset_id' => $asset->id, 'user_id' => null, 'event_type' => 'unscheduled_repair',
            'description_of_work' => 'Pending contractor submission...', 'is_draft' => true, 
            'secure_token' => $token, 'token_expires_at' => $expiresAt,
            'contractor_company' => $validated['contractor_company'], 'contractor_rep' => $validated['contractor_rep'],
        ]);

        $url = route('public.log.form', $token);

        return redirect()->route('assets.show', $asset->id)
                         ->with('success', 'Secure link generated successfully. Link is valid for 7 days.')
                         ->with('secure_link', $url);
    }

    public function showPublicForm($token)
    {
        $log = MaintenanceLog::where('secure_token', $token)
                             ->where('is_draft', true)
                             ->where('token_expires_at', '>', Carbon::now())
                             ->first();

        if (!$log) {
            abort(404, 'Link is invalid, expired, or has already been used.'); // <-- FIX: Was 40NOT_FOUND
        }

        $asset = $log->asset;

        return view('public.log_form', compact('log', 'asset', 'token'));
    }

    public function storePublicLog(Request $request, $token)
    {
        $log = MaintenanceLog::where('secure_token', $token)
                             ->where('is_draft', true)
                             ->where('token_expires_at', '>', Carbon::now())
                             ->first();

        if (!$log) {
            abort(404, 'Link is invalid, expired, or has already been used.');
        }

        $validated = $request->validate([
            'event_type' => 'required|in:scheduled_maintenance,unscheduled_repair,inspection',
            'service_date' => 'required|date',
            'description_of_work' => 'required|string',
            'parts_cost' => 'nullable|numeric|min:0',
            'labor_hours' => 'nullable|numeric|min:0',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx,mp4,mov',
        ]);

        $log->update([
            'event_type' => $validated['event_type'],
            'service_date' => $validated['service_date'],
            'description_of_work' => $validated['description_of_work'],
            'parts_cost' => $validated['parts_cost'],
            'labor_hours' => $validated['labor_hours'],
            'is_draft' => false,
            'secure_token' => null,
            'token_expires_at' => null,
        ]);
        
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = Storage::putFile('logs/' . $log->id, $file);
                
                LogAttachment::create([
                    'maintenance_log_id' => $log->id, 'file_path' => $path,
                    'original_file_name' => $file->getClientOriginalName(), 'file_type' => $file->getClientMimeType(),
                ]);
            }
        }

        $this->sendWebhooks('LOG_ADDED', $log);

        $asset = $log->asset;
        if ($log->event_type == 'scheduled_maintenance' && $asset->pm_interval_value) {
            try {
                $nextDueDate = Carbon::parse($log->service_date)->add($asset->pm_interval_value, $asset->pm_interval_unit);
                $asset->next_pm_due_date = $nextDueDate;
                $asset->save();
            } catch (\Exception $e) { /* ignore */ }
        }
        
        return redirect()->route('public.log.success');
    }
    
    public function showPublicSuccess()
    {
        return view('public.log_success');
    }
}
EOF

# Check if the overwrite was successful
if [ $? -eq 0 ]; then
    echo "Successfully replaced $TARGET_FILE with the corrected version."
else
    echo "Error: Could not overwrite $TARGET_FILE."
    echo "Your backup is safe at $BACKUP_FILE"
    exit 1
fi
