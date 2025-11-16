<?php

namespace App\Http\Controllers;

use App\Models\WebhookConfig;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class WebhookController extends Controller
{
    // Define all possible fields that can be included in a payload
    const ALL_FIELDS = [
        // Core asset fields
        'asset_id' => 'Asset ID',
        'asset_tag_id' => 'Asset Tag ID',
        'temp_asset_tag_id' => 'Temporary Asset Tag ID',
        'name' => 'Asset Name',
        'description' => 'Description',
        'location' => 'Location',
        'purchase_cost' => 'Purchase Cost',
        'status' => 'Asset Status',

        // New status-related flags
        'temporarily_out_of_service' => 'Temporarily Out of Service',
        'next_pm_due_date'          => 'Next PM Due Date',

        // Relationship-derived fields
        'department_name' => 'Department Name',
        'category_name'   => 'Primary Category Name',
        'tags'            => 'All Tag Names',
        'creator_name'    => 'Creator Name',

        // Timestamps
        'created_at' => 'Asset Created At',
        'updated_at' => 'Asset Updated At',

        // Maintenance log specific fields
        'log_id'              => 'Log ID',
        'event_type'          => 'Event Type',
        'service_date'        => 'Service Date',
        'description_of_work' => 'Description of Work',
        'parts_cost'          => 'Parts Cost (Log)',
        'labor_hours'         => 'Labor Hours (Log)',
        'contractor_company'  => 'Contractor Company',
        'logged_by_user_name' => 'Log Submitted By',

        // Who triggered the webhook
        'triggered_by_user_id'   => 'Trigger User ID',
        'triggered_by_user_name' => 'Trigger User Name',
    ];

    /**
     * Display a listing of the webhooks.
     */
    public function index()
    {
        $webhooks   = WebhookConfig::orderBy('created_at', 'desc')->get();
        $eventTypes = $this->getEventTypes();

        return view('admin.webhooks.index', compact('webhooks', 'eventTypes'));
    }

    /**
     * Show the form for creating a new webhook.
     */
    public function create()
    {
        $fields     = self::ALL_FIELDS;
        $eventTypes = $this->getEventTypes();

        return view('admin.webhooks.create', compact('fields', 'eventTypes'));
    }

    /**
     * Store a newly created webhook.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'url'        => 'required|url|max:255',
            'event_type' => ['required', Rule::in(array_keys($this->getEventTypes()))],
            'fields'     => 'nullable|array',
            'is_active'  => 'boolean',
        ]);

        WebhookConfig::create([
            'url'               => $validated['url'],
            'event_type'        => $validated['event_type'],
            // Convert simple array of checked boxes into a JSON structure
            'fields_to_include' => json_encode(array_fill_keys($validated['fields'] ?? [], true)),
            'is_active'         => $request->has('is_active'),
        ]);

        return redirect()
            ->route('webhooks.index')
            ->with('success', 'Webhook created successfully.');
    }

    /**
     * Show the form for editing the specified webhook.
     */
    public function edit(WebhookConfig $webhook)
    {
        $fields     = self::ALL_FIELDS;
        $eventTypes = $this->getEventTypes();

        // Get an array of keys that are currently set to 'true'
        $stored = json_decode($webhook->fields_to_include ?? '{}', true) ?: [];
        $selectedFields = array_keys(array_filter($stored, fn ($v) => $v === true));

        return view('admin.webhooks.edit', compact('webhook', 'fields', 'eventTypes', 'selectedFields'));
    }

    /**
     * Update the specified webhook.
     */
    public function update(Request $request, WebhookConfig $webhook)
    {
        $validated = $request->validate([
            'url'        => 'required|url|max:255',
            'event_type' => ['required', Rule::in(array_keys($this->getEventTypes()))],
            'fields'     => 'nullable|array',
            'is_active'  => 'boolean',
        ]);

        $webhook->update([
            'url'               => $validated['url'],
            'event_type'        => $validated['event_type'],
            'fields_to_include' => json_encode(array_fill_keys($validated['fields'] ?? [], true)),
            'is_active'         => $request->has('is_active'),
        ]);

        return redirect()
            ->route('webhooks.index')
            ->with('success', 'Webhook updated successfully.');
    }

    /**
     * Remove the specified webhook from storage.
     */
    public function destroy(WebhookConfig $webhook)
    {
        $webhook->delete();

        return redirect()
            ->route('webhooks.index')
            ->with('success', 'Webhook deleted successfully.');
    }

    /**
     * Send a test payload to the specified webhook.
     */
    public function test(WebhookConfig $webhook)
    {
        try {
            /** @var User|null $user */
            $user = auth()->user();

            $mockData = [
                // Asset-style fields
                'asset_id'                 => 999,
                'asset_tag_id'             => 'TEST-00001',
                'temp_asset_tag_id'        => 'TEMP-123',
                'name'                     => 'Test Asset Payload',
                'description'              => 'This is a mock asset for webhook testing.',
                'location'                 => 'Test Location',
                'purchase_cost'            => 12345.67,
                'status'                   => 'active',

                // NEW flags for testing
                'temporarily_out_of_service' => true,
                'next_pm_due_date'           => now()->addWeek()->toDateString(),

                'department_name'          => 'Test Department',
                'category_name'            => 'Test Primary Tag',
                'tags'                     => ['Tag A', 'Tag B'],
                'creator_name'             => optional($user)->name ?? 'System',

                'created_at'               => now()->subDays(10)->toDateTimeString(),
                'updated_at'               => now()->toDateTimeString(),

                // Log-style fields
                'log_id'              => 555,
                'event_type'          => $webhook->event_type,
                'service_date'        => now()->subDay()->toDateString(),
                'description_of_work' => 'Mock description of work for test payload.',
                'parts_cost'          => 123.45,
                'labor_hours'         => 2.5,
                'contractor_company'  => 'Test Contractor, Inc.',
                'logged_by_user_name' => optional($user)->name ?? 'Test User',

                // Trigger context
                'triggered_by_user_id'   => optional($user)->id ?? 'SYSTEM',
                'triggered_by_user_name' => optional($user)->name ?? 'System / Artisan',
            ];

            // Filter the mockData according to this webhook's selected fields
            $fields  = json_decode($webhook->fields_to_include ?? '{}', true) ?: [];
            $payload = [];

            if (is_array($fields) && !empty($fields)) {
                foreach ($fields as $key => $include) {
                    if ($include === true && array_key_exists($key, $mockData)) {
                        $payload[$key] = $mockData[$key];
                    }
                }
            }

            // If no specific fields selected, send everything
            if (empty($payload)) {
                $payload = $mockData;
            }

            $response = Http::timeout(5)
                ->withoutVerifying()
                ->post($webhook->url, $payload);

            if ($response->successful()) {
                return back()->with('success', 'Test payload sent successfully!');
            }

            return back()->with('error', 'Test payload failed with status: ' . $response->status());
        } catch (\Exception $e) {
            Log::error('Webhook test failed', [
                'webhook_id' => $webhook->id ?? null,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'An error occurred while sending test payload.');
        }
    }

    /**
     * Get the available event types.
     */
    protected function getEventTypes()
    {
        return [
            'ASSET_CREATED'             => 'Asset Created (Admin)',
            'ASSET_UPDATED'             => 'Asset Updated (Admin)',
            'LOG_ADDED'                 => 'Log Added (Internal or Contractor)',
            'PENDING_SUBMITTED'         => 'Pending Asset Submitted (Technician)',
            'PENDING_APPROVED'          => 'Pending Asset Approved (Admin)',

            // Maintenance / status-related events
            'ASSET_OOS'                 => 'Asset Out of Service',
            'ASSET_RETURNED_TO_SERVICE' => 'Asset Returned to Service',
            'ASSET_MAINTENANCE_DUE'     => 'Asset Maintenance Due / Overdue',
        ];
    }
}

