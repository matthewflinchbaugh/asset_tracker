<?php

namespace App\Traits;

use App\Models\WebhookConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

trait WebhookSender
{
    /**
     * Sends webhook payload for a given event.
     *
     * @param string $eventType The event type (e.g., 'ASSET_CREATED').
     * @param Model $model The model instance (Asset or MaintenanceLog).
     */
    protected function sendWebhooks(string $eventType, Model $model)
    {
        // 1. Get the currently authenticated user details
        $user = Auth::user();
        $userContext = [];
        if ($user) {
            $userContext['triggered_by_user_id'] = $user->id;
            $userContext['triggered_by_user_name'] = $user->name;
        } else {
            // This is for contractor links where Auth::user() is null
            $userContext['triggered_by_user_id'] = 'GUEST';
            $userContext['triggered_by_user_name'] = 'Contractor/Public Link';
        }
        
        // 2. Base data from the model
        $data = $model->toArray();
        
        // 3. Manually add rich relationship data
        if ($model instanceof \App\Models\Asset) {
            $model->load('department', 'category', 'creator', 'tags', 'maintenanceLogs'); // Eager load
            $data['department_name'] = $model->department->name ?? null;
            $data['category_name'] = $model->category->name ?? null; // Primary Tag
            $data['tags'] = $model->tags->pluck('name')->toArray(); // All Tags
            $data['creator_name'] = $model->creator->name ?? null;
            
            // --- FIX: Find and add commissioning log data ---
            $commissioningLog = $model->maintenanceLogs->where('event_type', 'commissioning')->first();
            if ($commissioningLog) {
                $data['log_id'] = $commissioningLog->id;
                $data['service_date'] = $commissioningLog->service_date;
                $data['description_of_work'] = $commissioningLog->description_of_work;
                $data['logged_by_user_name'] = $model->creator->name ?? 'System';
            }
        
        } elseif ($model instanceof \App\Models\MaintenanceLog) {
            $model->load('asset', 'user');
            $data['asset_tag_id'] = $model->asset->asset_tag_id ?? null;
            $data['name'] = $model->asset->name ?? null; // Asset Name
            
            // Determine who logged it
            if ($model->user) {
                $data['logged_by_user_name'] = $model->user->name;
            } elseif ($model->contractor_company) {
                $data['logged_by_user_name'] = "{$model->contractor_rep} ({$model->contractor_company})";
            } else {
                $data['logged_by_user_name'] = 'System';
            }
        }

        // 4. Merge model data with user context (who triggered the event)
        $fullData = array_merge($data, $userContext);

        // --- THIS IS THE FIX ---
        // Manually add the eventType to the payload so it can be filtered
        $fullData['event_type'] = $eventType;
        // --- END FIX ---


        // Fetch all active webhooks configured for this event
        $webhooks = WebhookConfig::where('event_type', $eventType)
                                 ->where('is_active', true)
                                 ->get();

        if ($webhooks->isEmpty()) {
            return;
        }

        foreach ($webhooks as $webhook) {
            try {
                // 5. Filter the data based on the user's config
                $payload = [];
                $fields = json_decode($webhook->fields_to_include, true);

                if (is_array($fields)) {
                    foreach ($fields as $key => $include) {
                        if ($include === true && array_key_exists($key, $fullData)) {
                            $payload[$key] = $fullData[$key];
                        }
                    }
                }
                
                if (empty($payload)) {
                    Log::warning("Webhook failed for URL: {$webhook->url}. Payload was empty after filtering.");
                    continue;
                }

                // 6. Send the HTTP request
                Http::timeout(5)
                    ->withoutVerifying() 
                    ->post($webhook->url, $payload);

            } catch (\Exception $e) {
                Log::error("Webhook failed to send to {$webhook->url}: " . $e->getMessage());
            }
        }
    }
}
