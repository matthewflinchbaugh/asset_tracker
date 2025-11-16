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
        
        $template = $asset->checklistTemplates->first(); // Get the first template
        
        if ($template) {
            // Show the dynamic checklist form
            return view('technician.logs.checklist_form', [
                'asset'    => $asset,
                'log'      => new MaintenanceLog(),
                'template' => $template,
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
            'event_type'          => 'required|in:commissioning,scheduled_maintenance,unscheduled_repair,inspection,decommissioning',
            'service_date'        => 'required|date',
            'description_of_work' => $isChecklistForm ? 'nullable|string' : 'required|string',
            'parts_cost'          => 'nullable|numeric|min:0',
            'labor_hours'         => 'nullable|numeric|min:0',
            'attachments'         => 'nullable|array|max:5',
            'attachments.*'       => 'file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx,mp4,mov',
            'checklist_data'      => 'nullable|array',
        ]);

        $validated['asset_id'] = $asset->id;
        $validated['user_id']  = Auth::id();
        
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
                    'maintenance_log_id'          => $log->id,
                    'checklist_template_field_id' => $fieldId,
                    'value'                       => $fieldValue,
                ]);
            }
        }
        // --- END CHECKLIST DATA ---

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = Storage::putFile('logs/' . $log->id, $file);
                LogAttachment::create([
                    'maintenance_log_id' => $log->id,
                    'file_path'          => $path,
                    'original_file_name' => $file->getClientOriginalName(),
                    'file_type'          => $file->getClientMimeType(),
                ]);
            }
        }

        if (!$log->is_draft) {
            $this->sendWebhooks('LOG_ADDED', $log);
	    $this->fireSpecialWebhooksForLogs($log);

            // --- PM scheduling ---
            if ($log->event_type == 'scheduled_maintenance' && $asset->pm_interval_value) {
                try {
                    $nextDueDate = Carbon::parse($log->service_date)
                        ->add($asset->pm_interval_value, $asset->pm_interval_unit);
                    $asset->next_pm_due_date = $nextDueDate;
                } catch (\Exception $e) {
                    // ignore
                }
            }
	    // --- Out-of-service toggle based on checkbox or event type ---
if (
    $request->boolean('mark_asset_out_of_service') ||
    in_array($log->event_type, ['unscheduled_repair', 'out_of_service'])
) {
    // Any of these events will mark the asset as out of service
    $asset->temporarily_out_of_service = true;

} elseif (in_array($log->event_type, [
    'commissioning',
    'scheduled_maintenance',
    'inspection',
    'returned_to_service',
])) {
    // These events explicitly clear the out-of-service flag
    $asset->temporarily_out_of_service = false;
}

// --- Decommissioning also updates status and clears OOS ---
if ($log->event_type === 'decommissioning') {
    $asset->status = 'decommissioned';
    $asset->temporarily_out_of_service = false;
}



            $asset->save();
	    $asset->syncNextPmDueToChildren();
	    $asset->refresh(); // make sure we have updated values
	    $asset->propagateOutOfServiceToParentIfNeeded();


            return redirect()
                ->route('assets.show', $asset->id)
                ->with('success', 'Maintenance log added successfully.');
        }

        return redirect()
            ->route('logs.drafts.index')
            ->with('success', 'Log saved as draft.');
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
        
        $template = $asset->checklistTemplates->first();
        
        if ($template) {
            // Get existing values
            $existingData = $log->checklistData->pluck('value', 'checklist_template_field_id');
            
            // Show the dynamic checklist form
            return view('technician.logs.checklist_form', [
                'asset'        => $asset,
                'log'          => $log,
                'template'     => $template,
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
            'event_type'          => 'required|in:commissioning,scheduled_maintenance,unscheduled_repair,inspection,decommissioning',
            'service_date'        => 'required|date',
            'description_of_work' => $isChecklistForm ? 'nullable|string' : 'required|string',
            'parts_cost'          => 'nullable|numeric|min:0',
            'labor_hours'         => 'nullable|numeric|min:0',
            'attachments'         => 'nullable|array|max:5',
            'attachments.*'       => 'file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx,mp4,mov',
            'checklist_data'      => 'nullable|array',
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
                    'maintenance_log_id'          => $log->id,
                    'checklist_template_field_id' => $fieldId,
                    'value'                       => $fieldValue,
                ]);
            }
        }
        // --- END CHECKLIST DATA ---

        // Handle file uploads (same as store)
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = Storage::putFile('logs/' . $log->id, $file);
                LogAttachment::create([
                    'maintenance_log_id' => $log->id,
                    'file_path'          => $path,
                    'original_file_name' => $file->getClientOriginalName(),
                    'file_type'          => $file->getClientMimeType(),
                ]);
            }
        }

        // Only fire webhooks and update PM dates if it's a FINAL submission
        if (!$log->is_draft) {
            $this->sendWebhooks('LOG_ADDED', $log);
	    $this->fireSpecialWebhooksForLog($log);

            // --- PM scheduling ---
            if ($log->event_type == 'scheduled_maintenance' && $asset->pm_interval_value) {
                try {
                    $nextDueDate = Carbon::parse($log->service_date)
                        ->add($asset->pm_interval_value, $asset->pm_interval_unit);
                    $asset->next_pm_due_date = $nextDueDate;
                } catch (\Exception $e) {
                    // ignore
                }
            }

            // --- Out-of-service flag based on checkbox or event type ---
            if ($request->boolean('mark_asset_out_of_service') || $log->event_type === 'unscheduled_repair') {
                $asset->temporarily_out_of_service = true;
            } elseif (in_array($log->event_type, ['commissioning', 'scheduled_maintenance', 'inspection'])) {
                $asset->temporarily_out_of_service = false;
            }

            // --- Decommissioning also updates status and clears OOS ---
            if ($log->event_type == 'decommissioning') {
                $asset->status = 'decommissioned';
                $asset->temporarily_out_of_service = false;
            }

            $asset->save();
	    $asset->syncNextPmDueToChildren();
	    $asset->refresh(); // make sure we have updated values
	    $asset->propagateOutOfServiceToParentIfNeeded();


            return redirect()
                ->route('assets.show', $asset->id)
                ->with('success', 'Maintenance log submitted successfully.');
        }

        return redirect()
            ->route('logs.drafts.index')
            ->with('success', 'Draft updated successfully.');
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

        return redirect()
            ->route('logs.drafts.index')
            ->with('success', 'Draft deleted successfully.');
    }

    /**
     * Generate a secure link for a contractor to submit a maintenance log.
     */
    public function generateSecureLink(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'contractor_company' => 'required|string|max:255',
            'contractor_rep'     => 'required|string|max:255',
        ]);
        
        $token     = Str::random(40);
        $expiresAt = Carbon::now()->addDays(7);

        $log = MaintenanceLog::create([
            'asset_id'            => $asset->id,
            'user_id'             => null,
            'event_type'          => 'unscheduled_repair',
            'description_of_work' => 'Pending contractor submission...',
            'is_draft'            => true, 
            'secure_token'        => $token,
            'token_expires_at'    => $expiresAt,
            'contractor_company'  => $validated['contractor_company'],
            'contractor_rep'      => $validated['contractor_rep'],
        ]);

        $url = route('public.log.form', $token);

        return redirect()
            ->route('assets.show', $asset->id)
            ->with('success', 'Secure link generated successfully. Link is valid for 7 days.')
            ->with('secure_link', $url);
    }

    /**
     * Show the public log submission form for contractors.
     */
    public function showPublicForm($token)
    {
        $log = MaintenanceLog::where('secure_token', $token)
                             ->where('is_draft', true)
                             ->where('token_expires_at', '>', Carbon::now())
                             ->first();

        if (!$log) {
            abort(404, 'Link is invalid, expired, or has already been used.');
        }

        $asset = $log->asset;

        return view('public.log_form', compact('log', 'asset', 'token'));
    }

    /**
     * Handle public log submission from contractors.
     */
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
            'event_type'          => 'required|in:scheduled_maintenance,unscheduled_repair,inspection',
            'service_date'        => 'required|date',
            'description_of_work' => 'required|string',
            'parts_cost'          => 'nullable|numeric|min:0',
            'labor_hours'         => 'nullable|numeric|min:0',
            'attachments'         => 'nullable|array|max:5',
            'attachments.*'       => 'file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx,mp4,mov',
        ]);

        $log->update([
            'event_type'         => $validated['event_type'],
            'service_date'       => $validated['service_date'],
            'description_of_work'=> $validated['description_of_work'],
            'parts_cost'         => $validated['parts_cost'],
            'labor_hours'        => $validated['labor_hours'],
            'is_draft'           => false,
            'secure_token'       => null,
            'token_expires_at'   => null,
        ]);
        
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = Storage::putFile('logs/' . $log->id, $file);
                
                LogAttachment::create([
                    'maintenance_log_id' => $log->id,
                    'file_path'          => $path,
                    'original_file_name' => $file->getClientOriginalName(),
                    'file_type'          => $file->getClientMimeType(),
                ]);
            }
        }

        $this->sendWebhooks('LOG_ADDED', $log);
	$this->fireSpecialWebhooksForLog($log);

        $asset = $log->asset;

        // --- PM scheduling ---
        if ($log->event_type == 'scheduled_maintenance' && $asset->pm_interval_value) {
            try {
                $nextDueDate = Carbon::parse($log->service_date)
                    ->add($asset->pm_interval_value, $asset->pm_interval_unit);
                $asset->next_pm_due_date = $nextDueDate;
            } catch (\Exception $e) {
                // ignore
            }
        }

        // --- Out-of-service flag based on checkbox or event type ---
        if ($request->boolean('mark_asset_out_of_service') || $log->event_type === 'unscheduled_repair') {
            $asset->temporarily_out_of_service = true;
        } elseif (in_array($log->event_type, ['commissioning', 'scheduled_maintenance', 'inspection'])) {
            $asset->temporarily_out_of_service = false;
        }

        // --- Decommissioning also updates status and clears OOS ---
        if ($log->event_type == 'decommissioning') {
            $asset->status = 'decommissioned';
            $asset->temporarily_out_of_service = false;
        }

        $asset->save();
	$asset->syncNextPmDueToChildren();
	$asset->refresh(); // make sure we have updated values
	$asset->propagateOutOfServiceToParentIfNeeded();

        
        return redirect()->route('public.log.success');
    }
        /**
     * Fire additional webhooks related to maintenance events that
     * affect asset state:
     *
     * - ASSET_OOS                 => asset taken out of service
     * - ASSET_RETURNED_TO_SERVICE => asset brought back into service
     * - ASSET_MAINTENANCE_DUE     => PM is overdue on this asset
     */
    protected function fireSpecialWebhooksForLog(MaintenanceLog $log): void
    {
        // Drafts should never trigger external notifications
        if ($log->is_draft ?? false) {
            return;
        }

        $asset = $log->asset ?? null;
        if (!$asset) {
            return;
        }

        $eventType = strtolower((string) $log->event_type);

        /*
         * 1) OUT OF SERVICE EVENTS
         *
         * These should correspond to cases where a log takes the asset
         * out of service. You already treat `unscheduled_repair`
         * (and the "mark_asset_out_of_service" checkbox) as OOS in your
         * controller logic, so we mirror that here. If in the future
         * you add explicit types like "out_of_service" or "unexpected_repair",
         * just add them to the array.
         */
        if (in_array($eventType, [
            'unscheduled_repair',
            'out_of_service',
            'unexpected_repair',
            'unscheduled_maintenance',
        ], true)) {
            $this->sendWebhooks('ASSET_OOS', $asset);
        }

        /*
         * 2) RETURNED TO SERVICE EVENTS
         *
         * You already use these to clear `temporarily_out_of_service`
         * in the controller: commissioning / scheduled_maintenance / inspection.
         * We also allow an explicit "returned_to_service" type.
         */
        if (in_array($eventType, [
            'commissioning',
            'scheduled_maintenance',
            'inspection',
            'returned_to_service',
        ], true)) {
            $this->sendWebhooks('ASSET_RETURNED_TO_SERVICE', $asset);
        }

        /*
         * 3) MAINTENANCE DUE / OVERDUE
         *
         * You have PM scheduling logic that sets next_pm_due_date, and
         * an accessor like getIsPmOverdueAttribute() on Asset.
         * If the asset is now overdue, we emit ASSET_MAINTENANCE_DUE.
         *
         * NOTE: This doesn't run on a schedule; it only fires when
         * a new log is added and the asset is (still) overdue.
         */
        if (method_exists($asset, 'getIsPmOverdueAttribute') || property_exists($asset, 'is_pm_overdue')) {
            if ($asset->is_pm_overdue) {
                $this->sendWebhooks('ASSET_MAINTENANCE_DUE', $asset);
            }
        }
    }

    
    public function showPublicSuccess()
    {
        return view('public.log_success');
    }
}

