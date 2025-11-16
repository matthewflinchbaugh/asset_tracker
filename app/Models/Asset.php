<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'category_id',
        'parent_asset_id',
        'created_by_user_id',
        'asset_tag_id',
        'temp_asset_tag_id',
        'status',
        'name',
        'manufacturer',
        'model_number',
        'serial_number',
        'location',
        'documentation_link',
        'purchase_cost',
        'purchase_date',
        'warranty_expiration_date',
        'pm_interval_value',
        'pm_interval_unit',
        'next_pm_due_date',
        'pm_procedure_notes',
        'commissioning_notes',
	'temporarily_out_of_service',
	'is_critical_infrastructure',
    ];
    protected $casts = [
	    'temporarily_out_of_service' => 'boolean',
	    'is_critical_infrastructure' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Category is stored in the Tag model (categories table)
    public function category()
    {
        return $this->belongsTo(Tag::class, 'category_id');
    }

    public function tags()
    {
        // Pivot: asset_tag (category_id = tag id, asset_id = asset)
        return $this->belongsToMany(Tag::class, 'asset_tag', 'asset_id', 'category_id');
    }

    public function parent()
    {
        return $this->belongsTo(Asset::class, 'parent_asset_id');
    }

    public function children()
    {
        return $this->hasMany(Asset::class, 'parent_asset_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function maintenanceLogs()
    {
        return $this->hasMany(MaintenanceLog::class);
    }

    /**
     * PM checklist templates assigned to this asset.
     * component_name in the pivot differentiates multiple instances.
     */
    public function checklistTemplates()
    {
        return $this->belongsToMany(
            ChecklistTemplate::class,
            'asset_checklist_template'
        )->withPivot('component_name')->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Derived values / accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Calculate total repair cost (parts + labor).
     */
    public function getTotalRepairCost($laborRate = 75.00)
    {
        if (!$this->relationLoaded('maintenanceLogs')) {
            $this->load('maintenanceLogs');
        }

        $partsCost  = $this->maintenanceLogs->sum('parts_cost');
        $laborHours = $this->maintenanceLogs->sum('labor_hours');
        $laborCost  = $laborHours * $laborRate;

        return $partsCost + $laborCost;
    }

    /**
     * PM due soon = next PM between today and 7 days from now.
     */
    public function getIsPmDueSoonAttribute()
    {
        if (!$this->next_pm_due_date) {
            return false;
        }

        $due   = Carbon::parse($this->next_pm_due_date)->startOfDay();
        $today = Carbon::today();

        return $due->greaterThanOrEqualTo($today)
            && $due->lessThanOrEqualTo($today->copy()->addDays(7));
    }

    /**
     * PM overdue = next PM before today.
     */
    public function getIsPmOverdueAttribute()
    {
        if (!$this->next_pm_due_date) {
            return false;
        }

        return Carbon::parse($this->next_pm_due_date)
            ->startOfDay()
            ->lt(Carbon::today());
    }

    /**
     * Out of service if:
     *  - The asset flag is set (checkbox), OR
     *  - The most recent log is an unscheduled repair.
     */
    public function getIsOutOfServiceAttribute()
    {
        // Explicit flag wins
        if (!empty($this->temporarily_out_of_service)) {
            return true;
        }

        if (!$this->relationLoaded('maintenanceLogs')) {
            $this->load('maintenanceLogs');
        }

        $latestLog = $this->maintenanceLogs
            ->sortByDesc(function ($log) {
                return $log->service_date ?? $log->created_at;
            })
            ->first();

        if (!$latestLog) {
            return false;
        }

        return $latestLog->event_type === 'unscheduled_repair';
    }
    /**
 * Keep the parent asset's out-of-service state in sync with this child
 * when this child is marked as critical infrastructure.
 *
 * Rules:
 * - If this critical child is out of service, mark the parent out of service.
 * - If this critical child is returned to service, and there are no other
 *   critical children still out of service, return the parent to service.
 */
public function propagateOutOfServiceToParentIfNeeded(): void
{
    // If there is no parent, nothing to do
    if (!$this->parent) {
        return;
    }

    $parent = $this->parent()->first();

    // Only critical children affect the parent
    if (!$this->is_critical_infrastructure) {
        return;
    }

    // CASE 1: this critical child is OOS → parent must be OOS
    if ($this->temporarily_out_of_service) {
        if (!$parent->temporarily_out_of_service) {
            $parent->temporarily_out_of_service = true;
            $parent->save();
        }

        return;
    }

    // CASE 2: this critical child is back in service
    // Check if any other critical children are still OOS.
    $otherCriticalOosExists = $parent->children()
        ->where('is_critical_infrastructure', true)
        ->where('temporarily_out_of_service', true)
        ->exists();

    // If no other critical children are OOS, return the parent to service
    if (!$otherCriticalOosExists) {
        $parent->temporarily_out_of_service = false;
        $parent->save();
    }
}


        /**
     * Propagate this asset's next_pm_due_date to all descendants.
     *
     * Children, grandchildren, etc. will all receive the same date.
     */
    
    public function syncNextPmDueToChildren()
    {
        if (!$this->next_pm_due_date) {
            return;
        }

        // Make sure children are loaded
        if (!$this->relationLoaded('children')) {
            $this->load('children');
        }

        foreach ($this->children as $child) {
            $child->next_pm_due_date = $this->next_pm_due_date;
            $child->save();

            // Recurse so grandchildren, etc. are also updated
            $child->syncNextPmDueToChildren();
        }
    }

}

