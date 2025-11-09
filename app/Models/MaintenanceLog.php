<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'user_id',
        'event_type',
        'service_date',
        'description_of_work',
        'parts_cost',
        'labor_hours',
        'is_draft',
        'secure_token',
        'token_expires_at',
        'contractor_company',
        'contractor_rep',
    ];

    /**
     * Get the asset that owns the log.
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get the user who submitted the log.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attachments associated with the log.
     */
    public function attachments()
    {
        return $this->hasMany(LogAttachment::class);
    }

    /**
     * Get the custom checklist data for this log.
     */
    public function checklistData()
    {
        return $this->hasMany(ChecklistLogData::class);
    }

    /**
     * Accessor to format event_type for display in the UI.
     */
    public function getEventTypeDisplayAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->event_type));
    }
}
