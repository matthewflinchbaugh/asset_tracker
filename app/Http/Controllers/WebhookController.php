<?php

namespace App\Http\Controllers;

use App\Models\WebhookConfig;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User; // <-- ADDED

class WebhookController extends Controller
{
    // Define all possible fields that can be included in a payload
    const ALL_FIELDS = [
        'asset_id' => 'Asset ID',
        'asset_tag_id' => 'Asset Tag ID',
        'temp_asset_tag_id' => 'Temp Asset ID',
        'name' => 'Asset Name',
        'department_name' => 'Department Name', // <-- ADDED
        'category_name' => 'Primary Tag Name', // <-- ADDED
        'tags' => 'All Tags (Array)', // <-- ADDED
        'purchase_cost' => 'Purchase Cost',
        'status' => 'Asset Status',
        'log_id' => 'Log ID',
        'event_type' => 'Event Type',
        'service_date' => 'Service Date',
        'parts_cost' => 'Parts Cost (Log)',
        'labor_hours' => 'Labor Hours (Log)',
        'contractor_company' => 'Contractor Company',
        'description_of_work' => 'Description of Work',
        'triggered_by_user_id' => 'Trigger User ID',
        'triggered_by_user_name' => 'Trigger User Name',
        'logged_by_user_name' => 'Log Submitted By', // <-- ADDED
    ];

    /**
     * Display a listing of the webhooks.
     */
    public function index()
    {
        $webhooks = WebhookConfig::orderBy('event_type')->get();
        return view('admin.webhooks.index', compact('webhooks'));
    }

    /**
     * Show the form for creating a new webhook.
     */
    public function create()
    {
        $fields = self::ALL_FIELDS;
        $eventTypes = $this->getEventTypes();
        return view('admin.webhooks.create', compact('fields', 'eventTypes'));
    }

    /**
     * Store a newly created webhook.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url|max:255',
            'event_type' => ['required', Rule::in(array_keys($this->getEventTypes()))],
            'fields' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        WebhookConfig::create([
            'url' => $validated['url'],
            'event_type' => $validated['event_type'],
            // Convert simple array of checked boxes into a JSON structure
            'fields_to_include' => json_encode(array_fill_keys($validated['fields'] ?? [], true)),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('webhooks.index')
                         ->with('success', 'Webhook created successfully.');
    }

    /**
     * Show the form for editing the specified webhook.
     */
    public function edit(WebhookConfig $webhook)
    {
        $fields = self::ALL_FIELDS;
        $eventTypes = $this->getEventTypes();
        // Get an array of keys that are currently set to 'true'
        $selectedFields = array_keys(array_filter(json_decode($webhook->fields_to_include, true) ?? [], fn($v) => $v === true));
        
        return view('admin.webhooks.edit', compact('webhook', 'fields', 'eventTypes', 'selectedFields'));
    }

    /**
     * Update the specified webhook in storage.
     */
    public function update(Request $request, WebhookConfig $webhook)
    {
        $validated = $request->validate([
            'url' => 'required|url|max:255',
            'event_type' => ['required', Rule::in(array_keys($this->getEventTypes()))],
            'fields' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $webhook->update([
            'url' => $validated['url'],
            'event_type' => $validated['event_type'],
            'fields_to_include' => json_encode(array_fill_keys($validated['fields'] ?? [], true)),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('webhooks.index')
                         ->with('success', 'Webhook updated successfully.');
    }

    /**
     * Remove the specified webhook from storage.
     */
    public function destroy(WebhookConfig $webhook)
    {
        $webhook->delete();
        return redirect()->route('webhooks.index')
                         ->with('success', 'Webhook deleted successfully.');
    }

    /**
     * Send a test payload to the specified webhook.
     */
    public function test(WebhookConfig $webhook)
    {
        try {
            $user = auth()->user();
            $mockData = [
                'asset_id' => 999, 'asset_tag_id' => 'TEST-00001', 'temp_asset_tag_id' => 'TEMP-123',
                'name' => 'Test Asset Payload', 'purchase_cost' => 12345.67, 'status' => 'active',
                'department_name' => 'Test Department', 'category_name' => 'Test Primary Tag',
                'tags' => ['Tag A', 'Tag B'],
                'log_id' => 123, 'event_type' => $webhook->event_type, 'service_date' => now()->toIso8601String(),
                'parts_cost' => 123.45, 'labor_hours' => 2.5, 'contractor_company' => 'Test Co.',
                'description_of_work' => 'This is a test payload sent from the Asset Tracker admin panel.',
                'triggered_by_user_id' => $user->id, 'triggered_by_user_name' => $user->name,
                'logged_by_user_name' => 'Test User',
            ];
            
            $payload = [];
            $fields = json_decode($webhook->fields_to_include, true);

            if (is_array($fields)) {
                foreach ($fields as $key => $include) {
                    if ($include === true && array_key_exists($key, $mockData)) {
                        $payload[$key] = $mockData[$key];
                    }
                }
            }

            if (empty($payload)) {
                return redirect()->route('webhooks.index')->with('error', 'Test failed: No fields are selected for this webhook.');
            }

            $response = Http::timeout(8)->withoutVerifying()->post($webhook->url, $payload);

            if ($response->successful()) {
                return redirect()->route('webhooks.index')->with('success', 'Test webhook sent successfully! (Status: ' . $response->status() . ')');
            } else {
                Log::error("Webhook Test Failed (HTTP {$response->status()}): URL: {$webhook->url}");
                return redirect()->route('webhooks.index')->with('error', 'Test webhook failed! Server responded with: ' . $response->status());
            }

        } catch (\Exception $e) {
            Log::error("Webhook Test Failed (Exception): URL: {$webhook->url} | Error: " . $e->getMessage());
            return redirect()->route('webhooks.index')->with('error', 'Test webhook failed: ' . $e->getMessage());
        }
    }

    /**
     * Get the available event types.
     */
    protected function getEventTypes()
    {
        return [
            'ASSET_CREATED' => 'Asset Created (Admin)',
            'ASSET_UPDATED' => 'Asset Updated (Admin)',
            'LOG_ADDED' => 'Log Added (Internal or Contractor)',
            'PENDING_SUBMITTED' => 'Pending Asset Submitted (Technician)',
            'PENDING_APPROVED' => 'Pending Asset Approved (Admin)',
        ];
    }
}
