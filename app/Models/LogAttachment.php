<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_log_id',
        'file_path',
        'original_file_name',
        'file_type',
    ];

    /**
     * Get the maintenance log that owns the attachment.
     */
    public function maintenanceLog()
    {
        return $this->belongsTo(MaintenanceLog::class);
    }
}
